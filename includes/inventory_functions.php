<?///INVENTORY_FUNCTIONS
require_once('functions.php');


function can_use($itemID,$uID){
	//wep = atk
	//shield and armor = def 
	//pet and consumable base level
	$iData = getIData($itemID);
	$pData = getPData($uID, 'hard');
	$lvl = $pData['level'];
	$ilvl = $iData['levelreq'];
	$islot = $iData['slot'];
	if($islot == 1){
		$lvl = $pData['atk'];
	}elseif($islot == 2 || $islot == 3){
		$lvl = $pData['def'];
	}
	if($lvl >= $ilvl){
		return true;
	}else{
		return false;
	}
}

function use_item($itemID,$uID,$token){
	if(check_token($token,'useitem')){
	if(!can_use($itemID,$uID)){
		echo('You arent strong enough to use that item.');
	}else{
	if(hasItem($itemID,$uID)){
		$itemData = getIData($itemID);
		switch($itemData['useaction']){
			case(1): //Item is equippable
			$slot = convertSlot($itemData['slot']);
			if(checkEquipped($uID,$itemID) != NULL){
				unequip($uID,$itemID);
				}
					if(mysqli_query($GLOBALS['link'],"UPDATE `players-equipped` SET `$slot`='$itemID' WHERE `id`='$uID'")){
						removeItem($uID,$itemData['id'],1); //remove from inventory
						$pData = getPdata($uID);
						if($pData ['hp'] > $pData['maxhp']){
							changeHp($uID,$pData['maxhp']);
						}
						echo(print_item($itemID) .' Equipped.');
					}				
					break;
			case (2): //Item has a use effect.
				switch($itemData['id']){
					case (8): //Health Potion, Restore 50 hp.
						changeHp($uID,50);
						removeItem($uID, 8, 1);
						header('Refresh; 3');
						break;
					case (12): //Energy Potion, Restore 50 nrg.
						changeEnergy($uID,50);
						removeItem($uID, 12, 1);
						header('Refresh; 3');
						break;
					default:
						break;
				}
				break;
			default: //Item is equippable.
				//get slot and id
				//equip.
			break;
		}
	}
}
}
}
function convertSlot($slot){
	switch($slot){
		case(1): //wp
			$slot = 'weaponslot';
			break;
		case(2): //sh
			$slot = 'shieldslot';
			break;;
		case(3): //ar
			$slot = 'armorslot';
			break;
		case(4): //pet
			$slot = 'petslot';
			break;
		case(5): //use
			$slot = 'effectslot';
			break;
		default:
			$slot = null;
			break;
	}
	return $slot;
}
function checkEquipped($uid,$iid){ //checks the players equipment for the item to see if the player has it equipped currently.
	$idata = getIData($iid);
	$slot = convertSlot($idata['slot']);
	if($slot != null ){
		$equipped = mysqli_fetch_assoc(mysqli_query($GLOBALS['link'],"SELECT `$slot` FROM `players-equipped` WHERE id = '$uid'"));
		if($equipped  != NULL && $equipped != false){
			return $equipped[$slot]; //returns id of the item in the checked slot.
		}else{
			return null;
		}
	}else{
		return null;
	}
	
}
function unequip($uid,$iid){
	$iData = getIData($iid); 
	$cheq = checkEquipped($uid,$iid);
	$slot = convertSlot($iData['slot']);
		
	if(mysqli_query($GLOBALS['link'], "UPDATE `players-equipped` SET $slot=NULL WHERE id = '$uid'")){
			giveItem($uid,$cheq,1);
	}else{
		echo("Something went wrong...200" );
	}
}


?>
