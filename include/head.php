<!DOCTYPE html>
<html lang="en-US">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />
<meta name="robots" content="noindex, nofollow">
<title><?php echo (isset($clipboard) ? 'Clipboard by ' : (isset($paper) ? 'Paper by ' : '')).htmlspecialchars($site_name); ?></title>
<?php echo ($site_description ? '<meta name="description" content="'.htmlspecialchars($site_description).'" />' : ''); ?>
<link rel="stylesheet" href="include/style.css" type="text/css" media="all" />
<?php if (((isset($post) && $post) || (isset($single) && $single)) && !isset($paper)) { ?>
<link rel="stylesheet" href="include/readability.css" type="text/css" media="all" />
<link rel="stylesheet" href="include/highlight/styles/xcode.css">
<?php } ?>
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="shortcut icon" href="favicon.ico" />
<link rel="apple-touch-icon" href="webapp-icon.png" />
<link rel="icon" href="webapp-icon.png" />

<meta name="apple-mobile-web-app-title" content="<?php echo (isset($clipboard) ? 'Clipboard' : (isset($paper) ? 'Paper' : 'NOTT')); ?>" />
<meta name="application-name" content="<?php echo (isset($clipboard) ? 'Clipboard' : (isset($paper) ? 'Paper' : 'NOTT')); ?>" />

</head>

<div id="lock-hide"<?php echo (isset($passcode) && $passcode && (isset($clipboard) || (isset($single) && $single) || (isset($post) && $post)) && !isset($paper) ? ' style="display:none;"' : ''); ?>>

<?php if (!isset($paper)) { ?>
<div id="header">
<div id="header-content">
<div id="logo">
<div id="lck-title" style="display:none;"><?php echo (isset($clipboard) ? 'Clipboard by ' : '').htmlspecialchars($site_name); ?></div>
<h1><a href="<?php echo (isset($clipboard) ? 'clipboard.php' : 'index.php'); ?>" title="<?php echo (isset($clipboard) ? 'Clipboard' : htmlspecialchars($site_name)); ?>"><?php echo (isset($clipboard) ? 'Clipboard' : htmlspecialchars($site_name)); ?></a></h1><?php echo (isset($clipboard) ? '<p>By '.htmlspecialchars($site_name).'</p>' : ($site_description ? '<p>'.htmlspecialchars($site_description).'</p>' : '')); ?>
</div>
<div class="clear">&nbsp;</div>
</div>
</div>
<!--end of header-->
<?php } ?>

<?php
if (!isset($post) || !$post) {
  if (file_exists($user_dir.'my_head.php'))
    include($user_dir.'my_head.php');
}
?>

<div id="wrap">
