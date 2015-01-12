<?php

require_once('gnucash_functions.php');

/* ドライバ呼び出しを使用して ODBC データベースに接続する */
$dsn = 'sqlite:./gnucash.gnucash';

try {
      $dbh = new PDO($dsn);
      $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
}

$rootGuid = '39e32d1d03c8385308274084d3d9c9b6';
$sql = 'SELECT * FROM accounts WHERE parent_guid = :guid AND account_type = :account_type';
$stmt = $dbh->prepare($sql);
$stmt->execute(array(':guid' => $rootGuid, ':account_type' => 'EXPENSE'));
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

$startDate = '20091201';
$monthStartDate = '20091201';
$startStr = strtotime($startDate);
$endDate = date('Ymt', $startStr);

$cashData = array();
switch ($_GET['graphType']) {
case 'monthlyAssets':
  $cashData['setting']['title'] = 'Monthly assets';
  $cashData['setting']['yMax'] = '2000000';
  $cashData['setting']['yMin'] = '-500000';
  $cashData['setting']['name'] = 'assets';
  break;
case 'totalAssets':
  $cashData['setting']['title'] = 'Total assets';
  $cashData['setting']['yMax'] = '8000000';
  $cashData['setting']['yMin'] = '2000000';
  $cashData['setting']['name'] = 'assets';
  break;
}
getChild($dbh, $res, $childList);

foreach ($childList as $val) {
  $cashData['guid'][] = $val['guid'];
  $cashData['name'][] = $val['name'];
}
echo json_encode($cashData);
