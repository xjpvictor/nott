<?php
if ((isset($_SERVER["HTTP_X_PURPOSE"]) && strtolower($_SERVER["HTTP_X_PURPOSE"]) == "preview") ||  (isset($_SERVER["HTTP_X_MOZ"]) && strtolower($_SERVER["HTTP_X_MOZ"]) == "prefetch")) {
  http_response_code(404);
  exit;
}

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
$paper_dir = $user_dir.'paper/';
$include_dir = __DIR__ . '/include/';
$tmp_dir = __DIR__ . '/tmp/';
$html_dir = __DIR__ . '/html/';

$id_file = $data_dir.'id.json';
$tags_file = $data_dir.'tags.json';
$kindle_file = $data_dir.'kindle.json';
$clipboard_file = $data_dir.'clipboard.txt';
$paper_file = $data_dir.'paper.json';

$allowed_tags = '<div><p><span><h1><h2><h3><h4><h5><h6><br><br/><small><a><img><figure><figcaption><iframe><table><caption><tbody><thead><tfoot><tr><td><th><blockquote><pre><code><ol><ul><li><abbr><del><strong><b><i><ins><u><em><sub><sup><hr>';
$webclip_identify_tag_open = '##NOTTwebclipBegin##';
$webclip_identify_tag_close = '##NOTTwebclipEnd##';

$site_name = ($site_name ? $site_name : 'My Notebook');
$site_url = ($site_url ? (stripos($site_url, 'http://') === false && stripos($site_url, 'https://') === false ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') : '').$site_url.(substr($site_url, -1) !== '/' ? '/' : '') : (isset($_SERVER['SERVER_NAME']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].'/' : ''));

$mail_note_to = (isset($mail_note_to) ? $mail_note_to : '');
$mail_note_account = (isset($mail_note_account) ? $mail_note_account : '');
$mail_note_from = (isset($mail_note_from) ? $mail_note_from : '');

$ever_limit = '5';

$cost = 12; //Need to reset password if change this
if ($password && !preg_match('/\$2y\$'.$cost.'\$[\.\/0-9a-zA-Z]{'.(60-5-strlen($cost)).'}/', $password))
  file_put_contents(__DIR__ . '/config.php', str_replace('$password = \''.$password.'\'', '$password = \''.($password = password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost])).'\'', file_get_contents(__DIR__ . '/config.php')), LOCK_EX);

if (isset($passcode) && $passcode && !preg_match('/\$2y\$'.$cost.'\$[\.\/0-9a-zA-Z]{'.(60-5-strlen($cost)).'}/', $passcode))
  file_put_contents(__DIR__ . '/config.php', str_replace('$passcode = \''.$passcode.'\'', '$passcode = \''.($passcode = password_hash($passcode, PASSWORD_BCRYPT, ['cost' => $cost])).'\'', file_get_contents(__DIR__ . '/config.php')), LOCK_EX);

$salt = substr($password, -10);

if (!isset($site_url) || !$site_url || !isset($user_name) || !$user_name || !isset($user_email) || !$user_email || !isset($password) || !$password) {
  if (!isset($site_url) || !$site_url)
    $site_url = 'https://github.com/xjpvictor/nott/';
  if (!isset($user_name) || !$user_name)
    $user_name = 'nott';

  http_response_code(403);
  $error = 'Please finish setup.';
  include($include_dir . 'error.php');
  exit;
}

$limit = ($limit ? $limit : '10');
$default_privacy = ($default_privacy ? $default_privacy : '0');
$otp = ($otp ? $otp : '0');

$str_hash_algo = 'sha1';
$paper_hash_algo = 'sha1';
$paper_id_length = 4;

$avatar_hash_algo = 'md5';

$notify_paper_revision = (isset($notify_paper_revision) ? $notify_paper_revision : 1);
$allow_set_subscribe_paper = (isset($allow_set_subscribe_paper) ? $allow_set_subscribe_paper : 1);
$default_subscribe_paper = (isset($default_subscribe_paper) ? $default_subscribe_paper : 1);
$notify_me_paper_revision = (isset($notify_me_paper_revision) ? $notify_me_paper_revision : 1);
$paper_notify_email_file = $data_dir.'paper_notify_email.json';

if (!defined('NOINIT') || NOINIT !== true) {
  $auth = auth();
  $nlist = getlist();
}
