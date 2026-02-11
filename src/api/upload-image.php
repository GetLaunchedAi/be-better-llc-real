<?php
declare(strict_types=1);

require_once __DIR__ . '/_common.php';

bb_require_auth();

// Basic upload endpoint (optional)
// - POST multipart/form-data with field name: file

if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
  bb_json(['ok' => false, 'error' => 'No file uploaded'], 400);
}

$file = $_FILES['file'];
if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
  bb_json(['ok' => false, 'error' => 'Upload failed'], 400);
}

$maxBytes = 5 * 1024 * 1024;
if ((int)($file['size'] ?? 0) > $maxBytes) {
  bb_json(['ok' => false, 'error' => 'File too large (max 5MB)'], 413);
}

$originalName = (string)($file['name'] ?? 'upload');
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
if (!in_array($ext, $allowedExt, true)) {
  bb_json(['ok' => false, 'error' => 'Unsupported file type'], 415);
}

// MIME whitelist (best-effort)
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = $finfo ? finfo_file($finfo, (string)($file['tmp_name'] ?? '')) : '';
if ($finfo) finfo_close($finfo);

$allowedMime = ['image/jpeg', 'image/png', 'image/webp'];
if ($mime && !in_array($mime, $allowedMime, true)) {
  bb_json(['ok' => false, 'error' => 'Invalid image MIME type'], 415);
}

$safeBase = preg_replace('/[^a-zA-Z0-9_-]/', '-', pathinfo($originalName, PATHINFO_FILENAME)) ?? 'upload';
$safeBase = trim($safeBase, '-');
if ($safeBase === '') $safeBase = 'upload';

$filename = $safeBase . '-' . date('Ymd-His') . '-' . substr(bin2hex(random_bytes(4)), 0, 8) . '.' . $ext;

$destDir = bb_docroot() . '/assets/uploads';
if (!is_dir($destDir)) {
  @mkdir($destDir, 0755, true);
}

$destPath = $destDir . '/' . $filename;

if (!@move_uploaded_file((string)$file['tmp_name'], $destPath)) {
  bb_json(['ok' => false, 'error' => 'Failed to save uploaded file'], 500);
}

$publicUrl = '/assets/uploads/' . $filename;

bb_json([
  'ok' => true,
  'url' => $publicUrl,
  'filename' => $filename,
]);
