<?php

require_once ('database.php');

$get = geturl();

if (!sess_exists($sess_id)) {
  sess_save($sess_id);
}
$session = sess_load($sess_id);

if ($get['wordid'] != 'startbut') {
  $get_id = $get['wordid'];
  $numbs = explode('_', $get_id);
  $get_comb = (int)$numbs[0];
  $get_word_num = (int)$numbs[1];
  if ($get_comb && $get_word_num) {
    comb_mark($get_comb, $get_word_num);
  }
}

$combs = comb_get($session, $get['set']);
if ($combs) {
  shuffle($combs);
  $show_comb = FALSE;
  foreach ($combs as $comb) {
    if ($comb['answer_word_id'] == 0) {
      $show_comb = $comb;
      continue;
    }
  }
}

if ($show_comb) {
  $smarty->assign('word1', $show_comb['word1_word']);
  $smarty->assign('word2', $show_comb['word2_word']);

  $set_id = (int)$get['set'];
  $id1 = $show_comb['id'].'_'.$show_comb['word1_id'];
  $id2 = $show_comb['id'].'_'.$show_comb['word2_id'];
  $smarty->assign('word1id', $id1);
  $smarty->assign('word2id', $id2);

  $smarty->display('reci.tpl');
} else {
  $smarty->assign('url', $base_url);
  $smarty->display('uradjeno.tpl');
}
