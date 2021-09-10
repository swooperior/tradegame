<? include('includes/header.php'); 
if(isset($_SESSION['pid'])){
	?>
	<h1>Training</h1>
	<p>You can pay the sensei to train your stats.  The cost of this depends on your level!</p>
	<?
	$pData = getPData($_SESSION['pid'],'hard');
	
	if(isset($_POST['submit'])){
		trainStat($_SESSION['pid'],$_POST['submit']);
	}
	?>
	<form method="post">
	<table style="text-align:center;">
	<tr><td>Stat</td><td>Level</td><td>Training Cost</td><td></td></tr>
	<tr><td>Attack</td><td><?php echo($pData['atk']);?></td><td><img src='includes/imgs/silver-icon.png' style='height:15px;'><?php echo($cost = (100 * $pData['atk']));?></td><td><button name="submit" value="atk">Train</button></td></tr>
	<tr><td>Defence</td><td><?php echo($pData['def']);?></td><td><img src='includes/imgs/silver-icon.png' style='height:15px;'><?php echo($cost = (100 * $pData['def']));?></td><td><button name="submit" value="def">Train</button></td></tr>
	<tr><td>Max HP</td><td><?php echo($pData['maxhp']);?></td><td><img src='includes/imgs/silver-icon.png' style='height:15px;'><?php echo($cost = (500 * ($pData['maxhp'] - 99)));?></td><td><button name="submit" value="maxhp">Train</button></td></tr>
	<tr><td>Max Energy</td><td><?php echo($pData['maxenergy']);?></td><td><img src='includes/imgs/silver-icon.png' style='height:15px;'><?php echo($cost = (500 * ($pData['maxenergy'] - 99)));?></td><td><button name="submit" value="maxenergy">Train</button></td></tr>
	</table>
	</form>
<? 
}else{
	echo 'You must be logged in to view this page.';
}
include('includes/footer.php'); ?>
