<?php 
//select renemy
//boss fight script

//Malevolant Spirit, level 20 boss

//change for database bossid
$bossid = 28;

$bossData = getEData($bossid);
$pdata = getPData($_SESSION['pid']);

$starttime = '26 Mar 2019';

$timeleft = timeuntil(strtotime('+20 minutes',$starttime));

$datafile = "raids/bossdmg.json";
$bossHp = $bossData['hp'];
$fresh = true;
$logData = array();
if(filesize($datafile)){
	$jsondata = file_get_contents($datafile);
	$logData= json_decode($jsondata, true);
	$fresh = false;
	$dmg = 0;
	$mydmg = 0;
	foreach($logData as $log){
		$dmg = $dmg + $log['dmg'];
		if($log['pid'] == $_SESSION['pid']){
			$mydmg = $mydmg + $log['dmg'];
		}
	}	
	$bossHp = $bossHp - $dmg;
}else{
	$jsondata = json_encode($logData,JSON_PRETTY_PRINT);
	$logData= json_decode($jsondata, true);
	$bossHp = $bossData['hp'];
	$fresh = true;	
}


$dmgpercent = floor(($mydmg / $dmg) * 100);
?>

<div style="text-align:center;">
<h3><?php echo(print_enemy($bossid)); ?></h3>
<img style="width:25%;"src="<?php echo($bossData['image']);?>" />
<div id="boss-hp" style="">
<div id="hp-bar" style="">
</div>
</div>

<p><b>Current Boss Hp: <?php echo($bossHp); ?></b></p>
<p><b>My Damage Done: <?php echo($mydmg."(".$dmgpercent."%)");?></b></p>


<form method='POST'>
<input type="submit" value="Attack" name="attack" />
</form>



<?php 
if(isset($_POST['attack'])){
	
	$timenow = strtotime('now');
	$pdata = getPData($_SESSION['pid']);
	
	$emHp = $bossData['hp'];
	$pmHp = $pdata['maxhp'];
	$pHp = $pdata['hp'];
	$pNrgy = $pdata['energy'];
	if($pHp <= 0 || $pNrgy <= 24){
		echo('Youre too weak to attack...');
		die();
		}
	
	$pAtk = ceil($pdata['atk']+(1.1*$pdata['def'])*(rand(1,1.2))+rand($pdata['level'],$pdata['level']+5) - (0.2*$bossData['def']));
	$eAtk = ceil($bossData['atk']+(1.1*$bossData['def'])*(rand(1,1.2))+rand($bossData['level'],$bossData['level']+3) - (0.2*$pdata['def']));
	
	$pHp = $pHp - $eAtk;		
	$bossHp = $bossHp - $pAtk;
			
			//Display round results
			
			
			if($bossHp <= 0){	
				$eAtk = 0;
				echo("<b>".$pdata['name'].' Deals '.$pAtk.' damage. <br> '.print_enemy($bossid).' Dies...<br>');
				echo('THE BOSS HAS BEEN DEFEATED');
				//Distribute loot and trigger status change
				
				
			}elseif($pHp <= 0){
				$pHp = 0;
				echo("<br> ".$pdata['name'].' Deals '.$pAtk.' damage. <br> '.print_enemy($bossid)."Deals ".$eAtk." damage.<br> ".$pdata['name']." Dies...<br>");
			}else{
				$pHp = $pHp - $eAtk;
			     echo("<br> ".$pdata['name'].' Deals '.$pAtk.' damage. <br> '.print_enemy($bossid).' Deals '.$eAtk.' damage.<br>');
			}
			
			echo(print_enemy($bossid).": ".$bossHp."/".$emHp."<br>");
			echo($pdata['name'].": ".$pHp."/".$pmHp."<br>");
			
			$logArray = array();
			try
			{
				$logArray = array(
					'pid'=> $pdata['pid'],
					'dmg'=>  "$pAtk",
					'time'=>  "$timenow"
				);
				
					array_push($logData,$logArray);
					$jsondata = json_encode($logData,JSON_PRETTY_PRINT);
				
				
				
				
				if(file_put_contents($datafile,$jsondata)){
						//echo('Attack Successful');
						changeHp($_SESSION['pid'],-$eAtk);
						changeEnergy($_SESSION['pid'],-25);
					}else{
						echo('Error');
					}
				
			}
			catch (Exception $e){
				echo 'Caught Exception: ', $e->getMessage(), "\n";
			}
			
			
	
	}
	$logData = array_reverse($logData);
	foreach($logData as $log){
		if($log['dmg'] >= 1){
			echo("<p>".getPData($log['pid'])['name']." Dealt ".$log['dmg']." Damage.</p>");
		}
		
	}

function calculate_damage($logData){
	//Creates an array of pids and damage dealt ordered by highest damage dealt overall
	$players = array();
	//idk how the fuck to do this
	foreach($logData as $log){
		if(in_array($log['pid'],$players['pid'])){
			$players['pid']['dmg'] += $log['dmg'];
		}else{
			array_push($players, array(
				'pid'=>$log['pid'],
				'dmg'=>$log['dmg']
			));
		}
	}


}
function random_mythic(){
	$mythics = array(34,35,36,37,38);
	return $mythics[rand(0,4)];

}
function distribute_rewards($damageArray){
	//Give a mythic item to highest d
	$count = 1;
		foreach($damageArray as $pid){
		if($count == 1){
			giveItem(random_mythic(),$pid['pid']);				
		}elseif($count == 2){
			
		}elseif($count == 3){
			
		}	
	}
}
?>
</div>