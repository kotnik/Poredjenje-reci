<?php

/*
 *
 * SESSIONS STUFF
 *
 */

/**
 * Check if sessions exists in the database
 */
function sess_exists($sess_id) {
  global $db;
  $sess_id = db_quote($sess_id);
  $sql = "SELECT * FROM sessions WHERE name=$sess_id";
  $rs = $db->Execute($sql);
  if ($rs->RecordCount()) {
    return TRUE;
  }
  return FALSE;
}

/**
 * Save session id to database
 */
function sess_save($sess_id, $value=FALSE) {
  global $db;
  $sess_id = db_quote($sess_id);
  if (!$value) {
    $sql = "INSERT INTO sessions SET name=$sess_id, created=UNIX_TIMESTAMP()";
  } else {
    $value = db_quote(serialize($value));
    $sql = "UPDATE sessions SET value=$value WHERE name=$sess_id";
  }
  $db->Execute($sql);
}

/**
 * Update or create new session based on array
 */
function sess_save_array($session) {
  global $db;
  if (is_array($session) && $session['id']) {
    $value = db_quote(serialize($session['value']));
    $sql = "UPDATE sessions SET value=$value WHERE id=".$session['id'];
    $db->Execute($sql);
  }
}

/**
 * Load session from database
 */
function sess_load($sess_id) {
  global $db;
  $sess_id = db_quote($sess_id);
  $sql = "SELECT * FROM sessions WHERE name=$sess_id";
  $rs = $db->Execute($sql);
  $session = array();
  if ($rs->RecordCount()) {
    $session['id'] = $rs->fields['id'];
    $session['name'] = $rs->fields['name'];
    $session['created'] = $rs->fields['created'];
    $session['value'] = $rs->fields['value'] != '' ? unserialize($rs->fields['value']) : '';
  }
  return $session;
}

function sess_destroy($session) {
  global $db;
  if (is_array($session) && $session['id']) {
    $sql = "DELETE FROM sessions WHERE id=".$session['id'];
    $db->Execute($sql);
  }
}

/*
 *
 * SETS STUFF
 *
 */

/**
 * Get set with id, or return last set
 * If nothing - return false
 */
function sets_get($set_id=0) {
  global $db;
  if (!$set_id) {
    $sql = "SELECT * FROM sets WHERE active=1 ORDER BY id DESC LIMIT 1";
  } else {
    $set_id = (int)$set_id;
    $sql = "SELECT * FROM sets WHERE id=$set_id";
  }
  $rs = $db->Execute($sql);
  if ($rs->RecordCount()) {
    $set = array();
    $set['id'] = $rs->fields['id'];
    $set['question'] = $rs->fields['question'];
    $set['intro'] = $rs->fields['intro'];
    $set['title'] = $rs->fields['title'];
    $set['active'] = $rs->fields['active'];
    $set['words'] = word_get_all($set_id);
    return $set;
  } else {
    return FALSE;
  }
}

/**
 * Get set with id, or return last set
 * If nothing - return false
 */
function sets_get_all() {
  global $db;
  $sql = "SELECT * FROM sets WHERE active=1 ORDER BY id DESC";
  $rs = $db->Execute($sql);
  $sets = array();
  $counter = 0;
  if ($rs->RecordCount()) {
    $sets = array();
    while (!$rs->EOF) {
      $sets[$counter]['id'] = $rs->fields['id'];
      $sets[$counter]['question'] = $rs->fields['question'];
      $sets[$counter]['intro'] = $rs->fields['intro'];
      $sets[$counter++]['title'] = $rs->fields['title'];
      $rs->MoveNext();
    }
    return $sets;
  } else {
    return FALSE;
  }
}

/**
 * Update set
 */
function sets_update($set) {
  global $db;
  $sql = 'UPDATE sets SET';
  $sql .= ' question = '. db_quote($set['question']);
  $sql .= ', intro = '. db_quote($set['intro']);
  $sql .= ', active = '. $set['active'];
  $sql .= ', title = '. db_quote($set['title']);
  $sql .= ' WHERE id = '. (int)$set['id'];
  $db->Execute($sql);
}

/**
 * Insert set
 */
