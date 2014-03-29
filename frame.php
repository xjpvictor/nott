<?php
$login = true;
include(__DIR__ . '/init.php');

if (isset($_GET['url']))
  $url = rawurldecode($_GET['url']);
else
  $url = '';

if (!$auth && isset($_POST['p']) && isset($_POST['u'])) {
  if ($_POST['u'] == $user_name && verifypw($_POST['p'])) {
    if (!$otp || !isset($otp_key) || (isset($_POST['o']) && verifyotp($_POST['o']))) {
      session_regenerate_id(true);
      $_SESSION['time'] = time();
      $_SESSION['ip'] = $ip;
      $auth = true;
    }
  }
}
if (!$auth)
  session_destroy();
?>

<!DOCTYPE html>
<html lang="en-US">
  <head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
    <style type="text/css" media="all">
html, body, div, span, h1, p, a, input, textarea{font-family:"Lucida Sans Unicode","Lucida Grande","wenquanyi micro hei","droid sans fallback",FreeSans,Helvetica,Arial,"hiragino sans gb","stheiti","microsoft yahei",\5FAE\8F6F\96C5\9ED1,sans-serif !important;font-size:14px;line-height:1.8em;}
html{background:transparent;}
body{padding:0;margin:0;}
#frame{height:auto;min-width:400px;max-width:660px;margin:0;padding:10px;background:#f9f9f9;border-width:1px;border-color:#ccc;border-style:solid;color:#000;}
#wrap{padding:10px 2%;border:1px solid #ccc;background:#fff;}
h1{font-size:16px;color:#000;text-decoration:none;text-align:center;padding-bottom:20px;border-bottom:1px solid #000;}
h1 a{font-size:16px;color:#000;text-decoration:none;}
h1 a:visited{color:#000;text-decoration:none;}
h1 a:hover{color:#d4291f;text-decoration:none;}
#cancel,#cancel:visited{color:#444;text-decoration:none;}
#cancel:hover{color:#d4291f;text-decoration:none;}
a{color:#000;}
a:visited{color:#000;}
a:hover{color:#d4291f;}
input[type='text']{border:1px solid #bbb;background:#fff;color:#000;padding:1px 3px;line-height:1em;box-sizing:border-box;width:100%;margin:0;}
#form-wrap{padding:0;width:100%;margin:0 0 20px;position:relative;}
.button{color:#000;font-size:13px !important;padding:5px 10px !important;height:27px !important;line-height:1em;background:#fdfdfd;border:1px solid #bbb;border-radius:1px;text-align:center;}
.button:hover{background:#d4291f;cursor:pointer;color:#fff;border-color:#d4291f;}
label{vertical-align:top;}
label:hover{cursor:pointer;}
#text-d{width:97%;height:265px;padding:1%;border:1px solid #bbb;line-height:1.5em;}
#login{width:300px;margin:30px auto;padding:0px 30px;}
#login p{padding-bottom:0.5em;}
#login input{width:100%;}
a.compose,.compose{height:auto;width:100%;box-sizing:border-box;color:#000;background-color:#eee;text-align:center;text-decoration:none;height:60px;line-height:20px;font-size:18px;border:none;padding:20px 0;vertical-align:middle;display:block;margin-bottom:10px;}
input.compose{color:#fff;background:#3953d4;}
a.compose:hover,.compose:hover{background-color:#3953d4;text-decoration:none;cursor:pointer;color:#fff;}
input.compose:hover{background-color:#ca2017;}
p#otp{padding:1em 0 2em;text-align:center;}
#create{text-align:center;}
#more-control{text-align:center;background:#eee;}
.hide{display:none !important;}
    </style>
  </head>
<body>
<div id="frame">
<div id="wrap">
  <h1><a href="<?php echo $site_url; ?>"><?php echo htmlentities($site_name); ?></a></h1>
<div id="form-wrap">
<?php if (isset($_GET['id'])) { ?>
<div id="create">
<p>Note created!</p>
<a class="compose" href="<?php echo $site_url; ?>?id=<?php echo $_GET['id']; ?>" onclick="window.top.postMessage('nott_close', '<?php echo $url; ?>');" target="_blank">View</a>
<a class="compose" href="<?php echo $site_url; ?>edit.php?id=<?php echo $_GET['id']; ?>" onclick="window.top.postMessage('nott_close', '<?php echo $url; ?>');" target="_blank">Edit</a>
<p id="cancel"><a href="javascript:;" onclick="window.top.postMessage('nott_close', '<?php echo $url; ?>');">Close</a></p>
</div>
<?php } elseif (!$auth) { ?>
<form id="login" method="POST" action="<?php echo $site_url; ?>frame.php?url=<?php echo rawurlencode($url); ?>">
<p>Username:<br/>
<input required name="u" autofocus></p>
<p>Password:<br/>
<input required name="p" type="password"></p>
<?php if ($otp && isset($otp_key)) { ?>
<p>Google Authenticator code:<br/>
<input name="o"></p>
<?php } ?>
<textarea class="hide" id="text-d" name="d" style="display:hidden;"></textarea>
<input class="compose" type="submit" value="Log in" >
<p id="cancel"><a href="javascript:;" onclick="window.top.postMessage('nott_close', '<?php echo $url; ?>');">Close</a></p>
</form>
<?php } else { ?>
<form method="POST" action="<?php echo $site_url; ?>post.php?r=bookmarklet&url=<?php echo rawurlencode($url); ?>">
<textarea id="text-d" name="d" required>
<?php
if (isset($_POST['d']) && $_POST['d']) {
  echo htmlentities($_POST['d']);
  unset($_POST['d']);
}
?>
</textarea>
<p id="more-control" onclick="toggleClass('more', 'hide')">Options</p>
<div id="more" class="hide">
<p>URL:<br/>
<input name="u" type="text" value="<?php echo (isset($_GET['href']) && $_GET['href'] ? htmlentities(rawurldecode($_GET['href'])) : ''); ?>"></p>
<p>Privacy:<br/>
<label><input type="radio" name="p" value="1" <?php echo ($default_privacy ? 'checked' : ''); ?>> Public</label><br/><label><input type="radio" name="p" value="0" <?php echo (!$default_privacy ? 'checked' : ''); ?>> Private</label></p>
</div>
<input type="submit" class="button" value="Post" id="submit-button" />
&nbsp;&nbsp;<a href="javascript:;" onclick="window.top.postMessage('nott_close', '<?php echo $url; ?>');">Close</a>
</form>
<?php } ?>
</div>
</div>
</div>
<script>
window.onload = function() {
  window.addEventListener('message', function(e) {
    if (e.origin == '<?php echo $url; ?>') {
      var message = e.data;
      document.getElementById('text-d').innerHTML = message;
    }
  });
}
function toggleClass(id, cls) {
  if (elem = document.getElementById(id)) {
    if ((' ' + elem.className + ' ').indexOf(' ' + cls + ' ') > -1) {
      document.getElementById(id).classList.remove(cls);
    } else {
      document.getElementById(id).classList.add(cls);
    }
  }
}
</script>
</body>
</html>
