<?php
include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id']))
    $url = 'login.php?url='.rawurlencode($site_url.'export.php?id='.$_GET['id']);
  $error = 'Access denied. Please <a title="login" href="'.(isset($url) ? $url : 'login.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_GET['id']) && $_GET['id']) {
  if (!($note = getnote($_GET['id'], 1))) {
    http_response_code(404);
    $error = 'Sorry, note not found.';
    include($include_dir . 'error.php');
    exit;
  }
  $notes = array($note);
} else {
  $notes = $nlist;
}

if (class_exists('ZipArchive')) {
  $zip = new ZipArchive();
  $zip_file = $tmp_dir.'export-'.time().'.zip';
  $html_files = array();
  $i = 0;
  $zip->open($zip_file, ZipArchive::CREATE);
  foreach ($notes as $note) {
    if ((is_string($note) && ($note = getnote($note, 1))) || (is_array($note) && $note)) {
      $zip->addFile($include_dir.'style.css', $note['id'].'/style.css');
      $zip->addFile($include_dir.'readability.css', $note['id'].'/readability.css');
      if (file_exists(($file = $content_dir.$note['id'].'.txt'))) {
        $html = '<html><head><meta charset="utf-8"><title>Note</title><link rel="stylesheet" href="./style.css" type="text/css" media="all"/><link rel="stylesheet" href="./readability.css" type="text/css" media="all"/></head><body>'.(isset($note['source']['url']) && $note['source']['url'] ? '<p>Note original from: <a href="'.$note['source']['url'].'">'.htmlentities($note['source']['url']).'</a></p><br><br>' : '').'<div id="readability">'.file_get_contents($file).'</div></body></html>';
        $id = $note['id'];
        $html = preg_replace_callback('/<img ((?:[^>]*\s)*)src\s*=\s*("|\')attachment\.php\?id='.$id.'&cache=([^"\']+)("|\')(\s+[^>]*)?(\/\s*)?>/i', function ($match) use ($id) {
          return '<br/><img '.$match[1].'src='.$match[2].'./'.$id.'-0-'.hash('sha1', rawurldecode($match[3])).$match[4].(isset($match[5]) ? $match[5] : '').(isset($match[6]) ? $match[6] : '').'><br/>';
        }, $html);
        $html_files[$i] = $tmp_dir.$id.'.html';
        file_put_contents($html_files[$i], $html);
        $zip->addFile($html_files[$i], $note['id'].'/'.$note['id'].'.html');
        $i++;
      }
      if ($attachment = getattachment($note['id'], 1)) {
        foreach ($attachment as $file) {
          $zip->addFile($file, $note['id'].'/'.basename($file));
        }
      }
    }
  }
  $zip->close();

  if (file_exists($zip_file)) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($finfo, $zip_file);
    header('Content-description: File Transfer');
    header('Content-type: application/octet-stream');
    header('Content-disposition: attachment; filename="'.(isset($_GET['id']) && $_GET['id'] ? $_GET['id'] : 'notes').'.zip"');
    header('Content-transfer-encoding: binary');
    header('Content-length: '.filesize($zip_file));
    readfile($zip_file);
    unlink($zip_file);
    if ($html_files) {
      foreach ($html_files as $html_file) {
        if (file_exists($html_file))
          unlink($html_file);
      }
    }
  } else {
    http_response_code(404);
    $error = 'Sorry, export failed.';
    include($include_dir . 'error.php');
  }
  exit;
}
