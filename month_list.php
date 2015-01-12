<?php

require_once('gnucash_functions.php');

$monthList = getMonthList();

echo json_encode($monthList);
