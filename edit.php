<?php
include(__DIR__ . '/init.php');

if (!$auth) {
  http_response_code(403);
  if (isset($_GET['id']) && $_GET['id'] && is_numeric($_GET['id']))
    $url = 'login.php?url='.rawurlencode($site_url.'edit.php?id='.$_GET['id']);
  $error = 'Access denied. Please <a title="login" href="'.(isset($url) ? $url : 'login.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_GET['id']) && $_GET['id']) {
  if (!($note = getnote($_GET['id'], 1))) {
    http_response_code(404);
    $error = 'Sorry, note not found.';
    include($include_dir . 'error.php');
    exit;
  }
}

$post = true;
include($include_dir . 'head.php');
?>
<form id="post" method="POST" action="post.php<?php echo (isset($note) ? '?id='.$note['id'] : ''); ?>" enctype="multipart/form-data">
<div id="main">
<div class="content">
<div id="edit-button">
<span id-title="Heading 1" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHR('====')">H1</span>
<span id-title="Heading 2" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHR('----')">H2</span>
<span id-title="Heading 3" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('### ')">H3</span>
<span id-title="Heading 4" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('#### ')">H4</span>
<span id-title="Heading 5" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('##### ')">H5</span>
<span id-title="Heading 6" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('###### ')">H6</span>
<span id-title="Italic" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddStyle('*')"><span style="font-style:italic;">i</span></span>
<span id-title="Bold" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddStyle('**')"><span style="font-weight:bold;">b</span></span>
<span id-title="Unordered list" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('- ')">&middot; -</span>
<span id-title="Ordered list" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('1. ')">1. -</span>
<span id-title="Blockquote" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('> ')">&gt;</span>
<span id-title="Inline code" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddStyle('`')">code</span>
<span id-title="Code block" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHead('    ')">&lt;/&gt;</span>
<span id-title="url" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddURL()">url</span>
<span id-title="Add break" class="button" onmouseover="mdTitle(this.getAttribute('id-title'))" onmouseout="mdTitle('')" onclick="mdAddHR('***')">---</span>
<div class="clear">&nbsp;</div>
</div>
<div id="edit-title">
Markdown supported
</div>
<?php $new_id = (!isset($note) ? generaterandomstring(4) : ''); ?>
<?php echo (!isset($note) ? '<input name="tmp" type="hidden" value="'.$new_id.'">' : ''); ?>
<textarea id="post-d" name="d"><?php echo ((isset($note) && $note['content']) ? htmlentities($note['content']) : ''); ?></textarea>
<div id="preview"><h1>Preview</h1><div id="readability"></div></div>
</div>
<div id="url">
<h2>Source</h2>
<p>URL:</p>
<input name="u" type="text" <?php echo ((isset($note) && $note['source']['url']) ? 'value="'.htmlentities($note['source']['url']).'"' : ''); ?>>
<?php echo ((isset($note) && $note['source']['title']) ? '<p>Site title:</p><p>'.$note['source']['title'].'</p><br/>' : ''); ?>
<?php echo ((isset($note) && $note['source']['description']) ? '<p>Site description:</p><p>'.$note['source']['description'].'</p>' : ''); ?>
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
