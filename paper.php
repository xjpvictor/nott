<?php
include(__DIR__ . '/init.php');

$post = true;
$paper = (file_exists($paper_file) && ($t = file_get_contents($paper_file)) ? json_decode($t, 1) : array());

if (isset($_GET['id']) && $_GET['id']) {
  if (isset($_GET['action']) && $_GET['action']) {
    if ($auth) {
      switch ($_GET['action']) {
      case 'delete':
        if (isset($_GET['ver']) && $_GET['ver']) {
          if ($paper && isset($paper[$_GET['id']]) && isset($paper[$_GET['id']]['review'][$_GET['ver']])) {
            unset($paper[$_GET['id']]['review'][$_GET['ver']]);
            file_put_contents($paper_file, json_encode($paper));
            chmod($paper_file, 0600);
          }
          if (file_exists($paper_dir.$paper[$_GET['id']]['time'].'-'.$_GET['ver'].'.txt'))
            unlink($paper_dir.$paper[$_GET['id']]['time'].'-'.$_GET['ver'].'.txt');
        } else {
          if ($paper && isset($paper[$_GET['id']])) {
            unset($paper[$_GET['id']]);
            file_put_contents($paper_file, json_encode($paper));
            chmod($paper_file, 0600);
          }
          if (($paper_txts = glob($paper_dir.$paper[$_GET['id']]['time'].'-*.txt', GLOB_NOSORT))) {
            foreach ($paper_txts as $paper_txt)
              unlink($paper_txt);
          }
        }
        break;
      }

      header('Location: '.$site_url.'paper.php'.(isset($_GET['ver']) && $_GET['ver'] ? '?id='.$_GET['id'] : ''));
      exit;

    }
  } else {
    if ($paper && isset($paper[$_GET['id']]) && $paper[$_GET['id']] && isset($paper[$_GET['id']]['review']) && ($paper_reviews = $paper[$_GET['id']]['review'])) {
      // Display paper
      $paper_reviews = array_filter($paper_reviews, function($review_version) use ($paper_dir, $paper) {
        return file_exists($paper_dir.$paper[$_GET['id']]['time'].'-'.$review_version.'.txt');
      }, ARRAY_FILTER_USE_KEY);

      uasort($paper_reviews, function($a, $b) {
        return $b['time'] - $a['time'];
      });

      if ($paper_reviews)
        $paper_content = file_get_contents($paper_dir.$paper[$_GET['id']]['time'].'-'.($display_select = array_slice(array_keys($paper_reviews), 0, 1)[0]).'.txt');
    }

    if (!isset($paper_content)) {
      http_response_code(404);
      $error = 'Sorry, paper not found.';
      include($include_dir . 'error.php');
      exit;
    }
  }
}

if (!isset($paper_content) && !$auth) {
  http_response_code(403);
  $error = 'Access denied. Please <a title="login" href="login.php?url='.rawurlencode($site_url.'paper.php'.(isset($_GET['id']) && $_GET['id'] ? '?id='.$_GET['id'] : (isset($_GET['view']) && $_GET['view'] == 1 ? '?view=1' : ''))).'">login</a>.';
  include($include_dir.'error.php');
  exit;
}

if (isset($_POST['d']) && $_POST['d'] && isset($_POST['comment']) && !$_POST['comment']) {
  if (!isset($paper_content)) {
    // New paper
    $time = time();
    $paper_hash = strtolower(hash($paper_hash_algo, $time));

    do {
      $paper_id = substr($paper_hash, 0, $paper_id_length++);
    } while ($paper_id_length <= strlen($paper_hash) && isset($paper[$paper_id]));

    $version = 0;
    $paper[$paper_id] = array('time' => $time, 'review' => array($version => array('time' => $time, 'email' => $user_email, 'name' => $user_name)));
  } elseif ($auth || (isset($_POST['e']) && $_POST['e'] && filter_var(filter_var($_POST['e'], FILTER_SANITIZE_EMAIL), FILTER_VALIDATE_EMAIL) && isset($_POST['n']))) {
    // Add revision
    $paper_id = $_GET['id'];
    $time = $paper[$paper_id]['time'];

    $version = max(array_keys($paper[$paper_id]['review'])) + 1;
    $paper[$paper_id]['review'][$version] = array('time' => time(), 'email' => ($auth ? $user_email : $_POST['e']), 'name' => ($auth ? $user_name : $_POST['n']));
  }

  if (isset($version)) {
    file_put_contents($paper_dir.$time.'-'.$version.'.txt', $_POST['d']);
    chmod($paper_dir.$time.'-'.$version.'.txt', 0600);

    file_put_contents($paper_file, json_encode($paper));
    chmod($paper_file, 0600);

    header('Location: '.$site_url.'paper.php?id='.$paper_id);
    exit;
  }
}

