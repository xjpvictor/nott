<?php
if (!isset($error)) {
  header("HTTP/1.0 403 Forbidden");
  $error = 'Access denied.';
}

include($include_dir . 'head.php');
?>
<div id="login">
<?php echo $error; ?>
</div>
<!--end of main-->

<?php
include($include_dir . 'foot.php');
