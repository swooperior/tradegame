<?php require 'dbconnect.php';
include 'serious_tokens.php';
//FUNCTIONS
//----------------------
//Registration/Login System
//-----------------------
function registerPlayer($joindate, $email, $name, $pass){
//Insert validation
$query = "INSERT INTO `players`(joindate,email,name,pass) VALUES('".$joindate."','".$email."','".$name."','".$pass."');";
if(mysqli_query($GLOBALS['link'],$query)){
	$id = mysqli_fetch_array(mysqli_query($GLOBALS['link'],"SELECT pid FROM players ORDER BY joindate DESC;"));
	$id = $id[0];
	$query = "INSERT INTO `players-equipped`(id) VALUES('$id');";
if(mysqli_query($GLOBALS['link'],$query)){
	echo("Registration completed successfully.");
}else{
	echo("Registration failed.");
}
}
}
function loginPlayer($name, $pass, $token){
	if(check_token($token, 'login')){
		$query = "SELECT * FROM players WHERE name='$name' AND pass='$pass'";
	$playerq = mysqli_query($GLOBALS['link'],$query) or die(mysqli_error($GLOBALS['link']));
	if (mysqli_num_rows($playerq) > 0){
		session_start();
		$playerdata = mysqli_fetch_assoc($playerq);
		$_SESSION['pid'] = $playerdata['pid'];
	} else{
		echo("Login failed.");
	}
	}
}
//Player related
//-----------------------
function getPData($pid, $o = NULL){
	$pdq = mysqli_query($GLOBALS['link'],"SELECT * FROM `players` WHERE pid='$pid'");
	//Find any equipped items and add their stats to player stats.
	$playerdata = mysqli_fetch_assoc($pdq);
	if($pdeq = mysqli_fetch_array(mysqli_query($GLOBALS['link'],"SELECT * FROM `players-equipped` WHERE id='$pid'"))){
		if($pdeq != null){
			$atkup = 0;
			$defup = 0;
			$hpup = 0;
			$equipslots = 5;
			for($i=1;$i <= $equipslots;$i++){
				$tmp = null;
				$tmp = getIData($pdeq[$i]);
				if($tmp['atkup'] > 0){
					$atkup += $tmp['atkup'];
				}
				if($tmp['defup'] > 0){
					$defup += $tmp['defup'];
				}
				if($tmp['hpup'] > 0){
					$hpup += $tmp['hpup'];
				}
			}
			if($o == null){
				$playerdata['atk'] += $atkup;
				$playerdata['def'] += $defup;
				$playerdata['maxhp'] += $hpup;
				}
		}
		return $playerdata;
	}else{
		return false;
	}
}

//-----------------------
//Item related
//-----------------------
function getIData($iid){
	$idq = mysqli_query($GLOBALS['link'],"SELECT * FROM `items` WHERE id='$iid'");
	$itemdata = mysqli_fetch_assoc($idq);
	return $itemdata;
}
function hasItem($iid,$pid){
	$item = mysqli_query($GLOBALS['link'],"SELECT * FROM `players-inventory` WHERE pid='$pid' AND `item`='$iid'");
	if(mysqli_num_rows($item) != 0|null){
		return true;
	}else{
		return false;
	}
}
//-----------------------
//Auction related
//-----------------------
function getAData($aid){
	$idq = mysqli_query($GLOBALS['link'],"SELECT * FROM `auctions` WHERE aid='$aid'");
	$itemdata = mysqli_fetch_assoc($idq);
	return $itemdata;
}
function postAuction($pid, $iid, $amount, $dur, $bid, $buy){
	if(isset($_SESSION['pid'])){
		$exists = mysqli_query($GLOBALS['link'],"SELECT * FROM `players-inventory` WHERE pid='$pid' AND item='$iid' AND amount >= $amount");
		if($exists && $amount > 0 && $buy > 0 && $bid < $buy && $bid > 0){
			mysqli_query($GLOBALS['link'],"INSERT INTO auctions(pid,iid,amount,dur,bid,buyout) VALUES($pid,$iid,$amount,$dur,$bid,$buy);") or die(mysqli_error($GLOBALS['link']));
			removeItem($_SESSION['pid'],$iid,$amount);
			$cost = intval($buy * 0.05);
			changeSilver($_SESSION['pid'],-$cost);
		}else{
			echo '<br>You cannot sell what does not exist.';
		}
	}
}

