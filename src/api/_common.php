<?php
// Shared helpers for /api endpoints

declare(strict_types=1);

function bb_is_https(): bool {
  if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
  if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
  return false;
}

function bb_start_session(): void {
  if (session_status() === PHP_SESSION_ACTIVE) return;

  // Harden session cookies a bit
  $params = session_get_cookie_params();
  session_set_cookie_params([
    'lifetime' => 0,
    'path' => $params['path'] ?? '/',
    'domain' => $params['domain'] ?? '',
    'secure' => bb_is_https(),
    'httponly' => true,
    'samesite' => 'Lax',
  ]);

  session_name('bb_admin');
  session_start();
}

function bb_json($data, int $status = 200): void {
  http_response_code($status);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store');
  echo json_encode($data, JSON_UNESCAPED_SLASHES);
  exit;
}

function bb_read_json_body(): array {
  $raw = file_get_contents('php://input');
  if (!$raw) return [];
  $decoded = json_decode($raw, true);
  return is_array($decoded) ? $decoded : [];
}

function bb_admin_password(): string {
  // Cloudways: SetEnv ADMIN_PASSWORD "..." (Application Settings/.htaccess)
  $p = getenv('ADMIN_PASSWORD');
  if (is_string($p) && strlen(trim($p)) > 0) return trim($p);
  // Some hosts expose env via $_SERVER
  if (!empty($_SERVER['ADMIN_PASSWORD'])) return trim((string)$_SERVER['ADMIN_PASSWORD']);
  return '';
}

function bb_is_authed(): bool {
  bb_start_session();
  return !empty($_SESSION['bb_admin_authed']);
}

function bb_require_auth(): void {
  if (!bb_is_authed()) {
    bb_json(['ok' => false, 'error' => 'Unauthorized'], 401);
  }
}

