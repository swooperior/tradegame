<?php //Inventory
require('includes/header.php');require_once('includes/inventory_functions.php');
?>
<h1>Inventory</h1>
<?
function displayInventory($pid){
	$token = create_token('useitem');
	$inventory = mysqli_query($GLOBALS['link'], "SELECT * FROM `players-inventory` WHERE pid='$pid'");
	echo('<form method="GET">');
	foreach($inventory as $item){
		$tmp = getIData($item['item']);
		echo("<a href='inventory.php?use=".$item['item']."&token=$token'>".print_item($tmp['id'])." x ".$item['amount']."</a><br>");

		//echo(print_item($tmp['id'])." x ".$item['amount']."<br>");
	}
	?>
	<?php
	echo('</form>');

}

if(isset($_GET['use'])){
	use_item($_GET['use'],$_SESSION['pid'],$_GET['token']);
}

displayInventory($_SESSION['pid']);


require('includes/footer.php');
?>