function cancelAuction($aid){

	if(mysqli_query($GLOBALS['link'],"UPDATE auctions SET status=4 WHERE aid='$aid'")){
		$auction = getAData($aid);
		giveItem($auction['pid'],$auction['iid'],$auction['amount']);
		echo('<br>Auction Cancelled.');
	}
}

function bidAuction($aid, $bid){
	if(isset($_SESSION['pid'])){

		$pid = $_SESSION['pid'];
		$pData = getPData($_SESSION['pid']);
		$aData = getAData($aid);
		if($bid < $aData['bid']){
			echo('<br>Your must bid higher than the current highest bid.');
		}elseif($pid == $aData['pid']){
			echo('<br>You cannot bid on your own listings.');
		}elseif($bid >= $aData['buyout']){
			echo('Why bid higher than the buyout price?');
		}else{
			if($pData['silver'] >= $aData['bid'] && $bid >= $aData['bid']){
				if($aData['highbidder'] != 0){
					if($bid == $aData['bid']){
						echo('You must bid higher than the current high bid!');
					}
					//Notify old high bidder that they have been outbid.
					
					//Give silver back to old high bidder.
					$silback = intval($aData['bid'] - ($aData['bid'] * 0.05));
					changeSilver($aData['highbidder'],$silback);
				}
				//place bid on the item.
				if(mysqli_query($GLOBALS['link'],"UPDATE auctions SET bid='$bid', highbidder='$pid', status='1' WHERE aid='$aid';")){
					echo '<br>Bid placed successfully, you are now the high bidder.';
					changeSilver($pid,-$bid);
				}else{
					echo '<br>Failed to update auction';
				}
			}
		}
	}
}
function buyAuction($aid){
	$aData = getAData($aid);
	if($aData['status'] == 0){
		$pData = getPData($_SESSION['pid']);
		if($pData['silver'] >= $aData['buyout']){
			giveItem($_SESSION['pid'],$aData['iid'], $aData['amount']);
			changeSilver($_SESSION['pid'],-$aData['buyout']);
			changeSilver($aData['pid'],$aData['buyout']);
			echo("You bought the ".getIData($aData['iid'])['name']." x ". $aData['amount']." from ".getPData($aData['pid'])['name']);
			mysqli_query($GLOBALS['link'],"UPDATE auctions SET status=3 WHERE aid=$aid");
		}else{
			echo('You do not have enough silver to do this.');
		}
	}
}
function completeAuction($aid){
	$auction = getAData($aid);
	//assign highbidder
	$item = $auction['iid'];
	$quant = $auction['amount'];
	$winner = $auction['highbidder'];
	$seller = $auction['pid'];
	$price = $auction['bid'];
	
	if(mysqli_query($GLOBALS['link'],"UPDATE auctions SET status=3 WHERE aid='$aid';")){
		changeSilver($seller,+$price);
		giveItem($winner,$item,$quant);

		//notify highbidder & seller item was sold
	}

	
}

function viewAuctions($arg = 1){
	$aq = mysqli_query($GLOBALS['link'],"SELECT * FROM auctions WHERE $arg ORDER BY `date` ASC,`buyout` ASC;");
	if (mysqli_num_rows($aq) > 0) {
    // output data of each row
		while($row = mysqli_fetch_assoc($aq)) {
			if ($row['highbidder'] == 0){
				$highbidder['name'] = "None";
			}else{
				$highbidder = getPData($row['highbidder']);	
			}
			$endtime = strtotime($row['date']) + ($row['dur']*3600);
			$timenow = strtotime(time());
			$ending = $timenow + $endtime;
			$buttons = "<button name='view' value='".$row['aid']."' type='submit'>View</button>";
			
			
			echo "<tr><td>".print_item($row["iid"])."</td><td>". $row["amount"]."</td><td>".getPData($row["pid"])['name']."</td><td>".timeuntil($ending)."</td><td>".$row['bid']."</td><td>". $row['buyout']. "</td><td>".$buttons."</td></tr>";
		}
	}else{
		echo "0 results";
	}
}
//------------------------
//StatChange functions
//------------------------
function giveShield($pid, $seconds){
	//gives $pid shield for $seconds
	$time = time()+$seconds;
	if(mysqli_query($GLOBALS['link'],"UPDATE `players` SET `shield_expires`='$time' WHERE pid='$pid'")or die(mysqli_error($GLOBALS['link']))){
		echo(($seconds/60)." minute shield given to ".getPData($pid)['name']);
		return true;
	}else{
		return mysqli_error($GLOBALS['link']);
	}
}
function shield_check($pid){ //returns whether player has a shield active
	$pdata = getPData($pid);
	$exptime = $pdata['shield_expires'];
	if($exptime == time()){
		return false;
	}elseif($exptime < time()){
		return false;
	}elseif($exptime > time()){
		return "<div class='tooltip'><span class='fas fa-user-shield'></span><span class='tooltiptext'>".timeuntil($exptime)."</span></div>";
	}
}

