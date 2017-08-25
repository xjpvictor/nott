<?php
if (isset($_GET['i']) && isset($_GET['id'])) {

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

  if (!($note = getnote($_GET['id']))) {
    http_response_code(404);
    $error = 'Sorry, note not found.';
    include($include_dir . 'error.php');
    exit;
  } else {
    if ($_GET['i'] == 0) {
      $tags = $note['tags'];
      $note['tags'] = array_diff($note['tags'], array('inbox'));

      file_put_contents($data_dir.$note['id'].'.json', json_encode($note), LOCK_EX);
      chmod($data_dir.$note['id'].'.json', 0600);
      updatetag($note['tags'], $tags, $note['id']);
    }

    header("Location: $url");
    exit;
  }

} else {

  $_GET['tag'] = 'inbox';
  include(__DIR__ . '/index.php');

}
