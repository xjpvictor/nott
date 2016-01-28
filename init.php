<?php
if (!file_exists(__DIR__ . '/config.php'))
  exit('Please update "config.php" file according to "config.php-dist"');
if (!function_exists('password_hash'))
  exit('Please update your php version >= 5.5.3');

if (@filemtime(__DIR__ . '/config.php') && function_exists('opcache_invalidate'))
  opcache_invalidate(__DIR__ . '/config.php',true);
include(__DIR__ . '/config.php');
include(__DIR__ . '/functions.php');
include(__DIR__ . '/include/Parsedown.php');

$user_dir = __DIR__ .'/usr/';
$data_dir = $user_dir.'data/';
$content_dir = $user_dir.'content/';
$upload_dir = $user_dir.'upload/';
$include_dir = __DIR__ . '/include/';
$tmp_dir = __DIR__ . '/tmp/';
$html_dir = __DIR__ . '/html/';

$id_file = $data_dir.'id.json';
$tags_file = $data_dir.'tags.json';
$kindle_file = $data_dir.'kindle.json';
$clipboard_file = $data_dir.'clipboard.txt';

$allowed_tags = '<p><span><h1><h2><h3><h4><h5><h6><br><br/><small><a><img><figure><figcaption><iframe><table><caption><tbody><thead><tfoot><tr><td><th><blockquote><pre><code><ol><ul><li><abbr><del><strong><b><i><ins><u><em><sub><sup><hr>';

$site_name = ($site_name ? $site_name : 'My Notebook');
$site_url = ($site_url ? (stripos($site_url, 'http://') === false && stripos($site_url, 'https://') === false ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') : '').$site_url.(substr($site_url, -1) !== '/' ? '/' : '') : (isset($_SERVER['SERVER_NAME']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/' : ''));

$ever_limit = '5';

$cost = 12; //Need to reset password if change this
if ($password && !preg_match('/\$2y\$'.$cost.'\$[\.\/0-9a-zA-Z]{'.(60-5-strlen($cost)).'}/', $password)) {
  file_put_contents(__DIR__ . '/config.php', str_replace('$password = \''.$password.'\'', '$password = \''.($password = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost])).'\'', file_get_contents(__DIR__ . '/config.php')), LOCK_EX);
}

$salt = substr($password, -10);
$ip = hash('sha512', $salt.$_SERVER['REMOTE_ADDR']);

if (!$site_url || !$user_name || !$password) {
  if (!$site_url)
    $site_url = 'https://github.com/xjpvictor/nott/';
  if (!$user_name)
    $user_name = 'nott';

  http_response_code(403);
  $error = 'Please finish setup.';
  include($include_dir . 'error.php');
  exit;
}

$limit = ($limit ? $limit : '10');
$default_privacy = ($default_privacy ? $default_privacy : '0');
$otp = ($otp ? $otp : '0');

$auth = auth();

$nlist = getlist();
