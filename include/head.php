<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($site_name); ?></title>
<?php echo ($site_description ? '<meta name="description" content="'.htmlspecialchars($site_description).'" />' : ''); ?>
<link rel="stylesheet" href="include/style.css" type="text/css" media="all" />
<?php if ((isset($post) && $post) || (isset($single) && $single)) { ?>
<link rel="stylesheet" href="include/readability.css" type="text/css" media="all" />
<link rel="stylesheet" href="include/highlight/styles/xcode.css">
<?php } ?>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="shortcut icon" href="favicon.ico" />
</head>

<div id="header">
<div id="header-content">
<div id="logo">
<h1><a href="<?php echo (isset($clipboard) ? 'clipboard.php' : 'index.php'); ?>" title="<?php echo (isset($clipboard) ? 'Clipboard' : htmlspecialchars($site_name)); ?>"><?php echo (isset($clipboard) ? 'Clipboard' : htmlspecialchars($site_name)); ?></a></h1><?php echo (isset($clipboard) ? '<p>By '.htmlspecialchars($site_name).'</p>' : ($site_description ? '<p>'.htmlspecialchars($site_description).'</p>' : '')); ?>
</div>
<div class="clear">&nbsp;</div>
</div>
</div>
<!--end of header-->

<?php
if (!isset($post) || !$post) {
  if (file_exists($user_dir.'my_head.php'))
    include($user_dir.'my_head.php');
}
?>

<div id="wrap">
