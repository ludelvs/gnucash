<?php

require_once('gnucash_functions.php');
/* ドライバ呼び出しを使用して ODBC データベースに接続する */
$dsn = 'sqlite:/data/gnucash.gnucash';

try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
  exit;
}

$graphType = $_GET['graphType'];

if (strlen($graphType) == 32) {
  $res['guidFlag'] = true;
} else {
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
    $cashData['setting']['yMax'] = '20000000';
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


