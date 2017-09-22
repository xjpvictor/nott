<?php
include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  $error = 'Access denied. Please <a title="login" href="login.php?url='.rawurlencode($site_url.'paper.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

$post = true;
$paper = true;

include($include_dir . 'head.php');
?>
<form id="post" method="POST" action="" enctype="multipart/form-data">
<div id="main">
<div class="content">
<textarea id="post-d" name="d"></textarea>
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
