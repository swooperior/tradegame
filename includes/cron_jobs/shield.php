<?php include('/home/reece/websites/tradeGame/includes/dbconnect.php'); include('/home/reece/websites/tradeGame/includes/functions.php');

$seconds = 1;
$minutes = 60;
$hours = 3600;
$days = 86400;

$time = time();
print_r(mysqli_query($GLOBALS['link'],"UPDATE `players` SET `shield_expires` = NULL WHERE `shield_expires` <= '$time'"));



?>
