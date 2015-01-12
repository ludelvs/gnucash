<?php

require_once('gnucash_functions.php');


$dsn = 'sqlite:./gnucash.gnucash';
try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}

$startDate = $_GET['startMonth'] . '01';
$endDate = $_GET['endMonth'] . '31';

$accountTypeList = array('INCOME', 'EXPENSE');

foreach ($accountTypeList as $accountType) {
  $totalAmount = 0;
  $accountList = getAccountByAccountType($dbh, $accountType);
  foreach ($accountList as $account) {
    $amount = calculateSplit($dbh, $account['guid'], $startDate, $endDate);
    $values['accountName'][] = $account['name'];
    $values['amount'][] = $amount;
    $totalAmount += $amount;
  }
  $values['accountName'][] = 'Total';
  $values['amount'][] =  $totalAmount;
  $totalAmountTmp[] = $totalAmount;
}
$deduction = $totalAmountTmp[0] - $totalAmountTmp[1];
$values['accountName'][] = '損益';
$values['amount'][] =  $deduction;

echo json_encode($values);
