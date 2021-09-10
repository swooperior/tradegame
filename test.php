<?php
include 'includes/header.php';
//giveItem($_SESSION['pid'],8,5);
//giveItem($_SESSION['pid'],12,5);

//giveShield($_SESSION['pid'],60);


$enemies = mysqli_query($GLOBALS['link'],"SELECT * FROM enemies ORDER BY level");
?>
<form method="post">
	<select name="enemy" value="<?php echo($_POST['enemy']);?>">
<?php
	foreach($enemies as $enemy){
		echo('<option value="'.$enemy['eid'].'">'.$enemy['name'].'('.$enemy['level'].')</option>');
	}
?>
</select>
<input type="submit" name="submit" value="Get Drops">
</form>
<p>
<?
if(isset($_POST['submit'])){
	echo('<h2>'.print_enemy($_POST['enemy']).'</h2>');
	viewLoot($_POST['enemy']);
}


include 'includes/footer.php';
?>
