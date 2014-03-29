<div id="sidebar">
<?php if (isset($post) && $post) { ?>
<input class="widget compose" type="submit" value="<?php echo (isset($note) ? 'Update' : 'Add Note'); ?>" />

<span class="widget compose view" onclick="if(!document.getElementById('edit-button').className){document.getElementById('readability').innerHTML=converter.makeHtml(document.getElementById('post-d').value);noteSH();this.innerHTML='Edit';uploadAddClass('post-d','hide');uploadAddClass('edit-button','hide');uploadAddClass('edit-title','hide');uploadAddClass('preview','show');}else{this.innerHTML='Preview';uploadRemoveClass('post-d','hide');uploadRemoveClass('edit-button','hide');uploadRemoveClass('edit-title','hide');uploadRemoveClass('preview','show');}">Preview</span>
<?php echo (isset($note) ? '<a class="widget compose view" id="view" href="'.$site_url.'?id='.$note['id'].'">View</a><a class="widget compose" onclick="return confirm(\'Permanently delete this note?\');" id="delete" href="'.$site_url.'delete.php?id='.$note['id'].'">Delete Note</a>' : ''); ?>
<div class="widget">
<h2>Tags</h2>
<input name="t" type="text" id="post-t" value="<?php if (isset($note) && $note['tags']) {foreach ($note['tags'] as $tag) {echo $tag.',';}} ?>">
<?php
if ($tags = gettaglist()) {
  echo '<p>';
  foreach ($tags as $tag => $ids) {
    echo '<span class="tag" onclick="var e=document.getElementById(\'post-t\');e.value=e.value+this.innerHTML+\',\';">'.$tag.'</span>';
  }
  echo '</p>';
}
?>
</div>
<div class="widget">
<h2>Privacy</h2>
<label><input type="radio" name="p" value="1" <?php echo ((isset($note) && $note['public']) ? 'checked' : (!isset($note) && $default_privacy ? 'checked' : '')); ?>> Public</label><br/><label><input type="radio" name="p" value="0" <?php echo (isset($note) && !$note['public'] ? 'checked' : (!isset($note) && !$default_privacy ? 'checked' : '')); ?>> Private</label>
</div>
<div class="widget" id="attachment">
<h2>Attachment</h2>
<div id="upload-input" class="file-button-wrap">
<span id="upload-clear" onclick="uploadClear()">Cancel</span>
<span id="upload-button" class="file-button">Upload</span>
<span id="upload-file-button-wrap" class="file-button-hide-wrap">
<input type="file" multiple id="upload-file-button" class="file-button-hide" name="files[]"
 onchange="
   if (!window.File || !window.FileList || !window.FileReader || !window.XMLHttpRequest) {
     var str = '';
     var t = Math.round(+new Date()/1000);
     var files = this.files;
     for (var i=0;i<files.length;i++) {
      str += '<div class=\'upload-file\' data-id=\''+t+'-'+i+'\'>'+uploadStringHtmlentities(files[i].name)+'<span class=\'delete\' id=\'upload-cancel-'+t+'-'+i+'\' onclick=\'this.parentNode.parentNode.removeChild(this.parentNode);\'>&#10007;</span><div class=\'upload-progress\' id=\'upload-progress-'+t+'-'+i+'\'></div></div>';
     }
     document.getElementById('upload-list').innerHTML = str;
   }
 "
></span>
<div class="clear">&nbsp;</div>
</div>
<div id="upload-drop">
<div id="upload-drop-text">or drop files here</div>
<div id="upload-list"></div>
</div>
<div id="uploadhtmlentities" style="display:none;"></div>
<div id="attachment-list-wrap">
<div id="attachment-list">
<?php
if (isset($note) && $list = getattachment($note['id'])) {
  foreach ($list as $attachment) {
    echo displayattachment($note['id'], parseattachmentname($attachment), 0, 1);
  }
}
?>
</div>
</div>
<div class="clear">&nbsp;</div>
</div>
<?php } else { ?>
<?php if ($auth) { ?>
<a class="widget compose" alt="Add note" href="<?php echo $site_url; ?>edit.php">Add Note</a>
<?php } ?>
<form id="search" method="get" action="/">
<input type="text" name="s"><input type="submit" value="">
</form>
<?php
if (isset($note)) {
  if ($note['tags']) {
?>
<div class="widget">
<h2>Tags</h2>
<?php
    echo '<p>';
    foreach ($note['tags'] as $tag) {
      echo '<a class="tag" href="'.$site_url.'?tag='.rawurlencode($tag).'" title="'.$tag.'">'.$tag.'</a>';
    }
    echo '</p>';
?>
</div>
<?php
  }
} elseif ($tags = gettaglist()) {
?>
<div class="widget">
<h2>Tags</h2>
<?php
  echo '<p>';
  foreach ($tags as $tag => $ids) {
    echo '<a class="tag" href="'.$site_url.'?tag='.rawurlencode($tag).'" title="'.$tag.'">'.$tag.'</a>';
  }
  echo '</p>';
?>
</div>
<?php
}
?>
<?php if (isset($note) && $note['source']['url']) { ?>
<div class="widget">
<h2>Source</h2>
<p id="source-u"><a href="<?php echo $note['source']['url']; ?>" target="_blank" title="<?php ($note['source']['title'] ? $note['source']['title'] : $note['source']['url']); ?>"><?php echo ($note['source']['title'] ? $note['source']['title'] : htmlentities($note['source']['url'])); ?></a></p>
<?php echo ($note['source']['description'] ? '<p id="source-d">'.$note['source']['description'].'</p>' : ''); ?>
</div>
<?php } ?>
<?php if (isset($single) && $single && isset($note) && ($auth || $note['public']) && $list = getattachment($note['id'])) { ?>
<div class="widget" id="attachment">
<h2>Attachment</h2>
<div id="attachment-list-wrap">
<div id="attachment-list">
<?php
foreach ($list as $attachment) {
  echo displayattachment($note['id'], parseattachmentname($attachment));
}
?>
</div>
</div>
</div>
<?php } ?>
<?php } ?>

