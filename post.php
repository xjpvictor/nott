<?php
include(__DIR__ . '/init.php');

if ($auth && (isset($_POST['d']) || isset($_POST['u']))) {
  if ((!isset($_POST['d']) || !$_POST['d']) && (!isset($_POST['u']) || !$_POST['u'])) {
    $empty_note = 1;
  } else {
    $note = postnote((isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id']) ? $_GET['id'] : null));
    if (isset($_POST['tmp']) && $_POST['tmp']) {
      if ($list = glob($tmp_dir.$_POST['tmp'].'*', GLOB_NOSORT)) {
        foreach ($list as $attachment) {
          $name = substr($attachment, strrpos($attachment, '/')+1);
          $name = substr($name, strpos($name, '-'));
          rename($attachment, $upload_dir.$note['id'].$name);
        }
      }
    }
  }
  if (isset($_GET['r']) && $_GET['r'] == 'bookmarklet')
    header('Location: '.$site_url.'frame.php?id='.$note['id'].(isset($_GET['url']) ? '&url='.rawurlencode(rawurldecode($_GET['url'])) : ''));
  elseif (isset($empty_note) && $empty_note == 1)
    header('Location: '.$site_url);
  elseif (isset($_GET['r']) && $_GET['r'] == 'view')
    header('Location: '.$site_url.'index.php?id='.$note['id']);
  else
    header('Location: '.$site_url.'edit.php?id='.$note['id']);
  exit;
}

http_response_code(403);
$error = 'Access denied.';
include($include_dir.'error.php');
