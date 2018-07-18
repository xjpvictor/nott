<?php
include(__DIR__ . '/init.php');

if (!empty($_GET) && array_key_exists('url',$_GET))
  $url = rawurldecode($_GET['url']);
else
  $url = $site_url;

if ($auth) {
  setcookie($cookie_name, '', 1, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 1 : 0), 1);
  session_destroy();
}
header("Location: $url");
?>
