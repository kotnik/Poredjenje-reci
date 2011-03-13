<?php

require_once ('../database.php');

if (!sess_exists($sess_id)) {
  sess_save($sess_id);
}
$session = sess_load($sess_id);

$gets = geturl();
if (!isset($gets['action'])) {
  $gets['action'] = '';
}
$posts = getpost();
if (!isset($posts['form_action'])) {
  $posts['form_action'] = '';
}

if ($posts['form_action']) {
  switch ($posts['form_action']) {
    case 'edit':
      $set_up = array();
      $set_up['id'] = $posts['set_id'];
      $set_up['title'] = $posts['title'];
      $set_up['intro'] = $posts['intro'];
      $set_up['question'] = $posts['question'];
      $set_up['active'] = $posts['active'] == 'on' ? 1 : 0;
      sets_update($set_up);
      $session['value']['message'] = 'Snimio!';
      sess_save_array($session);
      break;
    case 'new':
      $set = array();
      $set['title'] = $posts['title'];
      $set['intro'] = $posts['intro'];
      $set['question'] = $posts['question'];
      $set['active'] = $posts['active'] == 'on' ? 1 : 0;
      $set['words'] = $posts['words'];
      sets_insert($set);
      unset($set);
      $session['value']['message'] = 'Dodao!';
      sess_save_array($session);
  }
}

$session_message = FALSE;
if (isset($session['value']['message']) && $session['value']['message']) {
  $session_message = $session['value']['message'];
  unset($session['value']['message']);
  sess_save_array($session);
}

$smarty->assign('message', $session_message);
$smarty->assign('url', $base_url);
$smarty->assign('admin_url', $base_url.'admin/');
$smarty->assign('action', $gets['action']);

switch ($gets['action']) {

  case 'new':
    $set = array();
    $set['title'] = 'Novi set';
    $set['active'] = 1;
    $set['question'] = '';
    $set['intro'] = '';
    $set['words'] = FALSE;
    $set['id'] = 0;
    $smarty->assign('set', $set);
    $smarty->display('admin-edit.tpl');
    break;

  case 'edit':
    redirect('admin/');
    if (!isset($gets['set'])) {
      redirect('admin/');
    }
    if (!$gets['set']) {
      redirect('admin/');
    }
    $set = sets_get($gets['set']);
    $smarty->assign('set', $set);
    $smarty->display('admin-edit.tpl');
    break;

  case 'delete':
    if (!isset($gets['set'])) {
      redirect('admin/');
    }
    if (!$gets['set']) {
      redirect('admin/');
    }
    $set = sets_get($gets['set']);
    sets_delete($set);
    $session['value']['message'] = 'Obrisao!';
    sess_save_array($session);
    redirect('admin/');
    break;

  default:
    $sets = admin_get_sets();
    $smarty->assign('sets', $sets);
    $smarty->display('admin-home.tpl');
}