function sets_insert($set, $subset_count=250) {
  global $db;
  $sql = 'INSERT INTO sets SET';
  $sql .= ' question = '. db_quote($set['question']);
  $sql .= ', intro = '. db_quote($set['intro']);
  $sql .= ', active = '. $set['active'];
  $sql .= ', title = '. db_quote($set['title']);
  $rs = $db->Execute($sql);
  if ($rs) {
    $set_id = $db->Insert_ID();
    $words = explode(' ', $set['words']);
    foreach ($words as $word) {
      $word = trim($word);
      if ($word) {
        $sql = "INSERT INTO words SET set_id=$set_id, word=".db_quote($word);
        $db->Execute($sql);
      }
    }
    // Napravi kombinacije
    comb_subset_make($set_id);
    // Izdeli ih u setove
    subsets_make($set_id, $subset_count);
  }
}

function sets_delete($set) {
  global $db;
  $sql = 'DELETE FROM words WHERE set_id='.$set['id'];
  $db->Execute($sql);
  $sql = 'DELETE FROM combinations WHERE set_id='.$set['id'];
  $db->Execute($sql);
  $sql = 'DELETE FROM subsets WHERE set_id='.$set['id'];
  $db->Execute($sql);
  $sql = 'DELETE FROM sets WHERE id='.$set['id'];
  $db->Execute($sql);
}

/*
 *
 * SUBSETS STUFF
 *
 */

/**
 * Make subsets from a set
 */
function subsets_make($set_id, $limit) {
  global $db;
  // Uzmi sve kombinacije
  $set_id = (int) $set_id;
  $sql = "SELECT * FROM subset_combinations WHERE set_id=$set_id";
  $rs = $db->Execute($sql);
  $counter = 0;
  $subset_count = 1;
  $subset_id = 0;
  while (!$rs->EOF) {
    if ($counter == 0) {
      $title = 'Podset '.$subset_count;
      $title = db_quote($title);
      $db->Execute("INSERT INTO subsets SET set_id=$set_id, title=$title");
      $subset_id = $db->Insert_ID();
      $subset_count++;
    }

    $sql = "UPDATE subset_combinations SET subset_id=$subset_id WHERE id=".$rs->fields['id'];
    $db->Execute($sql);

    $counter++;
    if ($counter >= $limit) {
      $counter = 0;
    }
    $rs->MoveNext();
  }
}

function subsets_get($subset_id) {
  global $db;
  $subset_id = (int)$subset_id;
  $sql = "SELECT * FROM subsets WHERE id=$subset_id";
  $rs = $db->Execute($sql);
  if ($rs) {
    $subset = array();
    $subset['id'] = $rs->fields['id'];
    $subset['title'] = $rs->fields['title'];
    return $subset;
  } else {
    return FALSE;
  }
}

function subset_get_set($subset_id) {
  global $db;
  $subset_id = (int)$subset_id;
  $sql = "SELECT set_id FROM subsets WHERE id=$subset_id";
  $rs_subset = $db->Execute($sql);
  if ($rs_subset) {
    $set_id = $rs_subset->fields['set_id'];
    return sets_get($set_id);
  }
}

function subsets_get_all() {
  global $db;
  $sql = "SELECT ss.id, ss.title FROM subsets ss INNER JOIN sets s ON s.id=ss.set_id WHERE s.active=1 ORDER BY ss.id ASC";
  $rs = $db->Execute($sql);
  $sets = array();
  $counter = 0;
  if ($rs->RecordCount()) {
    $sets = array();
    while (!$rs->EOF) {
      $sets[$counter]['id'] = $rs->fields['id'];
      $sets[$counter++]['title'] = $rs->fields['title'];
      $rs->MoveNext();
    }
    return $sets;
  } else {
    return FALSE;
  }
}

/*
 *
 * COMBINATIONS STUFF
 *
 */

/**
 * Clear combinations for this session
 */
function comb_clear($session) {
  global $db;
  $sql = "DELETE FROM combinations WHERE session_id=".$session['id'];
  $db->Execute($sql);
}

function comb_copy($session, $subset_id, $set_id) {
  global $db;
  $set_id = (int)$set_id;
  $subset_id = (int)$subset_id;

  $sql = "SELECT * FROM subset_combinations WHERE subset_id=$subset_id";
  $rs = $db->Execute($sql);
  $combs = array();
  $counter = 0;
  while (!$rs->EOF) {
    $combs[$counter]['set_id'] = $rs->fields['set_id'];
    $combs[$counter]['word1_id'] = $rs->fields['word1_id'];
    $combs[$counter++]['word2_id'] = $rs->fields['word2_id'];
    $rs->MoveNext();
  }

  $statement = $db->Prepare('INSERT INTO combinations (session_id, set_id, subset_id, word1_id, word2_id) VALUES (?, ?, ?, ?, ?)');
  foreach ($combs as $comb) {
    $set_id = $comb['set_id'];
    $word1 = $comb['word1_id'];
    $word2 = $comb['word2_id'];
    $db->Execute($statement, array($session['id'], $set_id, $subset_id, $word1, $word2));
  }
}

