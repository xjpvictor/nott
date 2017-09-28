<?php
include(__DIR__ . '/init.php');

$avatar_expire_time = 604800;
$avatar_default = 'identicon';

if (isset($_GET['hash']) && $_GET['hash'] && isset($_GET['s']) && $_GET['s']) {
  $image_file = $tmp_dir.$_GET['hash'].$_GET['s'];
  if (file_exists($image_file) && time() - filemtime($image_file) < $avatar_expire_time) {
    header('Content-Type: '.image_type_to_mime_type(exif_imagetype($image_file)));
    header('Cache-Control: max-age='.$avatar_expire_time.', public');
    header("Pragma: cache");
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $avatar_expire_time).' GMT');
    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($image_file)).' GMT');
  } else {
    $url = 'https://www.gravatar.com/avatar/'.$_GET['hash'].'?s='.$_GET['s'].'&d='.$avatar_default.'&r=g';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    $headers = explode("\n", substr($response, 0, $header_size));
    foreach ($headers as $header)
      header($header);
    file_put_contents($image_file, substr($response, $header_size));
  }
  if (file_exists($image_file))
    readfile($image_file);
  exit;
}

http_response_code(404);
$error = 'Sorry, page not found.';
include($include_dir . 'error.php');
exit;
?>
