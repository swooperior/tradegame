<?php include('includes/header.php'); 
$token = create_token('select_enemy');
$htoken = create_token('hunt');
?>

<h1>Hunting</h1>
<form method="POST" name="select_enemy">
	<input type="submit" name="hunt" value="Hunt"/>
	<input type="hidden" name="token" value="<?php echo($token);?>" />
</form>
<?
$penergy = getPData($_SESSION['pid'])['energy'];
if(isset($_POST['hunt']) && $penergy >= 5){
	//if(check_token($_POST['token'],'select_enemy')){
	if(!isset($_POST['fight'])){
		$enemy = select_enemy(getPData($_SESSION['pid'])['level']);
		changeEnergy($_SESSION['pid'],-2);
	}else{
		$enemy = $_POST['enemy'];
	}
		//Searching for the creature costs energy!
		
		//Display the creature ready to be slain!  (or flee from creature)
		if($enemy != false && !isset($_POST['fight'])){
			echo(print_enemy($enemy));
			$enemy = getEData($enemy);
			echo("<img src='".$enemy['image']."' class='hunt-enemy'>");
			
			?>
			<form name="hunt" method="POST">
				<input type="hidden" name="htoken" value="<?php echo($htoken);?>">
				<input type="hidden" name="hunt" value="hunt">
				<?php if(!isset($_POST['fight']) && !isset($_POST['flee'])){
					?>
					<input type="submit" name="fight" value="Fight"/>
					<input type="hidden" name="enemy" value="<?php echo($enemy['eid']);?>" />
					<input type="submit" name="flee" value="Flee" />
				<?php }		
		}
		if(isset($_POST['fight'])){
			hunt($_SESSION['pid'],$_POST['enemy']);
		}elseif(isset($_POST['flee'])){
			header('Refresh: 0;');
		}
	//}
	


}elseif($penergy < 7){
	echo('You are very tired, you should rest!');
}
?>


<?php include('includes/footer.php') ?>