function giveItem($pid, $id, $amount){
	$exists = mysqli_query($GLOBALS['link'],"SELECT * FROM `players-inventory` WHERE pid='$pid' AND item='$id'");
	if(mysqli_num_rows($exists) == 0){
		if(mysqli_query($GLOBALS['link'],"INSERT INTO `players-inventory` (pid, item, amount) VALUES ('$pid', '$id', '$amount');")){
		echo("<br>".print_item($id)." Placed in your inventory.<br>");
		}else{
		echo('Something went wrong, the item was not placed into your inventory.<br>');
		}
	}else{ //item exists in inventory already, update.
		if(mysqli_query($GLOBALS['link'],"UPDATE `players-inventory` SET amount=amount+$amount WHERE pid='$pid' AND item='$id';")){
		echo("<br>".print_item($id)." Placed in your inventory.<br>");
		}else{
		echo('Something went wrong, the item was not placed into your inventory.<br>');
		}
	}
	
}
function removeItem($pid, $id, $amount){
	$exists = mysqli_fetch_assoc(mysqli_query($GLOBALS['link'],"SELECT * FROM `players-inventory` WHERE pid='$pid' AND item='$id'"));
	if(count($exists) == 0 || $exists == null){
		echo('<br>Item does not exist and therefore cannot be removed. ');
		return false;
	}else{ //item exists in inventory already, update.
		if($exists['amount'] == 1){
			if(mysqli_query($GLOBALS['link'],"DELETE FROM `players-inventory` WHERE pid='$pid' AND item='$id';")){
				//echo('<br>Item Placed in your inventory.<br>');
				return true;
				}else{
				echo('Something went wrong, the item was not placed into your inventory.<br>');
			}
		}else{
			if(mysqli_query($GLOBALS['link'],"UPDATE `players-inventory` SET amount=amount-$amount WHERE pid='$pid' AND item='$id';")){
				//echo('<br>Item removed from your inventory.<br>');
				return true;
			}else{
				echo('Something went wrong, the item was not placed into your inventory.<br>');
			}
		}
	}
	
}
function levelUp($pid){
	$pdata = getPData($pid);
	if($pdata['exp'] >= $pdata['reqxp']){
		$level = $pdata['level'] + 1;
		$reqxp = floor(exp(($level*0.4))*1.3)+10;
		if(mysqli_query($GLOBALS['link'],"UPDATE players SET exp='0', level='$level', reqxp='$reqxp' WHERE pid='$pid'")){
			echo('<p>You levelled up! Gratz mon!'); //LEVEL UP
			echo('<br>Next level:'.$level. '.  Exp to this level: '.$reqxp);

			//Set hp and energy to their max
			changeHp($pdata['pid'],$pdata['maxhp']);
			changeEnergy($pdata['pid'],$pdata['maxenergy']);

		}else{
			echo('Failed to level up');
		}
		
	}
}
function giveExp($pid, $exp){
	$pdata = getPData($pid);
	echo ("<p>".$exp." Experience gained.");
	$exp = $pdata['exp'] + $exp;
	mysqli_query($GLOBALS['link'],"UPDATE players SET exp='$exp' WHERE pid='$pid'");
	if($exp >= $pdata['reqxp']){
		levelup($pid);
	}
}
function changeSilver($pid, $silver){

	$pdata = getPData($pid);
	if($silver > 0){
		$status = " gained.";
	}else{
		$status = " lost.";
	}
	echo ("<p><img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' />".$silver.$status);
	$silver = $pdata['silver'] + $silver;
	mysqli_query($GLOBALS['link'],"UPDATE players SET silver='".$silver."' WHERE pid='".$pid."'");
}
function changeGold($pid, $gold){
	$pdata = getPData($pid);
	echo ("<p>".$gold." Gold obtained.");
	$gold = $pdata['gold'] + $gold;
	mysqli_query($GLOBALS['link'],"UPDATE players SET gold='".$gold."' WHERE pid='".$pid."'");
}
function changeHp($pid, $change){
	$pdata = getPData($pid);
	$newhp = $pdata['hp'] + $change;
	$maxhp = $pdata['maxhp'];
	if ($newhp <= 0){
		mysqli_query($GLOBALS['link'],"UPDATE players SET hp='0' WHERE pid='$pid'");
	}elseif($newhp >= $maxhp){
		mysqli_query($GLOBALS['link'],"UPDATE players SET hp='$maxhp' WHERE pid='$pid'");
	}else{
		mysqli_query($GLOBALS['link'],"UPDATE players SET hp='$newhp' WHERE pid='$pid'");
	}
}
function changeEnergy($pid, $change){
	$pdata = getPData($pid);
	$newenergy = $pdata['energy'] + $change;
	$maxenergy = $pdata['maxenergy'];
	if ($newenergy <= 0){
		mysqli_query($GLOBALS['link'],"UPDATE players SET energy='0' WHERE pid='$pid'");
	}elseif($newenergy >= $maxenergy){
		mysqli_query($GLOBALS['link'],"UPDATE players SET energy='$maxenergy' WHERE pid='$pid'");
	}else{
		mysqli_query($GLOBALS['link'],"UPDATE players SET energy='$newenergy' WHERE pid='$pid'");
	}
}
function changeRating($pid, $change){
	$pdata = getPData($pid);
	$newrating = $pdata['rating'] + $change;
	mysqli_query($GLOBALS['link'],"UPDATE players SET rating='$newrating' WHERE pid='$pid'");
}
function trainStat($pid, $stat){
	$pData = getPData($pid,'hard');
	$statlevel = $pData[$stat];
	$level = $pData['level'];
	switch($stat){
		case($stat == 'atk' || $stat == 'def'): //Attack or defence
			if($statlevel < $pData['level']){
				$cost = (100*$statlevel);
				if($pData['silver'] >= $cost){ //If player can afford it...
					if(mysqli_query($GLOBALS['link'],"UPDATE players SET `$stat`=`$stat`+1 WHERE `pid`='$pid';")){
						changeSilver($pid,-$cost);
						header('Location:training.php');
					}
				}
				break;
			}else{
				echo 'You must gain more exp before you can train this stat any more.';
				break;
			}
		case($stat == 'maxhp' || $stat == 'maxenergy'): //If is maxhp or maxenergy
			if(($stat - 100) < $pData['level']){
				$cost = (500*($statlevel - 99));
				if($pData['silver'] >= $cost){ //If player can afford it...
					if(mysqli_query($GLOBALS['link'],"UPDATE players SET `$stat`=`$stat`+1 WHERE `pid`='$pid';")){
						changeSilver($pid,-$cost);
						header('Location:training.php');
					}
				}
				break;
			}else{
				echo 'You must gain more exp before you can train this stat any more.';
				break;
			}
	}
	
	
}

