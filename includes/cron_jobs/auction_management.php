<?php 
include('/var/www/tradeGame/includes/functions.php');include('/var/www/tradeGame/includes/dbconnect.php');
//auction mangement script
//updates auction status based on current time and duration of auctuon
//get auctions to be removed
date_default_timezone_set('UTC');
$date = date('m/d/Y h:i:s a', time());
$auctions = mysqli_query($GLOBALS['link'],"SELECT * FROM auctions WHERE status < 3");
foreach($auctions as $auction){
	$endtime = $auction['dur'] * (60 * 60);
	$endtime = strtotime($auction['date']) + $endtime;
	$today = strtotime($date);
	$ending = $today-$endtime;
	echo($ending);
	if($endtime < $today){
		if($auction['highbidder'] != 0){
			completeAuction($auction['aid']);
		}else{
			cancelAuction($auction['aid']);
		}	
	}
}



?>