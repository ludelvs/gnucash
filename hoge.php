<?php
$num = -12345;
if (($num < 0)) {
  $num = abs($num);
}
$len = strlen($num) - 1;
$tenpow = pow(10, $len);
$formatedNum = $num / $tenpow;
$ceiledNum = ceil($formatedNum);

$result = $ceiledNum * $tenpow;

echo $result;