/**
 * Make new combination for new session
 */
function comb_make($session, $set_id) {
  global $db;
  $set_id = (int)$set_id;

  $word_comb = array();
  $word_comb_col1 = array();
  $word_comb_col2 = array();
  $counter = 0;

  $sql = "SELECT * FROM words WHERE set_id=$set_id";
  $rs_words = $db->Execute($sql);
  while (!$rs_words->EOF) {
    $word_word = $rs_words->fields['word'];
    $word_id = $rs_words->fields['id'];
    $word_comb_col1[$counter]['id'] = $word_id;
    $word_comb_col1[$counter++]['word'] = $word_word;
    $rs_words->MoveNext();
  }

  $word_comb_col2 = $word_comb_col1;
  shuffle($word_comb_col1);
  shuffle($word_comb_col2);
  $counter = 0;

  foreach ($word_comb_col1 as $wid1 => $word_set1) {
    foreach ($word_comb_col2 as $wid2 => $word_set2) {
      if (_comb_dup_test($word_set1, $word_set2, $word_comb)) {
        $word_comb[$counter]['first'] = $word_set1;
        $word_comb[$counter++]['second'] = $word_set2;
      }
    }
  }
  shuffle($word_comb);

  foreach ($word_comb as $comb) {
    $sql = "INSERT INTO combinations SET session_id=".$session['id'].", ";
    $sql .= "set_id=$set_id, ";
    $sql .= "word1_id=".$comb['first']['id'].", ";
    $sql .= "word2_id=".$comb['second']['id'];
    $db->Execute($sql);
  }
}

/**
 * Make new subset combinations
 */
function comb_subset_make($set_id) {
  global $db;
  $set_id = (int)$set_id;

  $word_comb = array();
  $word_comb_col1 = array();
  $word_comb_col2 = array();
  $counter = 0;

  $sql = "SELECT * FROM words WHERE set_id=$set_id";
  $rs_words = $db->Execute($sql);
  while (!$rs_words->EOF) {
    $word_word = $rs_words->fields['word'];
    $word_id = $rs_words->fields['id'];
    $word_comb_col1[$counter]['id'] = $word_id;
    $word_comb_col1[$counter++]['word'] = $word_word;
    $rs_words->MoveNext();
  }

  $word_comb_col2 = $word_comb_col1;
  shuffle($word_comb_col1);
  shuffle($word_comb_col2);
  $counter = 0;

  foreach ($word_comb_col1 as $wid1 => $word_set1) {
    foreach ($word_comb_col2 as $wid2 => $word_set2) {
      if (_comb_dup_test($word_set1, $word_set2, $word_comb)) {
        $word_comb[$counter]['first'] = $word_set1;
        $word_comb[$counter++]['second'] = $word_set2;
      }
    }
  }
  shuffle($word_comb);

  foreach ($word_comb as $comb) {
    $sql = "INSERT INTO subset_combinations SET ";
    $sql .= "set_id=$set_id, ";
    $sql .= "word1_id=".$comb['first']['id'].", ";
    $sql .= "word2_id=".$comb['second']['id'];
    $db->Execute($sql);
  }
}

/**
 * Helper function for comb_make. Tests new combination
 */
function _comb_dup_test($w1, $w2, $combs) {
  if ($w1['word'] == $w2['word']) {
    return FALSE;
  }
  foreach ($combs as $comb) {
    if (($comb['first']['word'] == $w2['word']) && ($comb['second']['word'] == $w1['word'])) {
      return FALSE;
    }
  }
  return TRUE;
}

/**
 * Get all set combinations for session
 */