//------------------------
//!!!!Battle Functions!!!!
//------------------------
//Attack function
function Attack($attacker, $defender){
	$energycost = 30;
	$ratingchange = 10;


	$pid = $attacker;
	$attacker = getPData($attacker);
	$defender = getPData($defender);
	$rankrange = abs($defender['rating'] - $attacker['rating']);

	//check for shields
	if(shield_check($defender['pid'])){
		echo('Target is shielded.  You cannot attack.');
	}else{
		if($attacker['rating'] > $defender['rating']){
		$ratingchange = 10;
	}else{
		$ratingchange = $ratingchange + $rankrange;
	}
	
	if($attacker['energy'] >= $energycost){

	//check lvl range
	$range = 3;
	if(abs($attacker['level'] - $defender['level']) <= $range && $attacker['energy'] >= $energycost && $attacker['hp'] >= round($attacker['hp']*0.2)){
		


		//Attacker and Defender Score

		$ascore = $attacker['atk'] + round(0.5*$attacker['def']) + $attacker['hp'] + $attacker['level'];
		$dscore = $defender['atk'] + $defender['def'] + $defender['hp'] + $defender['level'];
		


		if($ascore > $dscore){
			//Attacker won
			logAttack($attacker['pid'],$defender['pid'],$attacker['pid'],$defender['hp'],abs($ascore - $dscore),$defender['silver']*0.3);
			echo('You defeated '.$defender['name'].'!');
			echo('<br>You suffered '.abs($ascore - $dscore).' damage in the attack.');
			changeEnergy($pid,-$energycost);  //flat amount
			changeHp($pid,-abs($ascore - $dscore));
			changeHp($defender['pid'],-$defender['hp']);
			changeSilver($pid,round($defender['silver']*0.3));
			echo(' Was pillaged from their coffers.');
			changeSilver($defender['pid'],-round($defender['silver']*0.3));
			echo('<br>');
			changeRating($pid,+$ratingchange);
			changeRating($defender['pid'],-$ratingchange);
			giveShield($defender['pid'],3600);
			echo("<p>$ratingchange Rating Gained.");
			
		}elseif($dscore>$ascore){
			logAttack($attacker['pid'],$defender['pid'],$defender['pid'],$attacker['hp'],abs($ascore - $dscore),round($attacker['silver']*0.3));
			echo('You were overpowered by '.$defender['name'].'.<br>');
			echo('<br>You suffered '.abs($ascore - $dscore)+$attacker['hp'].' damage in the attack.');
			changeEnergy($pid,-50);
			changeHp($defender['pid'],abs($ascore - $dscore));
			changeHp($pid,-$attacker['hp']);
			changeSilver($pid,-round($attacker['silver']*0.3));
			echo(' Was taken from our coffers...');
			changeSilver($defender['pid'],round($attacker['silver']*0.3));
			echo(' Gained by the enemy.');
			echo('<p>Guttertrash.');
			changeRating($defender['pid'],+10);
			changeRating($pid,-10);
			echo('<p>10 Rating Lost.');
		}else{
			//Draw
			echo('Draw Match');
		}
	}elseif(abs($attacker['level'] - $defender['level']) > $range){
		if($attacker['level'] > $defender['level']){
			echo('Your opponent is too weak to attack.');
		}else{
			echo('You are too weak to fight this opponent.');
		}
	}
}else{
	echo('You do not have enough energy to attack this opponent (requires 30).');
}
	}

	
}

