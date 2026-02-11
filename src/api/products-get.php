<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

bb_require_auth();

$items = bb_read_products();

bb_json([
  'ok' => true,
  'products' => $items,
]);
