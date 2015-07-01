<?php
include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id']))
    $url = 'login.php?url='.rawurlencode($site_url.'export.php?id='.$_GET['id']);
  $error = 'Access denied. Please <a title="login" href="'.(isset($url) ? $url : 'login.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_GET['id']) && $_GET['id']) {
  if (!($note = getnote($_GET['id'], 1))) {
    http_response_code(404);
    $error = 'Sorry, note not found.';
    include($include_dir . 'error.php');
    exit;
  }
}

if (class_exists('ZipArchive')) {
  $zip = new ZipArchive();
  $zip_file = $tmp_dir.$note['id'].'-export.zip';
  $zip->open($zip_file, ZipArchive::CREATE);
  $zip->addFile($data_dir.$note['id'].'.json', $note['id'].'.json');
  if (file_exists(($file = $content_dir.$note['id'].'.txt')))
    $zip->addFile($file, $note['id'].'.txt');
  if ($attachment = getattachment($note['id'], 1)) {
    foreach ($attachment as $file) {
      $zip->addFile($file, basename($file));
    }
  }
  $zip->close();

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $file_type = finfo_file($finfo, $zip_file);
  header('Content-description: File Transfer');
  header('Content-type: application/octet-stream');
  header('Content-disposition: attachment; filename="'.$note['id'].'.zip"');
  header('Content-transfer-encoding: binary');
  header('Content-length: '.filesize($zip_file));
  readfile($zip_file);
  unlink($zip_file);
  exit;
}
