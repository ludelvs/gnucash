<?php
/* ドライバ呼び出しを使用して ODBC データベースに接続する */
$dsn = 'sqlite:./gnucash.gnucash';

try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}

$graphType = $_GET['graphType'];
//$graphType = 'b9f5219d638b903a7e968486986f4d1a';

if (strlen($graphType) == 32) {
  $res['guidFlag'] = true;
} else {
//$sql = 'SELECT * FROM accounts WHERE account_type = :account_type';
//$stmt = $dbh->prepare($sql);
//$stmt->execute(array(':account_type' => 'ROOT'));
//$res = $stmt->fetch(PDO::FETCH_ASSOC);
//$rootGuid = $res['guid'];
  $rootGuid = '39e32d1d03c8385308274084d3d9c9b6';
  $sql = 'SELECT * FROM accounts WHERE parent_guid = :guid AND account_type = :account_type';
  $stmt = $dbh->prepare($sql);
  $stmt->execute(array(':guid' => $rootGuid, ':account_type' => 'ASSET'));
  $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$startDate = '20091201';
$monthStartDate = '20091201';
$startStr = strtotime($startDate);
$endDate = date('Ymt', $startStr);

$cashData = array();
switch ($graphType) {
  case 'monthlyAssets':
    $cashData['setting']['title'] = '月別資産';
    $cashData['setting']['yMax'] = '2000000';
    $cashData['setting']['yMin'] = '-500000';
    $cashData['setting']['name'] = 'assets';
    break;
  case 'totalAssets':
    $cashData['setting']['title'] = '総資産';
    $cashData['setting']['yMax'] = '8000000';
    $cashData['setting']['yMin'] = '2000000';
    $cashData['setting']['name'] = 'assets';
    break;
}

if ($res['guidFlag']) {
  $cashData['setting']['yMin'] = '0';
  $cashData['setting']['title'] = $_GET['name'];
  $cashData['setting']['name'] = $_GET['name'];
  $res[]['guid'] = $graphType;
}

$totalTmp = 0;

while (true) {
  if ($currentMonthFlag) {
    break;
  }
  if ($res['guidFlag']) {
    $total = calculateSplit($dbh, $graphType, $startDate, $endDate);

    if ($total > $totalTmp) {
      $cashData['setting']['yMax'] = yAxis($total);
      $totalTmp = $total;
    }
  } else {
    $total = getChildTotal($dbh, $res, $startDate, $endDate);
  }
  if ($monthStartDate == date('Ym01', time())) {
    $currentMonthFlag = TRUE;
    //break;
  }
  $cashData['date'][] = substr($endDate, 0, 4) . '-' . substr($endDate, 4, 2);
  $cashData['total'][] = $total;

  $startStr = strtotime($monthStartDate . '+1 month');
  $monthStartDate = date('Ym01', $startStr);
  if ($graphType == 'monthlyAssets' || $res['guidFlag']) {
    $startDate = date('Ym01', $startStr);
  }
  $endDate = date('Ymt', $startStr);
}

echo json_encode($cashData);

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
    //$sql = 'SELECT SUM(value_num) AS total '
    //     . 'FROM splits AS s '
    //     . 'INNER JOIN transactions AS t '
    //     . 'ON s.tx_guid = t.guid '
    //     . 'WHERE account_guid = :guid '
    //     . 'AND t.post_date between :start_date and :end_date ';

    //$stmt = $dbh->prepare($sql);
    //$stmt->execute(array(':guid' => $guid, ':start_date' => $startDate, ':end_date' => $endDate));
    //$res2 = $stmt->fetch(PDO::FETCH_ASSOC);
    //if ($res2) {
    //  $total += $res2['total'];
    //}
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
    return intVal($res2['total']);
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

