<?php
function parseevernote($note) {
  if (($resources = preg_split('/(<\/?resource>)+/i', $note))) {
    $note = $resources[0];
    $resources = array_slice($resources, 1, -1);
    $files = array();
    foreach ($resources as $resource) {
      if (!preg_match('/<data(\s+[^>]*)*>([^<]*)<\/data>/i', $resource, $matches))
        break;
      else {
        $data = $matches[2];
        if (preg_match('/encoding\s*=\s*"([^"]*)"/i', $matches[1], $encode))
          $encode = $encode[1];
        else
          $encode = '';
        $info = array('mime' => '', 'file-name' => '');
        if (preg_match_all('/<('.implode('|', array_keys($info)).')>([^>]*)<\/\1>/i', $resource, $matches)) {
          foreach ($matches[1] as $i => $tag) {
            $info[$tag] = (isset($matches[2][$i]) ? $matches[2][$i] : '');
          }
        }
        $info['encode'] = strtolower($encode);
        $info['mime'] = strtolower($info['mime']);
        $files[] = array('data' => $data, 'info' => $info);
      }
    }
  }
  $note_a = array(
    'en-note' => '',
    'created' => '',
    'source-url' => '',
  );
  if (preg_match_all('/<('.implode('|', array_keys($note_a)).')[^>]*>(.*)<\/\1>/si', $note, $matches)) {
    foreach ($matches[1] as $i => $tag) {
      $note_a[$tag] = (isset($matches[2][$i]) ? $matches[2][$i] : '');
    }
  }
  $note_a['created'] = strtotime($note_a['created']);

  $note_a['tags'] = array();
  if (($tags = preg_split('/(<\/?tag>)+/i', $note))) {
    $note_a['tags'] = array_slice($tags, 1, -1);
  }

  return array('note' => $note_a, 'attachment' => $files);
}

include(__DIR__ . '/init.php');

$hash_algo = 'md5';

if (isset($_FILES['evernote']['tmp_name']) && $_FILES['evernote']['tmp_name']) {
  if (!$auth) {
    http_response_code(403);
    $error = 'Access denied. Please <a title="login" href="login.php">login</a>.';
    include($include_dir.'error.php');
    exit;
  }

  if (move_uploaded_file($_FILES['evernote']['tmp_name'], $tmp_dir.($f = 'evernote-'.time().'.enex')))
    file_get_contents('evernote.php?f='.$f);
  header('Location: '.$site_url);
  exit;
} elseif (!isset($_GET['f']) || !$_GET['f'])
  exit;

ob_end_clean();
ob_start();
header('HTTP/1.1 200 Ok');
$size=ob_get_length();
header("Content-Length: $size");
header("Connection: close");
ob_end_flush();
flush();
if (function_exists('fastcgi_finish_request'))
  fastcgi_finish_request();
if (session_id())
  session_write_close();

if (!file_exists(($evernote_file = $tmp_dir.$_GET['f'])))
  exit;

if (isset($_GET['offset']))
  $offset = $_GET['offset'];
else
  $offset = 0;
$str = file_get_contents($evernote_file, false, null, $offset);

if (!$offset) {
  $str = preg_replace('/<title>[\r\n]*.*[\r\n]*<\/title>/i', '', $str);
  file_put_contents($evernote_file, $str);
}

if (!($notes = preg_split('/(<\/note>)+/i', $str))) {
  header('Location: '.$site_url);
  exit;
}

$str = null; unset($str);

array_pop($notes);

if (isset($_GET['index']))
  $index = $_GET['index'];
else
  $index = 0;

if (isset($_GET['n']))
  $n = $_GET['n'];
else
  $n = count($notes);

foreach ($notes as $note) {
  $offset += strlen($note)+14;
  $note = parseevernote($note);
  $note['note']['en-note'] = toutf8($note['note']['en-note']);
  $t = time();
  $i = 0;
  $index++;
  foreach ($note['attachment'] as $attachment) {
    $i++;
    if ($attachment['info']['file-name'])
      $name = rawurlencode($attachment['info']['file-name']);
    else
      $name = 'evernote-attachment';
    if ($attachment['info']['encode'] == 'base64')
      $attachment['data'] = 'data:;base64,'.$attachment['data'];
    else
      $attachment['data'] = ','.$attachment['data'];
    if (strpos($attachment['info']['mime'], 'image/') === 0) {
      if ($attachment['info']['encode'] == 'base64')
        $hash = hash($hash_algo, base64_decode(strtr(substr($attachment['data'], strpos($attachment['data'], ',')+1), ' ', '+')));
      else
        $hash = hash($hash_algo, substr($attachment['data'], strpos($attachment['data'], ',')+1));
      if (($note['note']['en-note'] = preg_replace('/<en-media\s+[^>]*hash\s*=\s*["|\']'.$hash.'["|\'][^>]*>/i', '<img src="cid:'.$hash.'" />', $note['note']['en-note'], -1, $count)) && $count)
        $_GET['name'][] = 'email-'.$hash;
      else
        $_GET['name'][] = $t.'-'.$i.'-'.$name;
      $_POST['file'][] = $attachment['data'];
    } else {
      $_GET['name'][] = $t.'-'.$i.'-'.$name;
      $_POST['file'][] = $attachment['data'];
    }
  }

  $_POST['d'] = $note['note']['en-note'];
  $note['note']['tags'][] = 'evernote';
  $_POST['t'] = implode(',', $note['note']['tags']);
  $_POST['u'] = $note['note']['source-url'];

  postnote();

  if ($index == $ever_limit)
    break;
}

if ($n > $index)
  file_get_contents('evernote.php?f='.$_GET['f'].'&index='.$index.'&n='.$n.'&offset='.$offset);
else
  unlink($evernote_file);

exit;
