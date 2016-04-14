<?php
$login = true;
include(__DIR__ . '/init.php');

if (isset($_GET['url']))
  $url = rawurldecode($_GET['url']);
else
  $url = $site_url;

if ($auth) {
  header("Location: $url");
  exit(0);
}

if (isset($_POST['p']) && isset($_POST['u'])) {
  if ($_POST['u'] == $user_name && verifypw($_POST['p'])) {
    if (!$otp || !isset($otp_key) || (isset($_POST['o']) && verifyotp($_POST['o']))) {
      session_regenerate_id(true);
      $_SESSION['auth'] = 1;
      setcookie('_nott_lock', microtime(), 31536000, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 1 : 0), 1);
      if ($otp && !isset($otp_key)) {
        $otp_key_gen = generaterandomstring();
        file_put_contents(__DIR__.'/config.php', '$otp_key = \''.$otp_key_gen.'\'; //remove this line to regenerate otp secret key', LOCK_EX | FILE_APPEND);
      } else {
        header("Location: $url");
        exit(0);
      }
    }
  }
}
if (!isset($otp_key_gen))
  session_destroy();

include($include_dir.'head.php');
?>

<div id="login">
<?php if (!isset($otp_key_gen)) { ?>
<p>Please log in</p>
<form method="post" action="login.php?url=<?php echo rawurlencode($url); ?>">
<p>Username:<br/>
<input required name="u" autofocus></p>
<p>Password:<br/>
<input required name="p" type="password"></p>
<?php if ($otp && isset($otp_key)) { ?>
<p>OTP Authenticator code:<br/>
<input name="o"></p>
<?php } ?>
<label><input type="checkbox" name="r" value="1"> Remember me</label><br/>
<br/><input class="compose" type="submit" value="Log in" >
</form>
<?php } else { ?>
<p>Please add the secret key to your authenticator</p>
<p id="otp"><?php echo $otp_key_gen; ?></p>
<p>Or scan the qrcode</p>
<div id="qrcode"></div>
<p><a class="compose" href="<?php echo $url; ?>">Continue login...</a></p>
<script src="include/qrcode.js"></script>
<script>new QRCode(document.getElementById("qrcode"),{text:"otpauth://totp/<?php echo ($site_name ? rawurlencode(rawurlencode($site_name).': '.$user_name) : rawurlencode($user_name)); ?>?secret=<?php echo $otp_key_gen; ?><?php echo ($site_name ? '&issuer='.rawurlencode($site_name) : ''); ?>",width:300,height:300});</script>
<?php } ?>
</div>

<?php include($include_dir.'foot.php'); ?>
