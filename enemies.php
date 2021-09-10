<?php
require('includes/header.php');
$enemies = mysqli_query($GLOBALS['link'],"SELECT eid FROM enemies WHERE 1;");
foreach($enemies as $enemy){
	
	echo(print_enemy($enemy['eid']).'<br>');
}
require('includes/footer.php');
?>