<?php
include(__DIR__ . '/init.php');

if (isset($_GET['action']) && $_GET['action'] && isset($_GET['name']) && $_GET['name'] && isset($_GET['id'])) {
  if ($auth && $_GET['action'] == 'add' && is_string($_POST['file']) && $_POST['file']) {
    if (isset($_GET['tmp']) && $_GET['tmp']) {
      if (($attachment = saveattachment($_GET['id'], $tmp_dir)) === false) {
        http_response_code(400);
        $error = 'Error uploading.';
        include($include_dir.'error.php');
      } else {
        echo displayattachment($_GET['id'], parseattachmentname($attachment), 1, 1);
      }
    } elseif (($_GET['id'] && !getnote($_GET['id'])) || ($attachment = saveattachment($_GET['id'])) === false) {
      http_response_code(400);
      $error = 'Error uploading.';
      include($include_dir.'error.php');
    } else {
      echo displayattachment($_GET['id'], parseattachmentname($attachment), 0, 1);
    }
    exit;
  } elseif ($_GET['action'] == 'get') {
    $file_name = $_GET['id'].'-'.rawurldecode($_GET['name']);
    if (isset($_GET['tmp']) && $_GET['tmp']) {
      if ($auth) {
        if (file_exists($file = $tmp_dir.$file_name)) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $file_type = finfo_file($finfo, $file);
          header('Content-type: '.$file_type);
          readfile($file);
        } else {
          http_response_code(404);
          $error = 'File not found.';
          include($include_dir.'error.php');
        }
      }
    } else {
      if ((!$_GET['id'] && $auth) || ($note = getnote($_GET['id']) && ($note['public'] || $auth))) {
        if (file_exists($file = $upload_dir.$file_name)) {
          $finfo = finfo_open(FILEINFO_MIME_TYPE);
          $file_type = finfo_file($finfo, $file);
          header('Content-type: '.$file_type);
          readfile($file);
        } else {
          http_response_code(404);
          $error = 'File not found.';
          include($include_dir.'error.php');
        }
        exit;
      }
    }
  } elseif ($_GET['action'] == 'delete' && $auth) {
    $file_name = $_GET['id'].'-'.rawurldecode($_GET['name']);
    if (file_exists($upload_dir.$file_name)) {
      unlink($upload_dir.$file_name);
    } elseif (file_exists($tmp_dir.$file_name)) {
      unlink($tmp_dir.$file_name);
    } else {
      http_response_code(404);
      exit;
    }
    echo 'var elem = document.getElementById(\''.$_GET['elem'].'\');elem.parentNode.removeChild(elem);';
    exit;
  }
} elseif (isset($_GET['cache']) && $_GET['cache'] && isset($_GET['id']) && $_GET['id']) {
  if (($note = getnote($_GET['id'])) && ($note['public'] || $auth)) {
    $url = rawurldecode($_GET['cache']);
    $file = $upload_dir.$note['id'].'-0-'.hash('sha1', $url);
    if (file_exists($file)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $file_type = finfo_file($finfo, $file);
      header('Content-type: '.$file_type);
      readfile($file);
    } else {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_HEADER, o);
      curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36');
      curl_setopt($ch, CURLOPT_REFERER, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      $content = curl_exec($ch);
      curl_close($ch);
      if ($content) {
        file_put_contents($file, $content);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $file_type = finfo_file($finfo, $file);
        header('Content-type: '.$file_type);
        readfile($file);
      } else {
        http_response_code(404);
        $error = 'File not found.';
        include($include_dir.'error.php');
      }
    }
    exit;
  }
} elseif (isset($_GET['cid']) && $_GET['cid'] && isset($_GET['id']) && $_GET['id']) {
  if (($note = getnote($_GET['id'])) && ($note['public'] || $auth)) {
    $file = $upload_dir.$note['id'].'-0-'.$_GET['cid'];
    if (file_exists($file)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $file_type = finfo_file($finfo, $file);
      header('Content-type: '.$file_type);
      readfile($file);
    } else {
      http_response_code(404);
      $error = 'File not found.';
      include($include_dir.'error.php');
    }
    exit;
  }
}

http_response_code(403);
$error = 'Access denied.';
include($include_dir.'error.php');
