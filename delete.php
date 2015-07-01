<?php
include(__DIR__ . '/init.php');

if (!$auth || !isset($_GET['id'])) {
  http_response_code(403);
  if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id']))
    $url = 'login.php?url='.rawurlencode($site_url.'delete.php?id='.$_GET['id']);
  $error = 'Access denied. Please <a title="login" href="'.(isset($url) ? $url : 'login.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (!($note = getnote($_GET['id']))) {
  http_response_code(404);
  $error = 'Sorry, note not found.';
  include($include_dir . 'error.php');
  exit;
} else {
  deletenote($note);
  header("Location: $site_url");
  exit;
}
