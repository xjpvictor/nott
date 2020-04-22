<?php
$login = true;
include(__DIR__ . '/init.php');

if (isset($_GET['url']))
  $url = rawurldecode($_GET['url']);
else
  $url = $site_url;

if ($auth) {
  if (isset($_GET['action']) && strtolower($_GET['action']) == 'auth') {
    if (isset($_GET['code']) && $_GET['code'])
      file_put_contents($tmp_dir.'auth_'.$_GET['code'].'.tmp', $user_name, LOCK_EX);
  }
  header("Location: $url");
  exit(0);
}

if (isset($_GET['action']) && strtolower($_GET['action']) == 'qr') {
  header('Content-Type: text/event-stream');
  echo 'retry: 15000'."\n".'data: '.(isset($_GET['code']) && file_exists(($au_f = $tmp_dir.'auth_'.$_GET['code'].'.tmp')) && file_get_contents($au_f) && time() - filemtime($au_f) <= $auth_code_expiry ? '1' : '0')."\n\n";
  exit;
}

if (
  (isset($_POST['c']) && file_exists(($au_f = $tmp_dir.'auth_'.$_POST['c'].'.tmp')) && file_get_contents($au_f) == $user_name && time() - filemtime($au_f) <= $auth_code_expiry && (($otp = false) || 1)) ||
  (isset($_POST['p']) && isset($_POST['u']) && $_POST['u'] == $user_name && verifypw($_POST['p']))
) {
  if (isset($au_f) && file_exists($au_f))
    unlink($au_f);
  if (!$otp || !isset($otp_key) || (isset($_POST['o']) && verifyotp($_POST['o']))) {
    session_regenerate_id(true);
    $_SESSION['auth'] = 1;
    setcookie('_nott_lock', time()*1000, time() + 31536000, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 1 : 0));
    if ($otp && !isset($otp_key)) {
      $otp_key_gen = generaterandomstring();
      file_put_contents(__DIR__.'/config.php', '$otp_key = \''.$otp_key_gen.'\'; //remove this line to regenerate otp secret key', LOCK_EX | FILE_APPEND);
    } else {
      header("Location: $url");
      exit(0);
    }
  }
}
if (!isset($otp_key_gen) && session_status() === PHP_SESSION_ACTIVE) {
  setcookie($cookie_name, '', 1, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 1 : 0), 1);
  session_destroy();
}

include($include_dir.'head.php');
?>

<div id="login">
<?php if (!isset($otp_key_gen)) { ?>
<p>Please log in</p>
<form method="post" action="login.php?url=<?php echo rawurlencode($url); ?>" id="login-form">
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
<input type="text" id="auth-code" class="hidden" name="c">
</form>
<!-- Scan QR to login -->
<p>Or Scan to login</p>
<div id="qrcode-auth"></div>
<script src="include/qrcode.js"></script>
<script>
function authqr() {
  document.getElementById("auth-code").value = "<?php echo ($c = hash($sec_hash_algo, generaterandomstring(32, 1))); ?>";
  var qr = document.getElementById("qrcode-auth");
  qr.innerHTML = "";
  new QRCode(qr,{text:"<?php echo $site_url; ?>login.php?action=auth&code=<?php echo $c; ?>", width: 150, height: 150});
  setTimeout("authqr()", <?php echo ((max(60, ($auth_code_expiry - 60))) * 1000); ?>);
}
authqr();
if (typeof(EventSource) !== 'undefined') {
  var eSource = new EventSource('login.php?action=qr&code=<?php echo $c; ?>', {withCredentials: true});
  eSource.onmessage = function(event) {
    if (event.data) {
      if (event.data == 1)
        document.getElementById('login-form').submit();
    }
  };
}
</script>
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
