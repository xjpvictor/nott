<?php
include(__DIR__ . '/init.php');

require_once(realpath($include_dir.'device-detector/vendor/autoload.php'));
use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\DeviceParserAbstract;

function parse_client_ua($ua) {
  if (!$ua)
    return array('ua' => $ua);

  $dd = new DeviceDetector($ua);

  $dd->discardBotInformation();

  $dd->parse();

  if (!$dd->isBot()) {
    $client = $dd->getClient();
    $os = $dd->getOs();
    $brand = $dd->getBrandName();
    $device = $dd->getModel();
  }

  $client = array(
    'client' => (isset($client['name']) && $client['name'] ? $client['name'] : ''),
    'platform' => ($device ? ($brand ? $brand.' ' : '').$device : (isset($os['name']) && $os['name'] ? $os['name'].(isset($os['version']) && $os['version'] ? ' '.$os['version'] : '') : '')),
    'ua' => $ua
  );

  return $client;
}

if (!$auth) {
  http_response_code(403);
  $error = 'Access denied. Please <a title="login" href="login.php?url='.rawurlencode($site_url.'clipboard.php').'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

$post = true;
$clipboard = (file_exists($clipboard_file) ? file_get_contents($clipboard_file) : '');

if (isset($_GET['clipts']) && $_GET['clipts'] && isset($_GET['attachmentts']) && $_GET['attachmentts']) {
  $ua = parse_client_ua($_SERVER['HTTP_USER_AGENT']);
  $this_client = $tmp_dir.($this_client_hash = hash($str_hash_algo, $_SERVER['HTTP_USER_AGENT'])).'.client';

  $clients = glob($tmp_dir.'*.client', GLOB_NOSORT);
  $clients_list = '';
  foreach ($clients as $client) {
    $client_hash = substr($client, ($i = strrpos($client, '/')+1), strrpos($client, '.') - $i);
    if ($timestamp - ($ts = filemtime($client)) > 20) {
      if (file_exists($client))
        unlink($client);
      $files = glob($tmp_dir.$client_hash.'-*.transfer', GLOB_NOSORT);
      if ($files) {
        foreach ($files as $f) {
          if (file_exists($f))
            unlink($f);
        }
      }
    } else {
      if ($client !== $this_client) {
        if ($timestamp - $ts <= 10) {
          $clients_list .= '<div class="transfer-clients file-button-wrap" data-client="'.$client_hash.'" ondrop="fileTransfer(event, \''.$client_hash.'-'.$this_client_hash.'\');"><span class="file-button-hide-wrap"><input type="file" multiple class="transfer-upload-button file-button-hide" name="transfer" data-client="'.$client_hash.'" onchange="fileTransfer(event, \''.$client_hash.'-'.$this_client_hash.'\');"></span><span class="file-button">&raquo;&nbsp;'.file_get_contents($client).'</span><div class="clear">&nbsp;</div></div>';
        }
      }
    }
  }
  $files = glob($tmp_dir.$this_client_hash.'-*.transfer', GLOB_NOSORT);
  $files_list = '';
  if ($files) {
    if (!file_exists($this_client)) {
      foreach ($files as $f) {
        if (file_exists($f))
          unlink($f);
      }
    } else {
      foreach ($files as $f) {
        if (file_exists($f)) {
          $file_name = substr($f, strrpos($f, '/')+1);
          $from_hash = substr($file_name, ($i = strpos($file_name, '-')+1), ($ii = strpos($file_name, '-', $i)) - $i);
          $file_title = str_replace('.transfer', '', substr($file_name, $ii + 1));
          $file_ext = substr($file_title, strrpos($file_title, '.'));
          $files_list .= '<div class="transfer-files"><p><span><a href="attachment.php?id=0&action=get&tmp=1&transfer=1&name='.rawurlencode($file_name).'" target="_blank" title="'.htmlentities($file_title).'">'.htmlentities(mb_substr($file_title, 0, 12).(mb_strlen($file_title) > 12 ? '...' : '').$file_ext).'</a></span>'.(file_exists($tmp_dir.$from_hash.'.client') ? '<span>from: '.htmlentities(file_get_contents($tmp_dir.$from_hash.'.client')).'</span>' : '').'</p></div>';
        }
      }
    }
  }
  file_put_contents($this_client, (isset($ua['client']) && $ua['client'] ? $ua['client'].(isset($ua['platform']) && $ua['platform'] ? ' ('.$ua['platform'].')' : '') : $ua['ua']));

  if ((!file_exists($clipboard_file) || filemtime($clipboard_file) <= $_GET['clipts']) && (!file_exists($clipboard_attachment_cache) || filemtime($clipboard_attachment_cache) <= $_GET['attachmentts'])) {
    echo json_encode(array(
      'this-client' => $this_client_hash,
      'clients-list' => $clients_list,
      'files-list' => $files_list,
    ));
    exit;
  }

  $attachments = '';
  if (($list = getattachment(0))) {
    foreach ($list as $attachment) {
      $attachments .= displayattachment(0, parseattachmentname($attachment), 0, 1);
    }
  }
  if ($attachments !== file_get_contents($clipboard_attachment_cache))
    file_put_contents($clipboard_attachment_cache, $attachments, LOCK_EX);

  echo json_encode(array(
    'clip-ts' => filemtime($clipboard_file),
    'attachment-ts' => filemtime($clipboard_attachment_cache),
    'post-d' => $clipboard,
    'attachment-list' => $attachments,
    'this-client' => $this_client_hash,
    'clients-list' => $clients_list,
    'files-list' => $files_list,
  ));

  exit;
} elseif (isset($_GET['c']) && $_GET['c']) {
  $clipboard .= "\n\n".urldecode($_GET['c']);
  file_put_contents($clipboard_file, $clipboard);
  chmod($clipboard_file, 0600);
  echo '<html><body><script>window.confirm("Text copied to clipboard.");window.location="'.$site_url.'clipboard.php";</script></body></html>';
  //header('Location: clipboard.php');
  exit;
} elseif (isset($_POST['d']) && $_POST['d']) {
  $clipboard = $_POST['d'];
  file_put_contents($clipboard_file, $clipboard);
  chmod($clipboard_file, 0600);
  if (isset($_GET['r']) && $_GET['r'] == 'bookmarklet') {
    header('Location: '.$site_url.'frame.php?clip=true&close=true'.(isset($_GET['url']) ? '&url='.rawurlencode(rawurldecode($_GET['url'])) : ''));
    exit;
  }
}

include($include_dir . 'head.php');
?>
<form id="post" method="POST" action="clipboard.php" enctype="multipart/form-data">
<div id="main">
<div class="content">
<textarea id="post-d" name="d" onblur="autoSave(this.value);" onpaste="pasteImage(event);"><?php echo htmlentities($clipboard); ?></textarea>
</div>
</div>
<div id="clip-ts" style="display:none;"></div>
<div id="attachment-ts" style="display:none;"></div>
<!--end of main-->

<?php
include($include_dir . 'sidebar.php');
?>
</form>
<?php
include($include_dir . 'foot.php');
?>
