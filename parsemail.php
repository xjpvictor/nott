<?php
if (!function_exists('imap_open')) {
  http_response_code(403);
  exit('IMAP extension not installed');
}

define('NOINIT', true);
include(__DIR__ . '/init.php');

$img = __DIR__ . '/favicon.ico';

if (!isset($_GET[($h = hash('md5', $_SERVER['SCRIPT_FILENAME']))]) || $_GET[$h] != '1') {
  if (ob_get_level())
    ob_end_clean();
  ob_start();
  header('HTTP/1.1 200 Ok');
  header('Content-Type: image/png');
  header("Cache-Control: no-cache, must-revalidate");
  header("Pragma: no-cache");
  header('Expires: '.gmdate('D, d M Y H:i:s', time()).' GMT');
  $size=ob_get_length();
  header("Content-Length: ".($size + filesize($img)));
  header("Connection: close");
  readfile($img);
  ob_end_flush();
  flush();
  if (function_exists('fastcgi_finish_request'))
    fastcgi_finish_request();
  if (session_id())
    session_write_close();

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . ($_GET ? '&' : '?').hash('md5', $_SERVER['SCRIPT_FILENAME']).'=1');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  if ($_POST) {
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
  }
  curl_exec($ch);
  curl_close($ch);

  exit;
}

include(__DIR__ . '/functions_mail.php');

$conn = getimap($mail_server, $mail_port, $mail_service, $mail_cert, $mail_ssl, $mail_tls, $mail_folder, $mail_box, $mail_pwd);
$mails = getmails($conn);

if ($mails) {
  foreach ($mails as $mail) {
    $header = $mail[0];
    $body = $mail[1]['content'];
    $attachment = $mail[1]['attachment'];
    $inline = $mail[1]['inline'];
    $note = false;

    if (($header['recent'] == 'N' || $header['unseen'] == 'U') && $header['deleted'] !== 'D' && $header['draft'] !== 'X' && isset($header['from'][0]['addr']) && $header['from'][0]['addr'] && $allowed_mail && in_array($header['from'][0]['addr'], $allowed_mail) && (!$mail_passphrase || (isset($header['subject']) && (strpos($header['subject'], $mail_passphrase.' ') === 0 || $header['subject'] === $mail_passphrase)))) {
      $_POST['t'] = 'email,';
      if ($header['subject']) {
        if (($p = stripos($header['subject'], 'tags:')) !== false) {
          $_POST['t'] .= substr($header['subject'], $p+5);
          $options = substr($header['subject'], $p);
        } else {
          $options = $header['subject'];
        }
        if ($options) {
          $options = explode(' ', trim(strtolower($options)));
          if ($options) {
            foreach ($options as $option) {
              if ($option == 'public') {
                $_POST['p'] = 1;
              } elseif ($option == 'private') {
                $_POST['p'] = 0;
              } elseif (($u = isurl($option))) {
                $note = true;
                $_POST['u'] = $u;
              }
            }
          }
        }
      }

      if ($body) {
        $note = true;
        $_POST['d'] = toutf8($body);
      }

      if ($attachment) {
        $note = true;
        $t = time();
        $i = 0;
        foreach ($attachment as $file_name => $file) {
          $_POST['file'][] = 'data:;base64,' . $file;
          $_GET['name'][] = $t.'-'.$i.'-'.rawurlencode($file_name);
          $i++;
        }
      }

      if ($note && $inline) {
        foreach ($inline as $file_name => $file) {
          $_POST['file'][] = 'data:;base64,' . $file;
          $_GET['name'][] = 'email-'.$file_name;
        }
      }

      if ($note) {
        postnote();
      }
    }
    imap_delete($conn, $header['msgno']);
  }
}

imap_expunge($conn);
imap_close($conn);

exit;