function bb_client_ip(): string {
  // Basic IP detection (Cloudways often sets X-Forwarded-For)
  $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
  if ($xff) {
    $parts = array_map('trim', explode(',', $xff));
    if (!empty($parts[0])) return $parts[0];
  }
  return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function bb_throttle_allow(string $bucket, int $maxAttempts, int $windowSeconds): bool {
  $ip = preg_replace('/[^0-9a-fA-F:\.]/', '_', bb_client_ip());
  $file = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . "bb_throttle_{$bucket}_{$ip}.json";

  $now = time();
  $state = ['count' => 0, 'start' => $now];

  if (is_file($file)) {
    $raw = @file_get_contents($file);
    $decoded = $raw ? json_decode($raw, true) : null;
    if (is_array($decoded) && isset($decoded['count'], $decoded['start'])) {
      $state['count'] = (int)$decoded['count'];
      $state['start'] = (int)$decoded['start'];
    }
  }

  if ($now - $state['start'] > $windowSeconds) {
    $state = ['count' => 0, 'start' => $now];
  }

  if ($state['count'] >= $maxAttempts) {
    return false;
  }

  $state['count']++;
  @file_put_contents($file, json_encode($state), LOCK_EX);
  return true;
}

function bb_docroot(): string {
  $dr = $_SERVER['DOCUMENT_ROOT'] ?? '';
  if (is_string($dr) && $dr !== '') return rtrim($dr, '/');
  // Fallback: /api is inside docroot
  $fallback = realpath(__DIR__ . '/..');
  return $fallback ? rtrim($fallback, '/') : '';
}

function bb_products_path(): string {
  return bb_docroot() . '/products.json';
}

function bb_slugify(string $value): string {
  $value = trim(strtolower($value));
  $value = preg_replace('/[^a-z0-9\s-]/', '', $value) ?? '';
  $value = preg_replace('/[\s-]+/', '-', $value) ?? '';
  $value = trim($value, '-');
  return $value;
}

function bb_to_array($value): array {
  if (is_array($value)) {
    return array_values(array_filter(array_map(fn($v) => trim((string)$v), $value), fn($v) => $v !== ''));
  }
  if (is_string($value)) {
    $parts = array_map('trim', explode(',', $value));
    return array_values(array_filter($parts, fn($v) => $v !== ''));
  }
  return [];
}

function bb_parse_price($value): ?string {
  $raw = trim((string)$value);
  if ($raw === '') return null;
  $n = preg_replace('/[^0-9.]/', '', $raw);
  if ($n === '' || !is_numeric($n)) return null;
  // Keep as a normalized string (2 decimals)
  return number_format((float)$n, 2, '.', '');
}

function bb_atomic_write(string $path, string $contents): void {
  $dir = dirname($path);
  if (!is_dir($dir)) {
    @mkdir($dir, 0755, true);
  }

  $tmp = $path . '.tmp.' . bin2hex(random_bytes(6));
  $bytes = @file_put_contents($tmp, $contents, LOCK_EX);
  if ($bytes === false) {
    @unlink($tmp);
    throw new RuntimeException('Failed to write temp file');
  }

  if (!@rename($tmp, $path)) {
    @unlink($tmp);
    throw new RuntimeException('Failed to move temp file into place');
  }
}

function bb_read_products(): array {
  $path = bb_products_path();
  if (!is_file($path)) return [];
  $raw = @file_get_contents($path);
  $decoded = $raw ? json_decode($raw, true) : null;
  return is_array($decoded) ? $decoded : [];
}

function bb_validate_products(array $incoming): array {
  // Returns [products, errors]
  if (!is_array($incoming)) return [[], ['Payload must be an array']];

  $products = [];
  $errors = [];
  $seenSlugs = [];

  foreach ($incoming as $idx => $p) {
    if (!is_array($p)) {
      $errors[] = "Item #{$idx} is not an object";
      continue;
    }

    $title = trim((string)($p['title'] ?? ''));
    if ($title === '') {
      $errors[] = "Item #{$idx} missing title";
      continue;
    }

    $price = bb_parse_price($p['price'] ?? '');
    if ($price === null) {
      $errors[] = "Item #{$idx} has invalid price";
      continue;
    }

    $slug = trim((string)($p['slug'] ?? ''));
    $slug = $slug !== '' ? bb_slugify($slug) : bb_slugify($title);
    if ($slug === '') {
      $errors[] = "Item #{$idx} has invalid slug";
      continue;
    }

    $slugKey = strtolower($slug);
    if (isset($seenSlugs[$slugKey])) {
      $errors[] = "Duplicate slug '{$slug}'";
      continue;
    }
    $seenSlugs[$slugKey] = true;

    $id = trim((string)($p['id'] ?? ''));
    if ($id === '') {
      $id = 'p_' . time() . '_' . substr(bin2hex(random_bytes(4)), 0, 8);
    }

    $subtitle = trim((string)($p['subtitle'] ?? ''));
    $compareAt = $p['compareAt'] ?? '';
    $compareAtNorm = $compareAt !== '' ? bb_parse_price($compareAt) : '';
    if ($compareAt !== '' && $compareAtNorm === null) {
      $errors[] = "Item #{$idx} has invalid compareAt";
      continue;
    }

    // Normalize arrays
    $collections = bb_to_array($p['collections'] ?? ($p['collection'] ?? ''));
    $tags = bb_to_array($p['tags'] ?? '');
    $badges = bb_to_array($p['badges'] ?? '');
    $badge = trim((string)($p['badge'] ?? ''));

    $image = trim((string)($p['image'] ?? ''));
    if ($image === '') $image = '/assets/img/placeholder.jpg';

    // Preserve unknown fields, overwrite normalized core fields
    $out = $p;
    $out['id'] = $id;
    $out['slug'] = $slug;
    $out['url'] = '/products/' . $slug . '/';
    $out['title'] = $title;
    $out['subtitle'] = $subtitle;
    $out['price'] = $price;
    if ($compareAtNorm !== '') $out['compareAt'] = $compareAtNorm;
    else unset($out['compareAt']);
    if (!empty($badge)) $out['badge'] = $badge; else unset($out['badge']);
    if (!empty($badges)) $out['badges'] = $badges; else unset($out['badges']);
    $out['collections'] = $collections;
    $out['tags'] = $tags;
    $out['image'] = $image;

    $products[] = $out;
  }

  return [$products, $errors];
}
