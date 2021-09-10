<?php require('../includes/header.php');

//Catacombs Raid
//Author - swoops
//Required level 10
$reqlevel = 10;
//Boss - Skeleton King
//Boss ID
$boss = "";
//Unique Loot:
$loot[] = "";
//todo; add loot to database and ge
//
//
$timenow = strtotime("now");


$startTime = strtotime("+1 hour");


echo("<p>Now: ".$timenow);
echo("<p>Starts: ".$startTime);


if(strtotime('+3 hours',$startTime) <= $timenow){
	//Registration
	echo('<p>Registration!</p>');





}elseif(strtotime('+3 hours',$startTime) < $timenow && strtotime('+3 hours 10 minutes',$startTime) < $timenow){
	//Battle
	echo('<p>Battle!');





}elseif(strtotime('+3 hours 10 minutes',$startTime) < $timenow && $timenow < strtotime('+6 hours',$startTime)){
	//Results
	echo('<p>Results!');




}else{
	echo('I dunno lol');
}

require('../includes/footer.php'); ?>