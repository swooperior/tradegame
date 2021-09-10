<?php require('includes/header.php');
//Attack log page, may replace at some point for a killboard.
if(!isset($_SESSION['pid'])){
	echo('You must be logged in to view this page (Currently)...');
}else{
	$pid = $_SESSION['pid'];
	$logs = getAttackLogs($pid);
	if($logs == false){
		echo('You have a clean slate.');
	}else{


	echo('<h1>Attack Log</h1>');
	echo('<div class="attacks-area">');
	foreach($logs as $log){
		//init log variables
		$color = null;
		$action = null;
		$actionStr = null;
		$status = null;
		$won = null;
		//player data
		$attacker = getPData($log['attacker']);
		$defender = getPData($log['defender']);
		$winner = getPData($log['winner']);
		if($winner['pid'] == $pid){
			$won = true;
		}else{
			$won = false;
		}
		if($won){ //Update log color, red if lost, green if won.
			$color = 'green';
		}else{
			$color = 'red';
		}
		if($attacker['pid'] == $pid){ //Update action (Attacked or Defended)
			$actionStr = ' Attack ';
			$action = 'attacker';
		}else{
			$actionStr = ' Defence ';
			$action = 'defender';
		}
		if($log[$action] == $winner['pid']){
			$status = 'Successful ';
		}else{
			$status = 'Failed  ';
		}

		$ltitle = "<h2>".$status.$actionStr."</h2>";
		$color = "background-color:".$color;

		echo("<div class='attack-log' style='$color'>");
		echo($log['time']);
		echo($ltitle);
		echo($attacker['name']."(".$attacker['rating'].") <img src='includes/imgs/clash-icon.png' class='ico'/> ".$defender['name']."(".$defender['rating'].")<br>");
		echo(-$log['damagetaken']."/".$attacker['maxhp']."   ".$log['damagedone']."/".$defender['maxhp']."<br>");
		if($won){
			echo("<img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' />".$log['silver']." gained.<br>");
		}else{
			echo("<img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' />".-$log['silver']." lost.<br>");

		}
		if($action == 'defender'){
				echo("<a href='player.php?pid=".$attacker['pid']."' style='background-color:#000;'>View Player</a> <a href='fight.php?eid=".$attacker['pid']."' style='background-color:#000;'>Get Revenge</a>");
			}
		
		echo("</div>");
		
	}
	echo("</div>");
	}
}



require('includes/footer.php'); ?>