//Log the attack in the database.
function logAttack($attacker,$defender,$winner,$admg,$ddmg,$silver){
	mysqli_query($GLOBALS['link'],"INSERT INTO attacklog(attacker,defender,winner,damagetaken,damagedone,silver) VALUES($attacker,$defender,$winner,$ddmg,$admg,$silver);");
}
//------------------------
//!!!!!Event Functions!!!!
//------------------------
//Hunt Function
function hunt($pid, $selectedenemy){
//Damage roll
	//Select player and enemy, then fight.
	$pdata = getPData($pid);
	if($pdata['energy'] >= 5 && $pdata['hp'] >= 1){
		//$enemy = select_enemy($pdata['level']);
		$enemy = $selectedenemy;
		if($enemy == false){
			return false;
		}
		$edata = getEData($enemy);
		$pmHp = $pdata['hp'];
		$emHp = $edata['hp'];
		$eHp = $emHp;
		$pHp = $pmHp;


		//initialise battle data
		$expdrop = ceil(exp(0.2*$edata['level'])) + $edata['expdrop']; //adds dbxpdrop to normal system.
		$count = 1;
		$eDmg = 0;
		do { //Round loop
			$pAtk = ceil($pdata['atk']+(1.1*$pdata['def'])*(rand(1,1.2))+rand($pdata['level'],$pdata['level']+5));
			$eAtk = ceil($edata['atk']+(1.1*$edata['def'])*(rand(1,1.2))+rand($edata['level'],$edata['level']+3));
			
			echo(print_enemy($enemy).": ".$eHp."/".$emHp."<br>");
			echo($pdata['name'].": ".$pHp."/".$pmHp."<br>");
			//Display round results
			$eHp = $eHp - $pAtk;
			
			if($eHp <= 0){	
				$eAtk = 0;
				echo("<b>Round ".$count ."</b>:<br> ".$pdata['name'].' Deals '.$pAtk.' damage. <br> '.print_enemy($enemy).' Dies...<br>');
			}elseif($pHp <= 0){
				$eAtk = 0;
				echo("<b>Round ".$count ."</b>:<br> ".$pdata['name'].' Dies...<br>');
			}else{
				$pHp = $pHp - $eAtk;
			     echo("<b>Round ".$count ."</b>:<br> ".$pdata['name'].' Deals '.$pAtk.' damage. <br> '.print_enemy($enemy).' Deals '.$eAtk.' damage.<br>');
			}
			$eDmg = $eDmg + $eAtk; //Total damage delt by enemy.
			
			$count = $count + 1;
		}while($pHp > 0 && $eHp > 0);
		

		//Who won?
		if ($pHp > $eHp){ //The player wins
			echo($pdata['name']." is victorious!<br>");
			//fight until hp = 0, fatal blow deals damage to player.
			//determine silver
			$silver = $edata['level'];
			$silverRoll = rand(0,100);
			if($silverRoll == 100){
				//JACKPOT
				$silver = $silver + ((100*$edata['level'])*20);
				echo("Jackpot! ".$silver." Obtained!<br>");
			}else{
				$silver = $silver + (10*($edata['level'])+(ceil(0.2*$silverRoll)));
			}
			echo($eDmg.' Damage taken.');
			changeSilver($pid,$silver);
			changeHp($pid,-$eDmg);
			giveExp($pid, $expdrop);
			loot_drop($enemy,$pid);
			changeEnergy($pid,-5);
		}elseif($pHp < $eHp){ //The player loses
			echo(print_enemy($enemy).' wins the battle.  You are defeated.');
			changeHp($pid, -$eDmg);
			changeEnergy($pid,-10);
			//gain nothing
		}elseif($pHp == $eHp){ //Its a draw
			echo('The '.print_enemy($enemy).' drops '.$silver.' silver, its the last waking thing you see.');
			changeHp($pid, -$eDmg);
			//gain nothing
			
		}
	}else{
		echo('You need to rest up a little before you can fight..');
	}
	
}