if (!isset($paper_content) && isset($_GET['view']) && $_GET['view'] == 1)
  $papers = true;

include($include_dir . 'head.php');
?>
<?php if (!isset($papers)) { ?>
<form id="post" method="POST" action="post.php?r=view" enctype="multipart/form-data">
<div id="main">
<div class="content paper">
<textarea id="post-d" class="paper" name="d" tabindex=1 onkeydown="this.scrollTop=this.scrollHeight;document.getElementById('paper-list-editing').classList.remove('hide');" onkeyup="document.getElementById('paper-list-editing').dataset.content=this.value;focusVersion(document.getElementById('paper-list-editing'),false);"><?php echo (isset($paper_content) && $paper_content ? htmlentities($paper_content) : ''); ?></textarea>
<textarea id="post-area" name="comment"></textarea>
<?php
if (isset($paper_reviews) && $paper_reviews) {
  echo '<div id="paper-list">';
  echo '<div class="papers hide" id="paper-list-editing" onclick="focusVersion(this,-1);" data-content="" data-info="New Revision"><div id="paper-list-editing-preview"></div></div>';
  foreach ($paper_reviews as $paper_version => $paper_review) {
    echo '<div class="papers'.(isset($display_select) && $display_select == $paper_version ? ' selected' : '').'" onclick="focusVersion(this,true'.($paper_version ? ',\''.$paper_version.'\'' : '').');" data-content="'.htmlentities(($str = file_get_contents($paper_dir.$paper[$_GET['id']]['time'].'-'.$paper_version.'.txt'))).'" data-info="'.htmlentities($paper_review['name'] ? $paper_review['name'] : $paper_review['email'])."\n".date('d M, Y H:i', $paper_review['time']).'"><div>'.htmlentities(substr($str, 0, 100)).'</div><img class="avatar" src="avatar.php?hash='.hash($avatar_hash_algo, $paper_review['email']).'&s=36" /></div>';
  }
  echo '</div>';
  echo '<div id="paper-delete">';
  echo '<a href="paper.php?id='.$_GET['id'].'&action=delete" onclick="return confirm(\'Permanently delete this paper?\');">Delete Paper</a>';
  echo '<a id="paper-delete-ver" '.(count($paper_reviews) > 1 ? '' : 'class="hide" ').'data-href="paper.php?id='.$_GET['id'].'&action=delete&ver=" href="paper.php?id='.$_GET['id'].'&action=delete&ver='.(isset($display_select) ? $display_select : '').'" onclick="return confirm(\'Permanently delete this revision?\');"> / this revision</a>';
  echo '</div>';
}
?>
</div>
</div>
<input name="t" type="hidden" id="post-t" value="Nott Paper">
<?php } else { ?>
<div id="main">
<?php echo ($paper ? '<div id="post">' : ''); ?>
<div class="content">
<?php
  if (!$paper)
    echo 'No Paper created!';
  else {
    uasort($paper, function($a, $b) {
      return $b['time'] - $a['time'];
    });
    foreach ($paper as $paper_id => $paper_data) {
      uasort($paper_data['review'], function($a, $b) {
        return $b['time'] - $a['time'];
      });
      $paper_versions = array_keys($paper_data['review']);
      foreach ($paper_versions as $paper_version) {
        if (file_exists(($paper_txt = $paper_dir.$paper_data['time'].'-'.$paper_version.'.txt'))) {
          $paper_review = $paper_data['review'][$paper_version];
          echo '<div class="papers'.(!$paper_version ? ' noReview' : '').'" onclick="window.location=\''.$site_url.'paper.php?id='.$paper_id.'\';" data-info="Created on '.date('d M, Y', $paper_data['time']).($paper_version ? "\n".'Last reviewed by '.htmlentities($paper_review['name'] ? $paper_review['name'] : $paper_review['email']).' on '.date('d M, Y', $paper_review['time']) : '').'"><div>'.htmlentities(substr(file_get_contents($paper_txt), 0, 100)).'</div></div>';
          break;
        }
      }
    }
  }
?>
</div>
<?php echo ($paper ? '</div>' : ''); ?>
</div>
<?php } ?>
<!--end of main-->

<?php
include($include_dir . 'sidebar.php');
?>
</form>
<?php
include($include_dir . 'foot.php');
?>
