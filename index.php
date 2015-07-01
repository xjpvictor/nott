<?php
include(__DIR__ . '/init.php');

$id = '0';
$single = 0;
if (isset($_GET['id']) && $_GET['id']) {
  $id = $_GET['id'];
  $single = 1;
  if (!($note = getnote($_GET['id']))) {
    http_response_code(404);
    $error = 'Sorry, note not found.';
    include($include_dir . 'error.php');
    exit;
  } elseif (!$auth && !$note['public']) {
    http_response_code(403);
  }
}

if ($id) {
  include($include_dir . 'head.php');
  echo '<div id="main" class="single">';
  displaynote($note, '', 1);
  if (isset($note['prev']) || isset($note['next'])) {
    echo '<div id="pager">';
    if (isset($note['prev']))
      echo '<a title="Previous Note" class="nav" id="prev" href="index.php?id='.$note['prev'].'">&larr;</a>';
    if (isset($note['next']))
      echo '<a title="Next Note" class="nav" id="next" href="index.php?id='.$note['next'].'">&rarr;</a>';
    echo '<div class="clear">&nbsp;</div></div>';
  }
} else {
  if (isset($_GET['s']) && $_GET['s'] !== '') {
    $search = rawurldecode($_GET['s']);
    $list_s = $nlist;
    $list = array();
    foreach ($list_s as $file) {
      $note_s = getnote($file);
      if (($auth || $note_s['public']) && mb_stripos(strip_tags($note_s['content']), $search) !== false) {
        $list[] = $file;
      }
    }
  } elseif (isset($_GET['tag']) && $_GET['tag'] !== '') {
    $tag = rawurldecode($_GET['tag']);
    $list = gettaglist($tag);
  } else
    $list = $nlist;

  if ($list) {
    $n = count($list);
    if (isset($_GET['p']) && $_GET['p'] && is_numeric($_GET['p']) && $_GET['p'] > 1)
      $p = ($_GET['p'] - 1);
    else
      $p = 0;
    if ($p * $limit > $n - 1) {
      http_response_code(404);
      $error = 'Sorry, note not found.';
      include($include_dir . 'error.php');
      exit;
    }
    if ($limit) {
      $list = array_slice($list, $p * $limit, $limit);
    }
    include($include_dir . 'head.php');
    echo '<div id="main">';
    if (isset($tag) && $tag)
      echo '<h1 id="title">Tag: <span>'.htmlspecialchars($tag).'</span></h1>';
    elseif (isset($search) && $search)
      echo '<h1 id="title">Search: <span>'.htmlspecialchars($search).'</span></h1>';
    foreach ($list as $file) {
      if (isset($search))
        displaynote(getnote($file), $search);
      else
        displaynote(getnote($file));
    }
    if ($n > $limit) {
      echo '<div id="pager">';
      if ($n > ++$p * $limit)
        echo '<a title="Previous Page" class="nav" id="prev" href="index.php?'.(isset($tag) && $tag ? 'tag='.rawurlencode($tag).'&' : (isset($search) && $search ? 's='.rawurlencode($search).'&' : '')).'p='.++$p.'">&larr;</a>';
      else
        $p++;
      if ($p == 3) {
        echo '<a title="Next Page" class="nav" id="next" href="index.php'.(isset($tag) && $tag ? '?tag='.rawurlencode($tag) : (isset($search) && $search ? '?s='.rawurlencode($search) : '')).'">&rarr;</a>';
      } elseif ($p > 3) {
        echo '<a title="Next Page" class="nav" id="next" href="index.php?'.(isset($tag) && $tag ? 'tag='.rawurlencode($tag).'&' : (isset($search) && $search ? 's='.rawurlencode($search).'&' : '')).'p='.($p - 2).'">&rarr;</a>';
      }
      echo '<div class="clear">&nbsp;</div></div>';
    }
  } else {
    include($include_dir . 'head.php');
    echo '<div id="main">';
    if (isset($tag) && $tag)
      echo '<h1 id="title">Tag: <span>'.htmlspecialchars($tag).'</span></h1>';
    elseif (isset($search) && $search)
      echo '<h1 id="title">Search: <span>'.htmlspecialchars($search).'</span></h1>';
    echo '<div class="content">No note yet!</div>';
  }
}
?>
</div>
<!--end of main-->

<?php
include($include_dir . 'sidebar.php');
include($include_dir . 'foot.php');