function getAttackLogs($pid){
	$logs = mysqli_query($GLOBALS['link'],"SELECT * FROM attacklog WHERE attacker='$pid' OR defender ='$pid' ORDER BY `time` DESC");
	if(mysqli_num_rows($logs) != 0){
		return $logs;
	}else{
		return false;
	}
}

//------------------------
//Enemy Functions
//------------------------
function getEData($eid){ //get enemy data
	$edq = mysqli_query($GLOBALS['link'],"SELECT * FROM `enemies` WHERE eid='$eid'");
	$enemydata = mysqli_fetch_assoc($edq);
	return $enemydata;
}

function select_enemy($plevel, $dice = 0){ //Select enemy pool
	$rangestart = $plevel - 5;
	$rangeend = $plevel + 2;
	$rarity = null;
	if($dice == 0){
		$dice = rand(1,100);
		//echo($dice);
	}
	if($dice <= 100 && $dice >= 99){ //1% chance mythic will be included in enemy pool
		//mythic enemy	
		$rarity = 6;
	}elseif($dice <= 98 && $dice >= 96){ //2% chance legendary will be included in enemy pool
		//legendary enemy
		$rarity = 5;
	}elseif($dice <= 95 && $dice >= 90){ //5% chance epic will be included in enemy pool
		//epic enemy
		$rarity = 4;
	}elseif($dice <= 98 && $dice >= 91){ //7% chance rare will be included in enemy pool
		//rare enemy
		$rarity = 3;
	}elseif($dice <= 90 && $dice >= 80){ //10% chance uncommon will be included in enemy pool
		//uncommon enemy
		$rarity = 2;
	}else{ //enemy pool only common
		//common enemy
		$rarity = 1;
	}
	$enemyq = mysqli_query($GLOBALS['link'],"SELECT `eid` FROM enemies WHERE `level` >= '$rangestart' AND `level` <= '$rangeend' AND `rarity` <= '$rarity' ORDER BY RAND();")or die(mysqli_error($GLOBALS['link']));
	
	if(mysqli_num_rows($enemyq) == 0){
		echo('Your hunt was unsuccessful.  There were no creatures to be found.');
		return false;
	}else{
		$enemypool = mysqli_fetch_assoc($enemyq);
		$selected_enemy = $enemypool['eid'];
		return $selected_enemy;
	}
	
}
function viewLoot($eid){
	$eData = getEData($eid);
	$loot_pool = select_loot($eData['level'],$eData['rarity'],1000);
	foreach($loot_pool as $loot){
		echo(print_item($loot['id']).'<br>');
	}
}
function select_loot($level, $rarity, $number){ //returns a pool of loot to be selected from $level as range, $rarity as max.
	$botlevel = $level -2;
	$toplevel = $level +2;
	$loot = mysqli_query($GLOBALS['link'],"SELECT * FROM items WHERE levelreq >= '$botlevel' AND levelreq <= '$toplevel' AND rarity <= '$rarity' OR id=8 ORDER BY RAND() LIMIT $number") or die(mysqli_error($GLOBALS['link']));
	return $loot;
}
function loot_drop($eid,$pid = 0,$guarenteed = 0){
	if($pid == 0){
		$spid = $_SESSION['pid'];
	}else{
		$spid = $pid;
	}
	//init variables
	$loot_drops = 0;
	//Parse guarenteed drops.
	 //Not a guarenteed drop
	if ($guarenteed == 1){
		//Drop is guarenteed.
	}else{
		//Random drop
		//Determine if loot drops.
		$x = rand(0,6); //1 in 3 chance of loot dropping at all.
		if($x == 6){ //LOOT HAS DROPPED

			//Determine how much loot drops (1-3)
			$y = rand(0,100);
				switch($y){
					case 100:
						$loot_drops = 3;
						break;
					case($y >= 90):
						$loot_drops = 2;
						break;
					default:
						$loot_drops = 1;
						break;
				}
				//Determine what loot drops (level of enemy roughly level of item)
				$eData = getEData($eid);
				$level = $eData['level'];
				$rarity = $eData['rarity'];
				$loot_pool = select_loot($level, $rarity, $loot_drops);
				
				
				foreach ($loot_pool as $loot){
					echo('<br>The enemy drops: '.print_item($loot['id']));
					giveItem($spid, $loot['id'], 1);	
				}
		}
	}	
}
function print_item($iid){
	$idata = getIData($iid);
	$itemName = $idata['name'];
	$itemRarity = $idata['rarity'];
	
	$ilvl = $idata['levelreq'];
	$slot = 'level';
	if($idata['slot'] == 1){
		$slot = 'atk';
	}elseif($idata['slot'] ==2 || $idata['slot'] == 3){
		$slot = 'def';
	}
	$rarityCol = null;
	$itemlink = null;
	$tooltip = null;
	$irarity = null;
	switch($idata['rarity']){
		case(0):
			$rarityCol = "gray";
			
		case(1):
			$rarityCol = "white";
			$irarity = 'Common<br>';
			break;
		case(2):
			$rarityCol = "green";
			$irarity = 'Uncommon<br>';
			break;
		case(3):
			$rarityCol = "blue";
			$irarity = 'Rare<br>';
			break;
		case(4):
			$rarityCol = "purple";
			$irarity = 'Epic<br>';
			break;
		case(5):
			$rarityCol = "orange";
			$irarity = 'Legendary<br>';
			break;
		case(6):
			$rarityCol = "darkred";
			$irarity = 'Mythic<br>';
			break;
		default:
			break;
	}
	if($idata['useaction'] == 1){
		$tooltip = "<font color='$rarityCol'>".$irarity."</font>+".$idata['atkup']." Atk<br>+".$idata['defup']." Def<br>+".$idata['hpup']." Hp";
	}elseif($iid == 8){
		$tooltip = "Restore 50 hitpoints.";
	}elseif($iid == 12){
		$tooltip = "Restore 50 energy.";
	}
	if(isset($_SESSION)){
		//$token = create_token('useitem');
		$plvl = getPdata($_SESSION['pid'],'hard')[$slot];
		if($ilvl > $plvl){
			if($slot == 'level'){$slot ='';}
			$tooltip .= "<br><font color='red'><i>Requires level $ilvl $slot.</i></font>";	
		}//elseif(hasItem($iid,$_SESSION['pid'])){
			//$tooltip .= "<br><a href='inventory.php?use=".$iid."&token=$token'>Equip</a>";
		//}
	}
	return "<div  class='tooltip'><font color='".$rarityCol."'>".$itemName."</font><span class='tooltiptext'>".$tooltip."</div>";

}

