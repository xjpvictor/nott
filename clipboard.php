<?php
include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  $error = 'Access denied. Please <a title="login" href="login.php?url='.rawurlencode($site_url.'clipboard.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

$post = true;
$clipboard = (file_exists($clipboard_file) ? file_get_contents($clipboard_file) : '');

if (isset($_GET['clipts']) && $_GET['clipts'] && isset($_GET['attachmentts']) && $_GET['attachmentts']) {
  if ((!file_exists($clipboard_file) || filemtime($clipboard_file) <= $_GET['clipts']) && (!file_exists($clipboard_attachment_cache) || filemtime($clipboard_attachment_cache) <= $_GET['attachmentts'])) {
    http_response_code(304);
    exit;
  }
  $attachments = '';
  if (($list = getattachment(0))) {
    foreach ($list as $attachment) {
      $attachments .= displayattachment(0, parseattachmentname($attachment), 0, 1);
    }
  }
  if ($attachments !== file_get_contents($clipboard_attachment_cache))
    file_put_contents($clipboard_attachment_cache, $attachments, LOCK_EX);
  echo json_encode(array(
    'clip-ts' => filemtime($clipboard_file),
    'attachment-ts' => filemtime($clipboard_attachment_cache),
    'post-d' => $clipboard,
    'attachment-list' => $attachments,
  ));
  exit;
} elseif (isset($_GET['c']) && $_GET['c']) {
  $clipboard .= "\n\n".urldecode($_GET['c']);
  file_put_contents($clipboard_file, $clipboard);
  chmod($clipboard_file, 0600);
  echo '<html><body><script>window.confirm("Text copied to clipboard.");window.location="'.$site_url.'clipboard.php";</script></body></html>';
  //header('Location: clipboard.php');
  exit;
} elseif (isset($_POST['d']) && $_POST['d']) {
  $clipboard = $_POST['d'];
  file_put_contents($clipboard_file, $clipboard);
  chmod($clipboard_file, 0600);
  if (isset($_GET['r']) && $_GET['r'] == 'bookmarklet') {
    header('Location: '.$site_url.'frame.php?clip=true&close=true'.(isset($_GET['url']) ? '&url='.rawurlencode(rawurldecode($_GET['url'])) : ''));
    exit;
  }
}

include($include_dir . 'head.php');
?>
<form id="post" method="POST" action="clipboard.php" enctype="multipart/form-data">
<div id="main">
<div class="content">
<textarea id="post-d" name="d" onblur="autoSave(this.value);" onpaste="pasteImage(event);"><?php echo htmlentities($clipboard); ?></textarea>
</div>
</div>
<div id="clip-ts" style="display:none;"></div>
<div id="attachment-ts" style="display:none;"></div>
<!--end of main-->

<?php
include($include_dir . 'sidebar.php');
?>
</form>
<?php
include($include_dir . 'foot.php');
?>
