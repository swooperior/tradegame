<?php include('includes/functions.php');include('includes/dbconnect.php');
$items = (mysqli_query($GLOBALS['link'],"SELECT * FROM items;"));
foreach($items as $item){
	echo($item['id']. " " .$item['name']."<br>");
}
?>