function print_enemy($iid){
	$idata = getEData($iid);
	$itemName = $idata['name'];
	$itemRarity = $idata['rarity'];
	
	$ilvl = $idata['level'];
	
	$rarityCol = null;
	$itemlink = null;
	$tooltip = null;
	$irarity = null;
	switch($idata['rarity']){
		case(0):
			$rarityCol = "gray";	
		case(1):
			$rarityCol = "white";
			$irarity = 'Common<br>';
			break;
		case(2):
			$rarityCol = "green";
			$irarity = 'Uncommon<br>';
			break;
		case(3):
			$rarityCol = "blue";
			$irarity = 'Rare<br>';
			break;
		case(4):
			$rarityCol = "purple";
			$irarity = 'Epic<br>';
			break;
		case(5):
			$rarityCol = "orange";
			$irarity = 'Legendary<br>';
			break;
		case(6):
			$rarityCol = "red' style='text-shadow: 1px 1px darkred, -2px -2px #000;";
			$irarity = 'Mythic<br>';
			break;
		default:
			break;
	}

		$tooltip = "<font color='$rarityCol'>".$irarity."</font> Level  $ilvl<br>".$idata['atk']." Atk<br>".$idata['def']." Def<br>".$idata['hp']." Hp";
		
	return "<div  class='tooltip'><font color='".$rarityCol."'>".$itemName."</font><span class='tooltiptext'>".$tooltip."</div>";

}

