<?php
include(__DIR__ . '/init.php');

if (!$auth || !isset($_GET['id'])) {
  http_response_code(403);
  $error = 'Access denied.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_GET['url']))
  $url = rawurldecode($_GET['url']);
else
  $url = $site_url.'?id='.$_GET['id'];

if (isset($_GET['p']))
  $p = $_GET['p'];
else
  $p = $default_privacy;

if (!($note = getnote($_GET['id']))) {
  http_response_code(404);
  $error = 'Sorry, note not found.';
  include($include_dir . 'error.php');
  exit;
} else {
  $_POST['p'] = $p;
  $note = postnote($_GET['id']);
  header("Location: $url");
  exit;
}

