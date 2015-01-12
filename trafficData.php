<?php

/* ドライバ呼び出しを使用して ODBC データベースに接続する */
$dsn = 'mysql:dbname=umobile_traffic;host=localhost';
$user = 'webapp';
$password = 'eSdntnN0';

try {
      $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
      echo 'Connection failed: ' . $e->getMessage();
}

$dbh = new PDO($dsn, $user, $password);

$sql = 'SELECT * FROM (SELECT date, traffic FROM daily_traffics ORDER BY date DESC LIMIT 30) AS tmp ORDER BY date';
//$sql = 'SELECT date, traffic FROM daily_traffics ORDER BY date LIMIT 30';
$stmt = $dbh->prepare($sql);
$stmt->execute();

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
  $res['date'][] = substr($row['date'], -5);
  $res['traffic'][] = intval($row['traffic']);
}
echo json_encode($res);

