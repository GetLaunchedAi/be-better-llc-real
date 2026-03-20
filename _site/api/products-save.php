<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

bb_require_auth();

$body = bb_read_json_body();
$incoming = $body;
if (isset($body['products']) && is_array($body['products'])) {
  $incoming = $body['products'];
}

[$products, $errors] = bb_validate_products(is_array($incoming) ? $incoming : []);

if (!empty($errors)) {
  bb_json(['ok' => false, 'error' => 'Validation failed', 'details' => $errors], 422);
}

try {
  $path = bb_products_path();
  $json = json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
  bb_atomic_write($path, $json);
} catch (Throwable $e) {
  bb_json(['ok' => false, 'error' => 'Failed to write products.json'], 500);
}

bb_json(['ok' => true, 'products' => $products]);
