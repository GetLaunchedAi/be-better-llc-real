<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

bb_start_session();

bb_json([
  'ok' => true,
  'authed' => bb_is_authed(),
]);
