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

<?php if (!isset($error)) { ?>

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
var url_id = '<?php echo (isset($note) ? $note['id'] : $new_id); ?>';
var base_url = 'attachment.php?id='+url_id+'&action=add<?php echo (!isset($note) ? '&tmp=1' : ''); ?>';
var new_post = '<?php echo (!isset($note) ? '1' : ''); ?>';
</script>
<script src="include/edit.js"></script>
<?php } ?>
<?php } elseif (!isset($paper)) { // In Clipboard mode ?>
<script>
var base_url = 'attachment.php?id=0&action=add';
var new_post = '';
</script>
<script src="include/edit.js"></script>
<script>
var clientId = '';
var clipTsElem = document.getElementById('clip-ts');
var attachmentTsElem = document.getElementById('attachment-ts');
clipTsElem.innerHTML = '<?php echo (file_exists($clipboard_file) ? filemtime($clipboard_file) : time()); ?>';
attachmentTsElem.innerHTML = '<?php echo (file_exists($clipboard_attachment_cache) ? filemtime($clipboard_attachment_cache) : time()); ?>';
function updateClip() {
  if (typeof document.getElementById('post-d') == 'undefined' || document.getElementById('post-d') === null) {
    return false;
  }
  var clipTs = clipTsElem.innerHTML;
  var attachmentTs = attachmentTsElem.innerHTML;
  var xhr = new XMLHttpRequest();
  xhr.withCredentials = true;
  xhr.open("GET", 'clipboard.php?clipts='+clipTs+'&attachmentts='+attachmentTs, true);
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        var data = JSON.parse(xhr.responseText);
        if (typeof data['clip-ts'] != 'undefined' && data['clip-ts'] !== null) {
          clipTsElem.innerHTML = data['clip-ts'];
          attachmentTsElem.innerHTML = data['attachment-ts'];
          document.getElementById('post-d').value = data['post-d'];
          document.getElementById('attachment-list').innerHTML = data['attachment-list'];
        }
        clientId = data['this-client'];
        if (window.File && window.FileList && window.FileReader && window.XMLHttpRequest) {
          if (data['clients-list'] == '')
            document.getElementById('transfer').classList.add('hidden');
          if (document.getElementById('clients-list').dataset.content != data['clients-list']) {
            document.getElementById('clients-list').innerHTML = data['clients-list'];
            document.getElementById('clients-list').dataset.content = data['clients-list'];
          }
          if (data['clients-list'] != '' && document.getElementById('transfer').classList.contains('hidden'))
            document.getElementById('transfer').classList.remove('hidden');

          if (data['files-list'] == '')
            document.getElementById('transfer-file-list').classList.add('hidden');
          if (document.getElementById('transfer-recv-list').dataset.content != data['files-list']) {
            document.getElementById('transfer-recv-list').innerHTML = data['files-list'];
            document.getElementById('transfer-recv-list').dataset.content = data['files-list'];
            if (data['files-list'] != '')
              document.getElementById('transfer-file-list').classList.add('popup');
          }
          if (data['files-list'] != '') {
            document.getElementById('transfer-file-list').classList.remove('hidden');
            if (document.getElementById('transfer-file-list').classList.contains('popup'))
              document.getElementById('transfer-file-list').addEventListener('click', function(){document.getElementById('transfer-file-list').classList.remove('popup');});
          }
        }
      }
    }
  }
  xhr.send(null);
  setTimeout(updateClip, 5000);
}
setTimeout(updateClip, 5000);
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
<input id="passcode" type="password" tabindex="1" autofocus onfocus="document.getElementById('unlock-fail').classList.add('hidden');" onKeypress="if((window.event ? event.keyCode : (event.which ? event.which : false))=='13'){var elem=document.getElementById('passcode');lockUnlock(elem.value);elem.value='';}">
<span id="unlock-fail" class="hidden red small"><br>Invalid pass code</span></p><br/>
<input class="compose" type="submit" value="Unlock" tabindex="2" onClick="var elem=document.getElementById('passcode');lockUnlock(elem.value);elem.value='';">
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

<?php if (isset($clipboard)) { // In Clipboard mode ?>
    if (typeof(e=document.getElementById('post-d')) != 'undefined' && e !== null)
      e.blur();
<?php } ?>

    document.getElementById('lock').style.display='block';
    document.getElementById('lock-hide').innerHTML='';
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
  xhr.withCredentials = true;
  xhr.open("POST", 'passcode.php', true);
  xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4) {
      if (xhr.status == 200) {
        setLockCookie();
        location.reload();
      } else {
        document.getElementById('unlock-fail').classList.remove('hidden');
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

<?php if (!isset($clipboard) && !isset($note)) { // In New Note mode ?>

if (typeof window.sessionStorage != 'undefined') {

  if (typeof window.sessionStorage['new_id'] != 'undefined' && window.sessionStorage['new_id'] !== null && window.sessionStorage['new_id']) {

    if (typeof(e=document.getElementById('new_id')) != 'undefined' && e !== null) {
      e.value = (url_id = window.sessionStorage['new_id']);
    }

    if (typeof window.sessionStorage['post-d'] != 'undefined' && window.sessionStorage['post-d'] !== null && window.sessionStorage['post-d'] && typeof(e=document.getElementById('post-d')) != 'undefined' && e !== null) {
      e.value = window.sessionStorage['post-d'];
    }

    if (typeof window.sessionStorage['attachment-list'] != 'undefined' && window.sessionStorage['attachment-list'] !== null && window.sessionStorage['attachment-list'] && typeof(e=document.getElementById('attachment-list')) != 'undefined' && e !== null) {
      e.innerHTML = window.sessionStorage['attachment-list'];
    }

    var elems = document.getElementsByClassName('autoDraft');
    for (var i = 0; i < elems.length; i++) {
      var e = elems[i];

      if (e.type == 'radio') {
        if (typeof window.sessionStorage[e.name] != 'undefined' && window.sessionStorage[e.name] !== null && window.sessionStorage[e.name]) {
          if (e.value == window.sessionStorage[e.name]) {
            e.checked = true;
          } else {
            e.checked = false;
          }
        }
      } else {
        if (typeof window.sessionStorage[e.name] != 'undefined' && window.sessionStorage[e.name] !== null && window.sessionStorage[e.name]) {
          e.value = window.sessionStorage[e.name];
        }
      }

    }

  } else {

    window.sessionStorage['new_id'] = url_id;
    window.sessionStorage['post-d'] = '';
    window.sessionStorage['attachment-list'] = '';

    var elems = document.getElementsByClassName('autoDraft');
    for (var i = 0; i < elems.length; i++) {
      var e = elems[i];
      window.sessionStorage[e.name] = '';
    }

  }

}

<?php } ?>

</script>
<?php } ?>

<?php } ?>

<script>
document.addEventListener('gesturestart', function (e) {
  e.preventDefault();
});
</script>

</body>
</html>
