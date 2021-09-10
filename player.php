<?php 
include 'includes/header.php';
require_once 'includes/inventory_functions.php';
echo('<h1>Player</h1>');
if(!isset($_GET['pid'])){
	$pid = $_SESSION['pid'];
}else{
	$pid = $_GET['pid'];
}
$pdata = getPData($pid);
$eqdata = mysqli_fetch_assoc(mysqli_query($GLOBALS['link'],"SELECT * FROM `players-equipped` WHERE id='$pid'"));
?><table style="width:100%;">
<tr>
	<td>
		<!--Player Stats-->
		<?php
echo("<b>".$pdata['name']."</b><br>
	Level: ".$pdata['level']."<br>
	<img class='ico' src='includes/imgs/hp-icon.png' style='height:14px;' alt='hp'/>".$pdata['hp']."/".$pdata['maxhp']."<br>
	<img class='ico' src='includes/imgs/energy-icon.png' style='height:14px;' alt='energy' /> ".$pdata['energy']."/".$pdata['maxenergy']."<br>
	<img class='ico' src='includes/imgs/atk-icon.png' style='height:14px;' alt='Atk' /> ".$pdata['atk']."<br>
	<img class='ico' src='includes/imgs/def-icon.png' style='height:14px;' alt='Def' /> ".$pdata['def']."<br>
	<p>
	");
?>
</td>
<td>
	<!--Equipment-->
	<ul>
		<li><?php if($eqdata['weaponslot'] != null){echo("<b>".print_item($eqdata['weaponslot'])."</b>");}else{echo('<i>Empty Weapon Slot</i>');}?>
		<li><?php if($eqdata['shieldslot'] != null){echo("<b>".print_item($eqdata['shieldslot'])."</b>");}else{echo('<i>Empty Shield Slot</i>');}?>
		<li><?php if($eqdata['armorslot'] != null){echo("<b>".print_item($eqdata['armorslot'])."</b>");}else{echo('<i>Empty Armor Slot</i>');}?>
		<li><?php if($eqdata['petslot'] != null){echo("<b>".print_item($eqdata['petslot'])."</b>");}else{echo('<i>Empty Pet Slot</i>');}?>
		<li><?php if($eqdata['effectslot'] != null){echo("<b>".print_item($eqdata['effectslot'])."</b>");}else{echo('<i>Empty Effect Slot</i>');}?>
</td>
</tr>
</table>
<?php
if($pid == $_SESSION['pid']){
	echo("<a href='inventory.php'>View inventory</a>");
}else{
	echo("<a href='fight.php?eid=".$pid."'>Attack This Player</a>");
}


include 'includes/footer.php';
?>
