<?php

require_once('gnucash_functions.php');


$dsn = 'sqlite:./gnucash.gnucash';
try {
  $dbh = new PDO($dsn);
  $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
}

$startDate = '20141201';
$endDate = '20141231';

$accountTypeList = array('INCOME', 'EXPENSE');

foreach ($accountTypeList as $accountType) {
  $totalAmount = 0;
  $accountList = getAccountByAccountType($dbh, $accountType);
  foreach ($accountList as $account) {
    $amount = calculateSplit($dbh, $account['guid'], $startDate, $endDate);
    print_r($account['name'] . " " . $amount);
    print "\n";
    $totalAmount += $amount;
  }
  print $totalAmount;
  print "\n";
  $totalAmountTmp[] = $totalAmount;
}
$deduction = $totalAmountTmp[0] - $totalAmountTmp[1];
print $deduction;
print "\n";
