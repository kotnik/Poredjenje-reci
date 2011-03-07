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

if ($session['value'] == '') {
  $smarty->assign('url', $base_url);
  $smarty->display('forma.tpl');
} else {
  if (isset($get['set']) && $get['set']) {
    if ($last_set = sets_get($get['set'])) {
      if (!$comb = comb_get($session, $get['set'])) {
        comb_make($session, $get['set']);
        $comb = comb_get($session, $get['set']);
      }
      if (comb_open_answers($comb)) {
        $smarty->display('biraj.tpl');
      } else {
        $smarty->assign('url', $base_url);
        $smarty->display('biraj-nema.tpl');
      }
    } else {
      $smarty->display('prazno.tpl');
    }
  } else {
    if ($sets = sets_get_all()) {
      $smarty->assign('sets', $sets);
      $smarty->assign('base_url', $base_url);
      $smarty->display('index.tpl');
    } else {
      $smarty->display('prazno.tpl');
    }
  }
}