function comb_get($session, $set_id, $subset_id=0) {
  global $db;
  $set_id = (int)$set_id;
  if ($subset_id) {
    $sql_add = " AND subset_id=$subset_id ";
  }
  $sql = "SELECT * FROM combinations WHERE session_id=".$session['id']." AND set_id=$set_id $sql_add ORDER BY id ASC";
  $rs = $db->Execute($sql);
  $combs = array();
  $counter = 0;
  if ($rs->RecordCount()) {
    while (!$rs->EOF) {
      $combs[$counter]['id'] = $rs->fields['id'];
      $combs[$counter]['word1_id'] = $rs->fields['word1_id'];
      $combs[$counter]['word2_id'] = $rs->fields['word2_id'];
      $combs[$counter]['answer_word_id'] = $rs->fields['answer_word_id'];
      $sqlw = "SELECT word FROM words WHERE id=".$rs->fields['word1_id'];
      $rsw = $db->Execute($sqlw);
      $combs[$counter]['word1_word'] = $rsw->fields['word'];
      $sqlw = "SELECT word FROM words WHERE id=".$rs->fields['word2_id'];
      $rsw = $db->Execute($sqlw);
      $combs[$counter++]['word2_word'] = $rsw->fields['word'];
      $rs->MoveNext();
    }
    return $combs;
  } else {
    return FALSE;
  }
}

/**
 * Mark selected combination
 */
function comb_mark($comb_id, $word_id) {
  global $db;
  $sql = "UPDATE combinations SET answer_word_id=$word_id WHERE id=$comb_id";
  $db->Execute($sql);
}

/**
 * See if any anwser left unanswered
 */
function comb_open_answers($combs) {
  foreach ($combs as $comb) {
    if (!$comb['answer_word_id']) {
      return TRUE;
    }
  }
  return FALSE;
}

/*
 *
 * WORDS STUFF
 *
 */

/**
 * Return true if at least two words exist for a set
 */
function word_exists($set_id) {
  global $db;
  $set_id = (int)$set_id;
  $sql = "SELECT * FROM words WHERE set_id=$set_id";
  $rs = $db->Execute($sql);
  if ($rs->RecordCount() > 1) {
    return TRUE;
  }
  return FALSE;
}

function word_get_all($set_id) {
  global $db;
  $set_id = (int)$set_id;
  $sql = "SELECT * FROM words WHERE set_id=$set_id";
  $rs = $db->Execute($sql);
  if ($rs->RecordCount()) {
    $ret = array();
    while (!$rs->EOF) {
      $ret[] = $rs->fields['word'];
      $rs->MoveNext();
    }
    return $ret;
  } else {
    return array();
  }
}

/*
 *
 * ADMIN STUFF
 *
 */

/**
 * Get set or all sets
 */
function admin_get_sets($id=0) {
  global $db;
  if ($id) {
    $id = (int)$id;
    $sql_add = " WHERE id=$id";
  } else {
    $sql_add = '';
  }
  $sql = "SELECT * FROM sets".$sql_add;
  $rs = $db->Execute($sql);
  $sets = array();
  $cnt = 0;
  while (!$rs->EOF) {
    $sets[$cnt]['id'] = $rs->fields['id'];
    $sets[$cnt]['title'] = $rs->fields['title'];
    $sets[$cnt++]['active'] = $rs->fields['active'];
    $rs->MoveNext();
  }
  return $sets;
}

/*
 *
 * HELEPERS STUFF
 *
 */

/**
 * Sanitize input
 */
function db_quote($param='') {
  global $db;
  return $db->qstr($param, get_magic_quotes_gpc());
}

/**
 * Parse all GET variables
 */
function geturl() {
  $keys = array_keys($_GET);
  $url = "/";
  $gets = array();
  foreach ($keys as $key) {
    $gets[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
  }
  return $gets;
}

/**
 * Parse all POST variables
 */
function getpost() {
  $keys = array_keys($_POST);
  $url = "/";
  $posts = array();
  foreach ($keys as $key) {
    $posts[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_STRING);
  }
  return $posts;
}

/**
 * Redirect to the url
 */
function redirect($query='') {
  global $base_url;
  header('Location: ' . $base_url . $query);
  die();
}

function time_start() {
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $starttime = $mtime;
  return $starttime;
}

function time_end($starttime) {
  $mtime = microtime();
  $mtime = explode(" ",$mtime);
  $mtime = $mtime[1] + $mtime[0];
  $endtime = $mtime;
  $totaltime = ($endtime - $starttime);
  if (function_exists('fb')) {
    fb("This page was created in ".$totaltime." seconds");
  } else {
    echo "This page was created in ".$totaltime." seconds";
  }
}