function timeago($ptime)
{
    $etime = time() - $ptime;

    //if(time() > $etime){
    //	return("0 seconds");
    //}

    $a = array( 365 * 24 * 60 * 60  =>  'year',
                 30 * 24 * 60 * 60  =>  'month',
                      24 * 60 * 60  =>  'day',
                           60 * 60  =>  'hour',
                                60  =>  'minute',
                                 1  =>  'second'
                );
    $a_plural = array( 'year'   => 'years',
                       'month'  => 'months',
                       'day'    => 'days',
                       'hour'   => 'hours',
                       'minute' => 'minutes',
                       'second' => 'seconds'
                );

    foreach ($a as $secs => $str)
    {
        $d = $etime / $secs;
        if ($d >= 1)
        {
            $r = round($d);
            return $r . ' ' . ($r > 1 ? $a_plural[$str] : $str);
        }
    }
}
function timeuntil($stime){
	$diff=$stime-time();
	if($diff < 3600){
		$temp = $diff/60;
		// minutes 
		$minutes=floor($temp);  $temp=60*($temp-$minutes); 
		// seconds 
		$seconds=floor($temp);
		return $minutes."m".$seconds."s"; 
	}else{
		// ireturnmmediately convert to days 
		$temp=$diff/86400; // 60 sec/min*60 min/hr*24 hr/day=86400 sec/day 
		// days 
		$days=floor($temp); $temp=24*($temp-$days); 
		// hours 
		$hours=floor($temp); $temp=60*($temp-$hours); 
		// minutes 
		$minutes=floor($temp); $temp=60*($temp-$minutes); 
		// seconds 
		$seconds=floor($temp); 

		if($days == 0){
			return $hours."h".$minutes."m".$seconds."s"; 
		}elseif($hours == 0 && $days == 0){
			return $minutes."m".$seconds."s"; 
		}elseif($hours == 0 && $days == 0 && $minutes == 0){
			return $seconds."s"; 
		}else{
			return $days."d".$hours."h".$minutes."m".$seconds."s"; 
		}
		
	}
}
?>
