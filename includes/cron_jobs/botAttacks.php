<?php 
include('/var/www/tradeGame/includes/functions.php');include('/var/www/tradeGame/includes/dbconnect.php');

//bot attacks script, $n random bots will attack random targets every hour.
$n = 10;
$regex = '^[[:digit:]]{3}$';
$bots = mysqli_query($GLOBALS['link'],"SELECT * FROM players WHERE email REGEXP '$regex' ORDER BY RAND() LIMIT $n");
$result = mysqli_query($GLOBALS['link'],"SELECT pid FROM `players` WHERE `shield_expires` IS NULL AND rating > 0 ORDER BY RAND() LIMIT $n");
$num = mysqli_num_rows($result);
if($num > 0){
	while($row = mysqli_fetch_assoc($result)){
		$targets[] = $row;
	}
}


foreach($bots as $bot){
	$t = rand(0,$n);
	$target = $targets[$t]['pid'];
	Attack($bot['pid'],$target);
	hunt($bot['pid'], select_enemy($bot['level']));
	hunt($bot['pid'], select_enemy($bot['level']));
	hunt($bot['pid'], select_enemy($bot['level']));

}
?>