<?php
function toutf8($str) {
  if (!$str)
    return $str;
  if (mb_detect_encoding($str, 'ascii, utf-8'))
    return $str;
  elseif ($encode = mb_detect_encoding($str, 'gbk, gb2312, gb18030, big5, big5-hkscs, iso-8859-1, iso-8859-2, iso-8859-3, iso-8859-4, iso-8859-5, iso-8859-6, iso-8859-7, iso-8859-8, iso-8859-9, iso-8859-10, iso-8859-11, iso-8859-13, iso-8859-14, iso-8859-15, iso-8859-16, utf-16, utf-32, windows-1250, windows-1251, windows-1252, windows-1253, windows-1254, windows-1255, windows-1256, windows-1257, windows-1258, euc-jp, euc-kr, euc-tw, hz-gb-2312, ibm866, iso-2022-cn, iso-2022-jp, iso-2022-jp-1, iso-2022-kr, koi8-r, koi8-u, shift-jis, us-ascii, viscii'))
    return mb_convert_encoding($str, 'UTF-8', $encode);
  else
    return '';
}
function isurl($str) {
  if ($str == '')
    return $str;
  $str = strtolower($str);
  if (filter_var($str, FILTER_VALIDATE_URL)) {
    if (strpos($str, 'http://') === false && strpos($str, 'https://') === false)
      $str = 'http://'.$str;
    if (strpos($str, '/', 9) === false)
      $str .= '/';
    return filter_var($str, FILTER_VALIDATE_URL);
  } else
    return false;
}
function escattr($match) {
  if (is_array($match))
    $match = '<'.$match[1].$match[6].'>';
  return preg_replace_callback('/<([^>]*\s+)?(on[^=]+|jsaction|data|data-[a-z]+|dynsrc|accesskey|tabindex|shape|srcset)\s*=\s*(("[^"]*")|(\'[^\']*\'))?(\s+[^>]*\/?)?>/i', 'escattr', $match);
}
function escpost($str, $id, $source, $edit = 0) {
  global $allowed_tags, $site_url;

  if (!$str)
    return '';

  $str = strip_tags($str, $allowed_tags);
  $url = preg_quote($site_url, '/');
  if (!$source) {
    $source = '';
    $source_d = '';
  } elseif ($p = strpos($source, '/', 9)) {
    $source_d = substr($source, 0, $p).'/';
  }

  $str = escattr($str);

  $search = array(
    '/'.$url.'attachment\.php\?(?:[^& =]+(?<!name|tmp)=[^& =]+&)*(?:name=([^& ]+)&)?(?:[^& =]+(?<!name|tmp)=[^& =]+&)*tmp=1(?:&[^& =]+(?<!name|tmp)=[^& =]+)*(?:&name=([^& ]+))?(?:&[^& =]+(?<!name|tmp)=[^& =]+)*/i', //change image url for new post
    '/<\s*img ((?:[^>]*\s)*)src\s*=\s*("|\')cid:([^"\']*)("|\')(\s+[^>]*)?(\/)?>/i', //handle image in email
    '/<\s*a ((?:[^>]*\s)*)href\s*=\s*("|\')#([^"\']*)("|\')(\s+[^>]*)?(\/)?>/i', //handle relative url anchor point
    '/<\s*(a|img|iframe) ((?:[^>]*\s)*)(src|href)\s*=\s*("|\')\/\/([^"\']+)("|\')(\s+[^>]*)?(\/)?>/i', //handle url starting with double slash
    '/<\s*(a|img|iframe) ((?:[^>]*\s)*)(src|href)\s*=\s*("|\')\/([^"\'\/][^"\']+)("|\')(\s+[^>]*)?(\/)?>/i', //handle relative url
    '/<\s*blockquote(\s+[^>]*)?>[\r\n]+/i',
    '/\r\n/',
    '/\r/',
    '/\n[\s　(\xc2)(\xa0)]+\n/',
    '/<\s*br( +[^>]*)*\/?>\n?/i',
    '/<\/?p( +[^>]*)*>/i',
    '/<\s*\S+\s*>(\s|&nbsp;)*<\/\s*\1\s*>/',
    '/\n{2,}/',
    '/^\n+/',
    //'/(?<!  )\n/',
  );
  $replace = array(
    'attachment.php?id='.$id.'&name=$1$2&action=get',
    '<img $1src=$2attachment.php?id='.$id.'&cid=$3$4$5$6>',
    '<a $1href=$2'.$source.'#$3$4$5$6>',
    '<$1 $2$3=$4'.(strpos($source_d, 'https://') === 0 ? 'https://' : 'http://').'$5$6$7$8>',
    '<$1 $2$3=$4'.$source_d.'$5$6$7$8>',
    '<blockquote$1>',
    "\n",
    "\n",
    "\n\n\n",
    "\n",
    "\n\n",
    '',
    "\n\n",
    '',
    //"  \n",
  );
  $str = preg_replace($search, $replace, $str);
  $str = preg_replace_callback('/<img ((?:[^>]*\s)*)src\s*=\s*("|\')(?!'.$url.'|attachment\.php)([^"\']+)("|\')(\s+[^>]*)?(\/\s*)?>/i', function ($match) use ($id) {
    return '<img '.$match[1].'src='.$match[2].'attachment.php?id='.$id.'&cache='.rawurlencode($match[3]).$match[4].(isset($match[5]) ? $match[5] : '').(isset($match[6]) ? $match[6] : '/').'>';
  }, $str);

  return trim($str);
}
function getlist() {
  global $data_dir, $id_file, $tags_file, $kindle_file;

  $list = glob($data_dir.'*.json', GLOB_NOSORT);
  if ($list) {
    $list = array_diff($list, array($id_file, $tags_file, $kindle_file));
    natsort($list);
    return array_reverse($list);
  }
  return false;
}
function gettaglist($tag = null) {
  global $data_dir, $tags_file;

  if (file_exists($tags_file)) {
    $tags = json_decode(file_get_contents($tags_file), true);
    if (isset($tag) && isset($tags[$tag]))
      return $tags[$tag];
    elseif (isset($tag))
      return false;
    else
      return $tags;
  } else
    return false;
}
function getnote($id, $markdown = 0) {
  global $data_dir, $content_dir, $html_dir, $nlist;

  if (strpos($id, $data_dir) !== false)
    $id = substr($id, $start = (strrpos($id, '/')+1), strrpos($id, '.') - $start);

  if (file_exists($data_dir.$id.'.json') && $nlist !== false && ($index = array_search($data_dir.$id.'.json', $nlist)) !== false) {
    $data = json_decode(file_get_contents($data_dir.$id.'.json'), true);
    if (file_exists($content_dir.$id.'.txt')) {
      if ($markdown) {
        $data['content'] = file_get_contents($content_dir.$id.'.txt');
      } elseif (file_exists($html_dir.$id.'.html') && filemtime($html_dir.$id.'.html') >= filemtime($content_dir.$id.'.txt')) {
        $data['content'] = file_get_contents($html_dir.$id.'.html');
        chmod($html_dir.$id.'.html', 0600);
      } else {
        $parsedown = new Parsedown();
        $data['content'] = $parsedown->parse(file_get_contents($content_dir.$id.'.txt'));
        file_put_contents($html_dir.$id.'.html', $data['content'], LOCK_EX);
        chmod($html_dir.$id.'.html', 0600);
      }
    } else
      $data['content'] = '';
    if ($index)
      $data['next'] = substr($nlist[$index-1], $start = (strrpos($nlist[$index-1], '/')+1), strrpos($nlist[$index-1], '.') - $start);
    if ($index < count($nlist)-1)
      $data['prev'] = substr($nlist[$index+1], $start = (strrpos($nlist[$index+1], '/')+1), strrpos($nlist[$index+1], '.') - $start);
  } else
    $data = false;

  return $data;
}
function postnote($id = null) {
  global $data_dir, $content_dir, $html_dir, $default_privacy, $id_file, $mail_note_to, $mail_note_account, $mail_note_from, $site_name, $site_url;

  if ((!isset($_POST['d']) || !trim(strip_tags($_POST['d'], '<img>'))) && isset($_POST['u']) && isurl($_POST['u'])) {
    $_POST['d'] = $_POST['u'];
    $url = $_POST['u'];
  } elseif ((!isset($_POST['u']) || !$_POST['u']) && isset($_POST['d']) && trim(strip_tags($_POST['d'], '<img>')))
    $url = trim(strip_tags($_POST['d'], '<img>'));

  if (extension_loaded('tidy') && !isset($id) && isset($url) && isurl($url)) {
    if (!isset($_POST['u']) || !$_POST['u'])
      $_POST['u'] = $url;
    if (($p = geturlcontent($url))) {
      $_POST['d'] = 'Original url: <a href="'.$url.'">'.$url.'</a>'."\n\n".$p;

      if ($mail_note_to) {
        $send_mail = 1;
      }

    }
    $_POST['t'] = 'inbox'.(isset($_POST['t']) && $_POST['t'] ? ','.$_POST['t'] : '');
  }

  $note = array();
  if (!isset($id) || !($note = getnote($id, 1))) {
    if (file_exists($id_file))
      $id = file_get_contents($id_file) + 1;
    else
      $id = '1';
    file_put_contents($id_file, $id, LOCK_EX);
    chmod($id_file, 0600);
  }

  saveattachment($id);

  $data = array(
    'id' => $id,
    'time' => time(),
    'source' => array(
      'url' => (isset($_POST['u']) && ($u = isurl($_POST['u'])) !== false ? $u : (isset($note['source']['url']) ? $note['source']['url'] : '')),
      'title' => (isset($note['source']['title']) ? $note['source']['title'] : ''),
      'description' => (isset($note['source']['description']) ? $note['source']['description'] : ''),
    ),
    'public' => (isset($_POST['p']) ? $_POST['p'] : (isset($note['public']) ? $note['public'] : $default_privacy)),
    'tags' => (isset($note['tags']) ? $note['tags'] : array()),
  );
  if (isset($_POST['t'])) {
    if (isset($_POST['inbox']) && $_POST['inbox'] == 1)
      $_POST['t'] = 'inbox,'.$_POST['t'];
    $data['tags'] = array_unique(array_filter(array_map('trim', explode(',', htmlspecialchars($_POST['t'], ENT_QUOTES))), 'strlen'));
    sort($data['tags'], SORT_NATURAL | SORT_FLAG_CASE);
  }
  $data['content'] = (isset($_POST['d']) ? escpost($_POST['d'], $id, $data['source']['url']) : (isset($note['content']) ? $note['content'] : ''));

  if ($note != $data) {
    if ($data['source']['url'] !== (isset($note['source']['url']) ? $note['source']['url'] : '')) {
      $meta = geturlmeta($data['source']['url']);
      $data['source']['title'] = (isset($meta['t']) ? $meta['t'] : '');
      $data['source']['description'] = (isset($meta['d']) ? $meta['d'] : '');
    }
    if ($data['tags'] != (isset($note['tags']) ? $note['tags'] : array())) {
      updatetag($data['tags'], (isset($note['tags']) ? $note['tags'] : array()), $id);
    }
    if ($data['content'] !== (isset($note['content']) ? $note['content'] : '')) {
      if (!$data['content']) {
        if (file_exists($content_dir.$id.'.txt'))
          unlink($content_dir.$id.'.txt');
        if (file_exists($html_dir.$id.'.html'))
          unlink($html_dir.$id.'.html');
      } else {
        file_put_contents($content_dir.$id.'.txt', $data['content'], LOCK_EX);
        chmod($content_dir.$id.'.txt', 0600);
        $parsedown = new Parsedown();
        $data['content'] = $parsedown->parse($data['content']);
        file_put_contents($html_dir.$id.'.html', $data['content'], LOCK_EX);
        chmod($html_dir.$id.'.html', 0600);
      }
    }
    $note = $data;
    unset($data['content']);
    file_put_contents($data_dir.$id.'.json', json_encode($data), LOCK_EX);
    chmod($data_dir.$id.'.json', 0600);
  }

  if (isset($send_mail) && $send_mail)
    sendmail($mail_note_to, $mail_note_from, 'Note saved in Inbox by '.htmlentities($site_name), "\n\n\n\n".'<p><a href="'.$site_url.'?id='.$note['id'].'" target="_blank">View Note</a></p>'."\n\n\n\n".$note['content'], $mail_note_account);

  return $note;
}
function deletenote($note = null) {
  global $data_dir, $content_dir, $upload_dir, $html_dir;

  if (!isset($note))
    return false;

  if (file_exists($data_dir.$note['id'].'.json'))
    unlink($data_dir.$note['id'].'.json');
  if (file_exists($content_dir.$note['id'].'.txt'))
    unlink($content_dir.$note['id'].'.txt');
  if (file_exists($html_dir.$note['id'].'.html'))
    unlink($html_dir.$note['id'].'.html');
  if ($files = glob($upload_dir . $note['id'].'-*', GLOB_NOSORT)) {
    foreach ($files as $file) {
      unlink($file);
    }
  }
  updatetag(array(), $note['tags'], $note['id']);
  return true;
}
function updatetag($tags, $origin, $id) {
  global $data_dir, $tags_file;

  if (!($taglist = gettaglist()))
    $taglist = array();

  if (!empty($tags)) {
    foreach ($tags as $tag) {
      if (($tag = trim($tag))) {
        if (isset($taglist[$tag])) {
          $taglist[$tag] = array_unique(array_merge($taglist[$tag], array($id)));
          rsort($taglist[$tag], SORT_NATURAL);
        } else
          $taglist[$tag] = array($id);
      }
    }
  }

  if (!empty($origin)) {
    $rms = array_diff($origin, $tags);
    foreach ($rms as $rm) {
      $taglist[$rm] = array_diff($taglist[$rm], array($id));
      if (!$taglist[$rm])
        unset($taglist[$rm]);
    }
  }

  uasort($taglist, function ($tag1, $tag2) {
    if (($o = count($tag2)-count($tag1)))
      return $o;
    elseif (($o = $tag2[0]-$tag1[0]))
      return $o;
    else
      return 0;
  });

  file_put_contents($tags_file, json_encode($taglist));
  chmod($tags_file, 0600);
}
function displaynote($note, $search = '', $single = 0) {
  global $auth, $show_snippet;

  echo '<div class="content" id="post-'.$note['id'].'">';
  if (!$note['public'] && !$auth && ($single || (!$single && (!isset($show_snippet) || !$show_snippet || !$note['source']['url'])))) {
    echo '<p class="private">Private</p>';
    echo '<div class="meta">';
  } else {
    if ($note['content']) {
      if (!$single)
        echo preg_replace('/^<p>[\s　(\xc2)(\xa0)\r\n]*<\/p>$/i', '', '<p>'.cut($note['content'], $search).'</p>');
      else {
        echo '<div id="readability">'.$note['content'].'</div>';
      }
      $via = true;
    } else {
      $via = false;
      if (!$note['source']['url'] && ($list = getattachment($note['id'])) && count($list) === 1) {
        $attachment = parseattachmentname($list[0]);
        if ($attachment['type'] == 1) {
          echo '<div class="media"><img src="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get" alt="'.$attachment['display_name'].'" title="'.$attachment['display_name'].'"/></div>';
        } elseif ($attachment['type'] == 2) {
          echo '<audio controls><source src="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get"><embed height="50" width="200" src="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get"></audio>';
        } elseif ($attachment['type'] == 3) {
          echo '<video controls><source src="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get"><object height="190" width="260" data="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get"><embed height="190" width="260" src="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get"></object></video>';
        }
      }
    }
    if (!$single && $note['source']['url']) {
      echo '<p>'.($via ? 'via: <a href="'.$note['source']['url'].'" target="_blank" title="'.($note['source']['title'] ? $note['source']['title'] : $note['source']['url']).'">'.($note['source']['title'] ? $note['source']['title'] : 'Link').'</a></p>' : '<a href="'.$note['source']['url'].'" target="_blank" title="'.$note['source']['url'].'">'.htmlspecialchars($note['source']['url']).'</a></p>'.($note['source']['description'] ? '<p>'.$note['source']['description'].'</p>' : ''));
    }
    echo '<div class="meta">';
    if (!$single && $list = getattachment($note['id'])) {
      echo '<div class="attachment-list"><p>';
      foreach ($list as $attachment) {
        $attachment = parseattachmentname($attachment);
        echo '<a href="attachment.php?id='.$note['id'].'&name='.$attachment['url_name'].'&action=get" target="_blank" title="'.$attachment['display_name'].'">'.$attachment['display_name'].'</a>';
      }
      echo '</p></div>';
    }
    if (!$single && $note['tags']) {
      $tag_str = '';
      foreach ($note['tags'] as $tag) {
        if ($tag !== 'inbox')
          $tag_str .= '<a href="index.php?tag='.rawurlencode($tag).'" title="'.$tag.'">#'.$tag.'</a>';
      }
      if ($tag_str)
        echo '<p class="taglist">'.$tag_str.'</p>';
    }
  }
  echo date('M. d, Y', $note['time']).(!$note['public'] ? ($auth ? '<a class="private-s" href="privacy.php?id='.$note['id'].'&p=1&url='.getrefurl($note['id'], $single).'" title="Set to public">Private</a>' : '<span class="private-s">Private</span>') : '').(in_array('inbox', $note['tags']) ? '<a class="private-s" href="inbox.php?id='.$note['id'].'&i=0&url='.getrefurl($note['id'], $single, 1).'" title="Move from inbox to note">Inbox</a>' : '').'<span class="link">'.(!$single && ($note['public'] || $auth) ? '<a title="view" href="index.php?id='.$note['id'].'">View</a>' : ($auth && class_exists('ZipArchive') ? '<a title="export" href="export.php?id='.$note['id'].'">Export</a>' : '')).($auth ? '<a title="edit" href="edit.php?id='.$note['id'].'">Edit</a><a title="delete" onclick="return confirm(\'Permanently delete this note?\');" href="delete.php?id='.$note['id'].'">Delete</a>' : '').'</span></div>';
  echo '</div>';
}
function getrefurl($id, $single, $inbox = 0) {
  global $site_url;

  if ($single)
    return rawurlencode($site_url.'?id='.$id);

  if (isset($_GET['tag']))
    $url = $site_url.'?tag='.$_GET['tag'];
  elseif (isset($_GET['s']))
    $url = $site_url.'?s='.$_GET['s'];

  if (isset($_GET['p'])) {
    if (isset($url))
      $url .= '&p='.$_GET['p'];
    else
      $url = $site_url.'?p='.$_GET['p'];
  } elseif (!isset($url))
    $url = $site_url;

  if (!$inbox)
    $url .= '#post-'.$id;

  return rawurlencode($url);
}
function geturlmeta($url = '') {
  if (!$url)
    return '';
  //$str = @file_get_contents($url);
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  $str = curl_exec($ch);
  curl_close($ch);
  if (strlen($str)) {
    preg_match('/\<title\>(.*)\<\/title\>/i', $str, $title);
    preg_match('/<meta +([^>]*content *= *["\']([^>]*)["\'][^>]* *)? *name *= *["\']description["\'] *([^>]*content *= *["\']([^>]*)["\'][^>]* *)? *\/? *>/i', $str, $description);
    return array('t' => (isset($title[1]) ? toutf8($title[1]) : ''), 'd' => toutf8((isset($description[2]) ? $description[2] : '').(isset($description[4]) ? $description[4] : '')));
  }
  return '';
}
function geturlcontent($url = '') {
  global $include_dir;

  if (!$url)
    return '';

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  $content = curl_exec($ch);
  curl_close($ch);
  if ($content = toutf8($content)) {
    $content = preg_replace(array('/<style.*?\/style>/si', '/<script.*?\/script>/si'), '', $content); //remove js element
    $tidy = new tidy;
    $content = $tidy->repairString($content);
    if (!$content || !($tidy->parseString($content)) || !($tidy->cleanRepair()))
      return '';
    else
      $content = $tidy;

    require $include_dir . 'readability/config.inc.php';
    require $include_dir . 'readability/common.inc.php';
    require $include_dir . 'readability/Readability.inc.php';
    $Readability = new Readability($content, 'utf8');
    $ReadabilityData = $Readability->getContent();
    $content = '<h1>'.$ReadabilityData['title'].'</h1>'.$ReadabilityData['content'];

    //$content = preg_replace('/<([^>]* +)(src) *= *("|\')\//si','<$1$2=$3'.substr($url, 0, strpos($url, '/', 8) + 1).'/', $content); //modify img src

    return $content;
  }

  return '';
}
function cut($str, $highlight = '', $len = 140) {
  $str = preg_replace('/[\r\n]+/',' ',$str);
  $str = strip_tags($str);
  if (mb_strlen($str, 'utf-8') <= $len || $len < 1) {
   return ($highlight ? preg_replace('/'.preg_quote($highlight, '/').'/i', '<span class="highlight">$0</span>', $str) : $str);
  } else {
    $str = ($highlight && ($pos = mb_stripos($str, $highlight)) > 10 ? '... ' : '').mb_substr($str, ($highlight ? max(0, $pos - 10) : 0), $len - 8, 'utf-8').' ...';
    return ($highlight ? preg_replace('/'.preg_quote($highlight, '/').'/i', '<span class="highlight">$0</span>', $str) : $str);
  }
}
function generaterandomstring($length = 16) {
  $characters = '234567ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $randomString = '';
  for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
  }
  return $randomString;
}
function gettimestamp() {
  return floor(microtime(true)/30);
}
function oathhotp($timestamp) {
  global $otp_key;

  $otpLength = 6;
  $otp_key = strtoupper($otp_key);
  if (!preg_match('/^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]+$/',$otp_key,$match))
    throw new Exception('Invalid characters in the base32 string.');
  $l = strlen($otp_key);
  $n = 0;
  $j = 0;
  $binary_key = "";
  $lut = array(
           "A" => 0,       "B" => 1,
           "C" => 2,       "D" => 3,
           "E" => 4,       "F" => 5,
           "G" => 6,       "H" => 7,
           "I" => 8,       "J" => 9,
           "K" => 10,      "L" => 11,
           "M" => 12,      "N" => 13,
           "O" => 14,      "P" => 15,
           "Q" => 16,      "R" => 17,
           "S" => 18,      "T" => 19,
           "U" => 20,      "V" => 21,
           "W" => 22,      "X" => 23,
           "Y" => 24,      "Z" => 25,
           "2" => 26,      "3" => 27,
           "4" => 28,      "5" => 29,
           "6" => 30,      "7" => 31
  );
  for ($i = 0; $i < $l; $i++) {
    $n = $n << 5;
    $n = $n + $lut[$otp_key[$i]];
    $j = $j + 5;
    if ($j >= 8) {
      $j = $j - 8;
      $binary_key .= chr(($n & (0xFF << $j)) >> $j);
    }
  }
  if (strlen($binary_key) < 8)
    throw new Exception('Secret key is too short. Must be at least 16 base 32 characters');
  $bin_timestamp = pack('N*', 0) . pack('N*', $timestamp);
  $hash = hash_hmac ('sha1', $bin_timestamp, $binary_key, true);
  return str_pad(oathtruncate($hash,$otpLength), $otpLength, '0', STR_PAD_LEFT);
}
function verifyotp($key) {
  if (!isset($key))
    return false;
  $window=1;
  $timeStamp = gettimestamp(30);
  for ($ts = $timeStamp - $window; $ts <= $timeStamp + $window; $ts++) {
    if (oathhotp($ts) == $key)
      return true;
  }
  return false;
}
function oathtruncate($hash,$otpLength) {
  $offset = ord($hash[19]) & 0xf;
  return (
       ((ord($hash[$offset+0]) & 0x7f) << 24 ) |
       ((ord($hash[$offset+1]) & 0xff) << 16 ) |
       ((ord($hash[$offset+2]) & 0xff) << 8 ) |
       (ord($hash[$offset+3]) & 0xff)
  ) % pow(10, $otpLength);
}
function auth() {
  global $site_url, $login;

  if (isset($login)) {
    if (isset($_POST['r']) && $_POST['r'])
      $expire = 31536000;
    else
      $expire = 0;
    session_set_cookie_params($expire, '/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 1 : 0), 1);
  }
  session_name('_nott_'.str_replace(array('.', '/'), '_', substr($site_url, stripos($site_url, '//')+2)));
  session_save_path(__DIR__ . '/session');
  if(session_status() !== PHP_SESSION_ACTIVE)
    session_start();

  if (session_status() === PHP_SESSION_ACTIVE && (!isset($_SESSION['robot']) || $_SESSION['robot'] !== 0)) {
    if (isset($_COOKIE['_nott_notRobot']) && $_COOKIE['_nott_notRobot'] == 1)
      $_SESSION['robot'] = 0;
    else {
      session_destroy();
      return false;
    }
  }

  if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== 1)
    return false;
  else
    return true;
}
function getattachment($id = null, $cache = 0) {
  global $upload_dir;

  if (!isset($id))
    return false;

  if ($list = glob($upload_dir.$id.'-*-*', GLOB_NOSORT)) {
    natsort($list);
    if (!$cache) {
      $cache_files = glob($upload_dir.$id.'-0-*', GLOB_NOSORT);
      $list = array_diff($list, $cache_files);
    }
    return $list;
  } else
    return false;
}
function parseattachmentname($attachment) {
  $name = substr($attachment, strrpos($attachment, '/')+1);
  $id = substr($name, 0, strpos($name, '-'));
  $name = substr($name, strpos($name, '-')+1);
  $type = substr($name, 0, 1);
  $display_name = substr($name, strpos($name, '-')+1);
  $t = substr($display_name, 0, ($p = strpos($display_name, '-', strpos($display_name, '-')+1)));
  $display_name = htmlspecialchars(substr($display_name, $p+1));
  return array('id' => $id, 'type' => $type, 't' => $t, 'display_name' => $display_name, 'url_name' => rawurlencode($name));
}
function displayattachment($id, $attachment, $tmp = 0, $post = 0) {
  if (!isset($id) || !isset($attachment) || !is_array($attachment))
    return false;

  return '<div id="attachment-'.$id.'-'.$attachment['t'].'"'.($attachment['type'] == '1' ? ' class="image" style="background-image:url(\'attachment.php?id='.$id.'&name='.$attachment['url_name'].'&action=get'.($tmp ? '&tmp=1' : '').'\');"' : '').'>
    <span class="attachment">
      <a href="attachment.php?id='.$id.'&name='.$attachment['url_name'].'&action=get'.($tmp ? '&tmp=1' : '').'" target="_blank">'.$attachment['display_name'].'</a>'.($id && $attachment['type'] == '1' && $post ? '&nbsp;&nbsp;
      <span class="insert" onclick="mdAddHR(\'!['.$attachment['display_name'].'](attachment.php?id='.$id.'&name='.$attachment['url_name'].'&action=get'.($tmp ? '&tmp=1' : '').' &quot;'.$attachment['display_name'].'&quot;)\')">Insert</span>' : '').'
    </span>'.($post ? '<span class="delete" onclick="deleteAttachment(\''.$id.'\', \''.$attachment['url_name'].'\', \'attachment-'.$id.'-'.$attachment['t'].'\')">&#10007;</span>' : '').'</div>';
}
function saveattachment($id = null, $dir = null) {
  global $upload_dir, $tmp_dir;

  if (!isset($id))
    return false;

  if (!isset($dir))
    $dir = $upload_dir;

  if (isset($_POST['file']) && $_POST['file']) {
    if (!is_array($_POST['file'])) {
      $_POST['file'] = array($_POST['file']);
      $_GET['name'] = array($_GET['name']);
    }
    foreach ($_POST['file'] as $i => $data) {
      if (strpos(($name = rawurldecode($_GET['name'][$i])), 'email-') === 0) {
        $email = true;
        $tmp_file = $dir.$id.'-0-'.($name = substr($name, 6));
      } else {
        $email = false;
        $tmp_file = $dir.$id.'-tmp-'.$name;
      }
      if (strpos($data, ',') !== false) {
        if (strpos(substr($data, 0, strpos($data, ',')+1), ';base64,') !== false)
          $data = base64_decode(strtr(substr($data, strpos($data, ',')+1), ' ', '+'));
        else
          $data = substr($data, strpos($data, ',')+1);
      }
      if (file_put_contents($tmp_file, $data)) {
        unset($data);
        if (!filesize($tmp_file)) {
          unlink($tmp_file);
        } else {
          $file = $dir.$id.'-'.($email ? '0' : ($type = getfiletype($tmp_file))).'-'.$name;
          rename($tmp_file, $file);
          chmod($file, 0600);
        }
      } else {
        unset($data);
      }
    }
    unset($_POST['file']);
    unset($_GET['name']);
    if (isset($file))
      return $file;
  } elseif (isset($_FILES['files']['tmp_name'][0]) && $_FILES['files']['tmp_name'][0]) {
    $files = $_FILES['files'];
    $i = 0;
    foreach ($files['name'] as $file_id => $file) {
      $name = $files['name'][$file_id];
      $tmp_file = $files['tmp_name'][$file_id];
      $file_name = $id.'-'.getfiletype($tmp_file).'-'.time().$i.'-'.$name;
      if (!file_exists($dir.$file_name)) {
        if (!move_uploaded_file($tmp_file, $dir.$file_name))
          rename($tmp_file, $dir.$file_name);
      } elseif (file_exists($tmp_file))
        unlink($tmp_file);
      $i++;
    }
    $_FILES = array();
    if (isset($file_name))
      return $file_name;
  }
  return true;
}
function getfiletype($file) {
  if (!is_readable($file))
    return false;

  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $type = strtolower(finfo_file($finfo, $file));
  if ($type == 'image/jpeg' || $type == 'image/png' || $type == 'image/gif') {
    $type = 1;
  } elseif ($type == 'audio/mpeg' || $type == 'audio/mpa' || $type == 'audio/mpa-robust' || $type == 'audio/ogg' || $type == 'audio/wav' || $type == 'audio/vnd.wave' || $type == 'audio/wave' || $type == 'audio/x-wav' || ($type == 'application/octet-stream' && (($ext = strtolower(substr($file, strrpos($file, '.')))) == '.mp3' || $ext == '.wav' || $ext == '.wave'))) {
    $type = 2;
  } elseif ($type == 'video/mp4' || $type == 'video/webm' || $type == 'video/ogg' || ($type == 'application/octet-stream' && (($ext = strtolower(substr($file, strrpos($file, '.')))) == '.mp4' || $ext == '.webm'))) {
    $type = 3;
  } else {
    $type = 4;
  }
  return $type;
}
function verifypw($pw) {
  global $password;

  if (password_verify($pw, $password))
    return true;
  else
    return false;
}

function sendmail($to, $from = '', $subject = '', $message = '', $account = '') {
  global $user_name;

  if (!$to)
    return 0;

  $headers =
    'MIME-Version: 1.0'."\r\n".
    'Content-type: text/html; charset=utf-8'."\r\n".
    ($from ? 'From: '.$user_name.' <'.$from.'>' : '');

  if ($account)
    mail($to, $subject, $message, $headers, '-a '.$account);
  else
    mail($to, $subject, $message, $headers);
}

