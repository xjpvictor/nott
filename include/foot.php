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

</div>
<!--end of lock-hide-->

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

<?php if (!isset($clipboard) && !isset($paper)) { // In Notes mode ?>
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
<?php } elseif (!isset($paper)) { // In Clipboard mode ?>
<script>
var base_url = 'attachment.php?id=0&action=add';
</script>
<script src="include/edit.js"></script>
<script>
var tsElem = document.getElementById('clip-ts');
tsElem.innerHTML = '<?php echo (file_exists($clipboard_file) ? filemtime($clipboard_file) : time()); ?>';
function updateClip() {
  var clipTs = tsElem.innerHTML;
  var xhr = new XMLHttpRequest();
  xhr.open("GET", 'clipboard.php?ts='+clipTs, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        var data = JSON.parse(xhr.responseText);
        tsElem.innerHTML = data[0];
        document.getElementById('post-d').value = data[1];
      }
    }
  }
  xhr.send(null);
  setTimeout(updateClip, 3000);
}
setTimeout(updateClip, 3000);
</script>
<?php } elseif (!isset($paper_content) && !isset($papers)) { // In Paper mode ?>
<script>
function clearContent() {
  document.getElementById('post-d').value = '';
}
clearContent();
document.getElementById('post-d').focus();
window.onbeforeunload = function(){clearContent();};
</script>
<?php } elseif (!isset($papers)) { // Show single Paper ?>
<script>
function focusVersion(element, scroll = true, version = false) {
  document.getElementById('post-d').value = element.dataset.content;
  var elems = document.getElementsByClassName('selected');
  for (var i = 0; i < elems.length; i++) {
    elems[i].classList.remove('selected');
  }
  element.classList.add('selected');
  document.getElementById('paper-list-editing-preview').innerHTML = document.getElementById('paper-list-editing').dataset.content.substring(0, 100);
  if (scroll === true)
    document.getElementById('post-d').scrollTop = 0;
  else if (scroll === -1)
    document.getElementById('post-d').scrollTop = document.getElementById('post-d').scrollHeight;
  elemDelVer = document.getElementById('paper-delete-ver');
  if (version) {
    elemDelVer.href = elemDelVer.dataset.href + version;
    elemDelVer.classList.remove('hide');
  } else
    elemDelVer.classList.add('hide');
}
</script>
<?php } ?>

<?php if (isset($passcode) && $passcode && (isset($clipboard) || (isset($single) && $single) || (isset($post) && $post)) && !isset($paper)) { ?>
<div id="lock" style="display:none;">
<div id="login">
<p>Enter Pass code:<br/><br/>
<form method="POST" action="javascript:void(0);" onSubmit="var elem=document.getElementById('passcode');lockUnlock(elem.value);elem.value='';">
<input id="passcode" type="password" tabindex="1" autofocus></p><br/>
<input class="compose" type="submit" value="Unlock" tabindex="2">
</form>
</div>
</div>
<script>
function getCookie(name) {
  var value = "; " + document.cookie;
  var parts = value.split("; " + name + "=");
  if (parts.length == 2) return parts.pop().split(";").shift();
  else return '';
}
function setLockCookie() {
  n = Date.now();
  d = new Date();
  d.setTime(n+31536000000);
  document.cookie = "_nott_lock="+n+";expires="+d.toGMTString()+";path=/";
}
function lockDown() {
  t = getCookie('_nott_lock');
  if (t && Date.now() - t >= 600000) {
    document.getElementById('lock').style.display='block';
    window.removeEventListener("scroll", setLockCookie);
    window.removeEventListener("mousemove", setLockCookie);
    window.removeEventListener("mousedown", setLockCookie);
    window.removeEventListener("keypress", setLockCookie);
    document.title = 'Locked | <?php echo str_replace('\'', '\\\'', htmlentities($site_name)); ?>';
    document.getElementById('passcode').focus();
    return true;
  } else {
    setTimeout("lockDown()", 60000);
    return false;
  }
}
function lockUnlock(p) {
  var xhr = new XMLHttpRequest();
  xhr.open("POST", 'passcode.php', true);
  xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        document.getElementById('lock').style.display='none';
        document.title = '<?php echo str_replace('\'', '\\\'', htmlentities($site_name)); ?>';
        setLockCookie();
        window.addEventListener('scroll', setLockCookie);
        window.addEventListener('mousemove', setLockCookie);
        window.addEventListener('mousedown', setLockCookie);
        window.addEventListener('keypress', setLockCookie);
        lockDown();
      }
      if (document.getElementById('lock_s'))
        document.head.removeChild(document.getElementById('lock_s'));
    }
  }
  xhr.send('p='+p);
}
if (!lockDown()) {
  setTimeout(function() {
    window.addEventListener("scroll", setLockCookie);
    window.addEventListener("mousemove", setLockCookie);
    window.addEventListener("mousedown", setLockCookie);
    window.addEventListener("keypress", setLockCookie);
  }, 1000);
}
document.getElementById('lock-hide').style.display='block';
</script>
<?php } ?>

<script>
document.addEventListener('gesturestart', function (e) {
  e.preventDefault();
});
</script>

</body>
</html>
