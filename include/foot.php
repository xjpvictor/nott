<div class="clear">&nbsp;</div>

<?php
if (!isset($post) || !$post) {
  if (file_exists($user_dir.'my_foot.php'))
    include($user_dir.'my_foot.php');
}
?>

<div id="footer">
<p id="copy">&copy; <?php echo date("Y"); ?> <a href="index.php"><?php echo htmlspecialchars($site_name); ?></a>. All Rights Reserved.</p>
<p>Powered by <a href="https://github.com/xjpvictor/nott/" target="_blank">nott</a></p>
<img src="parsemail.php" width="20px" height="20px" alt="get mail" style="padding:5px;" />
</div>
<!--end of footer-->

</div>
<!--end of wrap-->

<script>
function notRobot() {
  document.cookie = "_nott_notRobot=1;path=/";
  window.removeEventListener("scroll", notRobot);
  window.removeEventListener("mousemove", notRobot);
  window.removeEventListener("keypress", notRobot);
}
window.addEventListener("scroll", notRobot);
window.addEventListener("mousemove", notRobot);
window.addEventListener("keypress", notRobot);
</script>

<?php if (!isset($clipboard)) { ?>
<?php if ((isset($single) && $single) || (isset($post) && $post)) { ?>
<script src="include/highlight/highlight.pack.js"></script>
<script>
function noteSH() {
  var c=document.querySelectorAll('pre code');
  for(var i=0;i<c.length;i++){
    var elem=c[i];
    elem.innerHTML='<ol>' + elem.innerHTML.replace(/^.*?(\n|\r|$)/gm, '<li><span>$&</span></li>') + '</ol>';
    hljs.highlightBlock(elem);
  }
}
</script>
<?php } ?>
<?php if (isset($single) && $single) { ?>
<script>
noteSH();
</script>
<?php } elseif (isset($post) && $post) { ?>
<script src="include/Markdown.Converter.js"></script>
<script>
var converter = new Markdown.Converter();
var base_url = 'attachment.php?id=<?php echo (isset($note) ? $note['id'] : $new_id); ?>&action=add<?php echo (!isset($note) ? '&tmp=1' : ''); ?>';
</script>
<script src="include/edit.js"></script>
<?php } ?>
<?php } else { ?>
<script>
var base_url = 'attachment.php?id=0&action=add';
</script>
<script src="include/edit.js"></script>
<?php } ?>

</body>
</html>
