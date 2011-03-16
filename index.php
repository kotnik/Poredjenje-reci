<?php

require_once ('database.php');

$get = geturl();
$post = getpost();

if (!sess_exists($sess_id)) {
  sess_save($sess_id);
}

if (isset($post['name']) && isset($post['index'])) {
  $sess_value = array();
  $sess_value['name'] = $post['name'];
  $sess_value['index'] = $post['index'];
  sess_save($sess_id, $sess_value);
}

$session = sess_load($sess_id);
if (!is_array($session['value'])) {
  $session['value'] = array();
}

if (($session['value'] == '' || empty($session['value'])) && (isset($get['set']) && $get['set'] != '')) {
  $smarty->assign('url', $base_url);
  if (isset($get['set'])) {
    $smarty->assign('set', $get['set']);
  } else {
    $smarty->assign('set', '');
  }
  $smarty->display('forma.tpl');
} else {
  if (isset($get['set']) && $get['set']) {
    $subset_id = $get['set'];
    if ($last_set = subsets_get($subset_id)) {
      $set_info = subset_get_set($subset_id);
      if (!$comb = comb_get($session, $set_info['id'], $subset_id)) {
        comb_copy($session, $subset_id, $set_info['id']);
        $comb = comb_get($session, $set_info['id'], $subset_id);
      }
      if (comb_open_answers($comb)) {
        $smarty->assign('intro', nl2br($set_info['intro']));
        $smarty->assign('question', $set_info['question']);
        $smarty->display('biraj.tpl');
      } else {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
          $params = session_get_cookie_params();
          setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
          );
        }
        $smarty->assign('url', $base_url);
        $smarty->display('biraj-nema.tpl');
      }
    } else {
      $smarty->display('prazno.tpl');
    }
  } else {
    if ($sets = subsets_get_all()) {
      $smarty->assign('sets', $sets);
      $smarty->assign('base_url', $base_url);
      $smarty->display('index.tpl');
    } else {
      $smarty->display('prazno.tpl');
    }
  }
}
