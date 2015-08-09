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

if (isset($_POST['d']) && $_POST['d'] !== $clipboard) {
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
<textarea id="post-d" name="d"><?php echo htmlentities($clipboard); ?></textarea>
</div>
</div>
<!--end of main-->

<?php
include($include_dir . 'sidebar.php');
?>
</form>
<?php
include($include_dir . 'foot.php');
?>
