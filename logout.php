<?php
include(__DIR__ . '/init.php');

if (!empty($_GET) && array_key_exists('url',$_GET))
  $url = rawurldecode($_GET['url']);
else
  $url = $site_url;

if ($auth) {
  session_destroy();
}
header("Location: $url");
?>
