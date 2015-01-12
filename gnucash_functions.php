<?php

function getAccountByGuid($dbh, $guid) {
    $sql = 'SELECT name, account_type '
         . 'FROM accounts '
         . 'WHERE guid = :guid';
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(':guid' => $guid));
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    return $res;
}

function getAccountByAccountType($dbh, $accountType) {
    $sql = 'SELECT guid, name, account_type '
         . 'FROM accounts '
         . 'WHERE account_type = :account_type';
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(':account_type' => $accountType));
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $res;
}

function getMonthList() {
  $i = 0;
  $monthList = array();
  while (TRUE) {
    $month = mktime(0, 0, 0, date('12') + $i, 1,   date('2009'));
    $monthList['month'][$i] =  date('Ym', $month);
    if ($monthList['month'][$i] == date('Ym')) {
      break;
    }
    $i++;
  }
  return $monthList;
}

function getChild($dbh, $res, &$list) {
  foreach ($res as $val) {
    $guid = $val['guid'];
    $sql = 'SELECT * FROM accounts WHERE parent_guid = :guid';
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(':guid' => $guid));
    $res2 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($res2) {
      if (existAccountTransaction($dbh, $guid)) {
        $list[] = $val;
      }
      getChild($dbh, $res2, $list);
    } else {
      $list[] = $val;
    }
  }
}

function existAccountTransaction($dbh, $accountGuid) {
    $sql = 'SELECT guid '
         . 'FROM splits '
         . 'WHERE account_guid = :account_guid';
    $stmt = $dbh->prepare($sql);
    $stmt->execute(array(':account_guid' => $accountGuid));
    $res = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($res) {
      return TRUE;
    }
    return FALSE;
}

function getChildTotal($dbh, $res, $startDate, $endDate) {
  getChild($dbh, $res, $childList);

  $startDate .= '150000';
  $endDate .= '150000';

  foreach ($childList as $val) {
    $guid = $val['guid'];
    $split = calculateSplit($dbh, $guid, $startDate, $endDate);
    if ($split) {
      $total += $split;
    }
  }
  return $total;
}

function calculateSplit($dbh, $guid, $startDate, $endDate) {

  // dbの日付とgnucashアプリ上での日付のズレを修正
  $startStr = strtotime($startDate . '-1 day');
  $startDate = date('Ymd', $startStr);
  $endStr = strtotime($endDate . '-1 day');
  $endDate = date('Ymd', $endStr);

  $startDate .= '150000';
  $endDate .= '150000';

  $account = getAccountByGuid($dbh, $guid);

  $sql = 'SELECT SUM(value_num) AS total '
       . 'FROM splits AS s '
       . 'INNER JOIN transactions AS t '
       . 'ON s.tx_guid = t.guid '
       . 'WHERE account_guid = :guid '
       . 'AND t.post_date between :start_date and :end_date';

  $stmt = $dbh->prepare($sql);
  $stmt->execute(array(':guid' => $guid, ':start_date' => $startDate, ':end_date' => $endDate));
  $res2 = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($res2['total']) {
    $total = intVal($res2['total']);
    if ($account['account_type'] == 'INCOME') {
      $total = -$total;
    }
    return $total;
  } else {
    return 0;
  }
}

function yAxis($num) {
  $result = array();
  if (($num < 0)) {
    $num = abs($num);
    $result['type'] = 'min';
  }
  $len = strlen($num) - 1;
  $tenpow = pow(10, $len);
  $formatedNum = $num / $tenpow;
  $ceiledNum = ceil($formatedNum);
  $result = $ceiledNum * $tenpow;

  return $result;
}

