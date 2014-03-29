<?php
function parsekindle($str) {
  $reg = '/(.+)\((.+)\)\s+(.+)on\s+(?:page\s+(\d+))?.*Loc\S+\s+(\d+(?:-\d+)?).+on\s+(.+)\s+(.+)/mi';

  if (preg_match($reg, $str, $matches)) {
    $title = trim($matches[1]);
    $author = trim($matches[2]);
    $type = end(explode(' ', strtolower(trim($matches[3]))));
    $page = trim($matches[4]);
    $location = trim($matches[5]);
    $date = strtotime(trim($matches[6]));
    $quote = htmlentities(trim($matches[7]));

    return (array(
      'title' => $title,
      'author' => str_replace(';', ', ', $author),
      'type' => $type,
      'page' => $page,
      'loc' => $location,
      'date' => $date,
      'quote' => $quote,
    ));
  } else
    return false;
}
function postkindle($book, $author) {
  $_POST['d'] .= '<p>--<br/>Quoted from <span class="kindle-title">'.htmlentities($book).'</span>, <span class="kindle-author">'.htmlentities($author).'</span></p>';
  $_POST['t'] = 'kindle,'.$book.','.$author;
  postnote();
  $_POST = array();
}

include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  $error = 'Access denied. Please <a title="login" href="'.$site_url.'login.php">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_FILES['kindle']['tmp_name']) && $_FILES['kindle']['tmp_name']) {
  $str = file_get_contents($_FILES['kindle']['tmp_name']);
  if (is_uploaded_file($_FILES['kindle']['tmp_name']))
    unlink($_FILES['kindle']['tmp_name']);
  $_FILES = array();
} else {
  header('Location: '.$site_url);
  exit;
}

$BOM = substr($str, 0, 3);
if ($BOM == "\xEF\xBB\xBF")
  $str = substr($str, 3);

if (!($highlights = explode('==========', $str))) {
  header('Location: '.$site_url);
  exit;
}

array_pop($highlights);
$highlight = end($highlights);

if (file_exists($kindle_file)) {
  $highlight = parsekindle($highlight);
  if ($highlight['date'] <= ($ts = file_get_contents($kindle_file))) {
    header('Location: '.$site_url);
    exit;
  }
} else
  $ts = 0;

$n = count($highlights);
$i = 0;
$_POST['d'] = '';
$notes = array();
foreach ($highlights as $highlight) {
  $i++;
  $highlight = parsekindle($highlight);
  if ($highlight['type'] == 'highlight' && $highlight['date'] > $ts) {
    $str = ($highlight['date'] ? '<span class="kindle-date">'.date('M. d, Y', $highlight['date']).'</span>'.($highlight['page'] ? ' on <span class="kindle-page">Page '.$highlight['page'].'</span>'."\n" : "\n") : ($highlight['page'] ? 'on <span class="kindle-page">Page '.$highlight['page'].'</span>'."\n" : '')).(isset($notes[hash('md5', $highlight['title'].$highlight['author'])][$highlight['date']]) && ($note = $notes[hash('md5', $highlight['title'].$highlight['author'])][$highlight['date']]) ? '<span class="kindle-note">'.$note.'</span>'."\n" : '').'<blockquote class="kindle-highlight">'.$highlight['quote'].'</blockquote><hr class="kindle-separator">'."\n\n";
    if (isset($book) && isset($author) && ($highlight['title'] !== $book || $highlight['author'] !== $author)) {
      postkindle($book, $author);
    }
    $book = $highlight['title'];
    $author = $highlight['author'];
    $_POST['d'] .= $str;
    if ($i == $n) {
      postkindle($book, $author);
    }
  } elseif ($highlight['type'] == 'note' && $highlight['date'] > $ts) {
    $notes[hash('md5', $highlight['title'].$highlight['author'])][$highlight['date']] = $highlight['quote'];
  }
  if ($highlight['date'] > $ts && $i == $n) {
    file_put_contents($kindle_file, $highlight['date']);
    chmod($kindle_file, 0600);
  }
}

header('Location: '.$site_url);
exit;