<?php
if (!isset($post) || !$post) {
  if (file_exists($user_dir.'my_sidebar.php'))
    include($user_dir.'my_sidebar.php');
}
?>

<div class="widget" id="meta">
<h2>Meta</h2>
<?php
if ($auth) {
  if (!isset($post) || !$post) {
    echo '<p id="bookmarklet">Note with bookmarklet <a href="javascript:var url=\''.$site_url.'\';var x=document.createElement(\'SCRIPT\');x.type=\'text/javascript\';x.src=url+\'bookmarklet.js\';document.getElementsByTagName(\'head\')[0].appendChild(x);void(0)">Clip to '.htmlentities($site_name).'</a></p>';
  }
  if (!isset($note)) {
?>
<form id="kindle-upload" method="POST" action="/kindle.php" enctype="multipart/form-data">
<p>Import kindle highlights</p>
<div id="kindle-button-wrap">
<div class="file-button-wrap" id="kindle-button">
<span class="file-button">Import</span>
<span class="file-button-hide-wrap">
<input type="file" name="kindle" class="file-button-hide" accept="text/plain" onchange="document.getElementById('kindle-upload').submit();">
</span>
<div class="clear">&nbsp;</div>
</div>
</div>
</form>
<form id="evernote-upload" method="POST" action="/evernote.php" enctype="multipart/form-data">
<p>Import evernote notes (.enex)</p>
<div id="evernote-button-wrap">
<div class="file-button-wrap" id="evernote-button">
<span class="file-button">Import</span>
<span class="file-button-hide-wrap">
<input type="file" name="evernote" class="file-button-hide" accept="application/enex+xml" onchange="document.getElementById('evernote-upload').submit();">
</span>
<div class="clear">&nbsp;</div>
</div>
</div>
</form>
<?php
  }
  echo '<a title="Logout" href="'.$site_url.'logout.php?url='.rawurlencode($site_url.(isset($note) ? '?id='.$note['id'] : (isset($_GET['p']) && $_GET['p'] && is_numeric($_GET['p']) && $_GET['p'] > 1 ? '?p='.$_GET['p'] : ''))).'">Logout</a>';
} else
  echo '<a title="Login" href="'.$site_url.'login.php?url='.rawurlencode($site_url.(isset($note) ? '?id='.$note['id'] : (isset($_GET['p']) && $_GET['p'] && is_numeric($_GET['p']) && $_GET['p'] > 1 ? '?p='.$_GET['p'] : ''))).'">Login</a>';
?>
</div>
</div>
<!--end of sidebar-->

