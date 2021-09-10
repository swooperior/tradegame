<?php include(__DIR__.'/../dbconnect.php'); include(__DIR__.'/../functions.php');
/*if(!isset($_GET)){
	echo('Nothing to see here.');
}else{ 
	if($pass == "1029384756"){ //pass to secure cron job from being run manually. */

		//Selects everyone and regenerates 10% of maximum hp and 5 energy.  To be run every 60 seconds.
		$everyone = mysqli_query($GLOBALS['link'], "SELECT * FROM players");
		$maxhp = null;
		foreach($everyone AS $person){
			echo($person['name'].'<br>');
			$heal = $person['maxhp']*0.1;
			$pid = $person['pid'];
			$pcurhp = getPData($pid)['hp'];
			$pmaxhp = getPData($pid)['maxhp'];
			$penergy = getPData($pid)['energy'];
			$pmaxenergy = getPData($pid)['maxenergy'];
			if($pcurhp < $pmaxhp){
				changeHp($person['pid'],$heal);
			}
			if($penergy < $pmaxenergy){
				changeEnergy($person['pid'],3);
			}
			if($pcurhp > $pmaxhp){
				mysqli_query($GLOBALS['link'],"UPDATE players SET hp='$pmaxhp' WHERE pid='$pid'");
			}
			if($penergy > $pmaxenergy){
				mysqli_query($GLOBALS['link'],"UPDATE players SET energy='$pmaxenergy' WHERE pid='$pid'");
			}
			
			

		}
				
	//}
//}
?>
