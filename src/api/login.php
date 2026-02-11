<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

// Rate limit: 10 attempts per 10 minutes per IP
if (!bb_throttle_allow('login', 10, 10 * 60)) {
  bb_json(['ok' => false, 'error' => 'Too many attempts. Try again later.'], 429);
}

$password = '';

// Accept JSON or form-encoded
$ct = (string)($_SERVER['CONTENT_TYPE'] ?? '');
if ($ct !== '' && stripos($ct, 'application/json') !== false) {
  $body = bb_read_json_body();
  $password = (string)($body['password'] ?? '');
} else {
  $password = (string)($_POST['password'] ?? '');
}

$expected = bb_admin_password();
if ($expected === '') {
  bb_json(['ok' => false, 'error' => 'ADMIN_PASSWORD is not configured on the server.'], 500);
}

// Constant-time comparison
$ok = hash_equals($expected, $password);

if (!$ok) {
  bb_json(['ok' => false, 'error' => 'Invalid password'], 401);
}

bb_start_session();
$_SESSION['bb_admin_authed'] = true;
$_SESSION['bb_admin_iat'] = time();

bb_json(['ok' => true, 'authed' => true]);
