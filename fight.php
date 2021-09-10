<?php
require_once('includes/header.php');
$eid = $_GET['eid'];
$pdata = getPData($_SESSION['pid']);
$edata = getPData($eid);
?><p>
<table class="fight">
	<tr>
		<td>
			<!--User's Area-->
			<?
			echo("<b>".$pdata['name']."</b><br>
			Level: ".$pdata['level']."<br>
			<img class='ico' src='includes/imgs/hp-icon.png' style='height:14px;' alt='hp'/> ".$pdata['hp']."<br>
			<img class='ico' src='includes/imgs/atk-icon.png' style='height:14px;' alt='Atk' /> ".$pdata['atk']."<br>
			<img class='ico' src='includes/imgs/def-icon.png' style='height:14px;' alt='Def' /> ".$pdata['def']."<br>
			<p>
			");
			?>
		</td>
		<td>
			VS<br>
			<form method="POST">
<input type="submit" value="Fight" name="fight" />
</form>
		</td>
		<td>
			<!--Defender's Area-->
			<?echo("<b>".$edata['name']."</b><br>
			Level: ".$edata['level']."<br>
			<img class='ico' src='includes/imgs/hp-icon.png' style='height:14px;' alt='hp'/> ".$edata['hp']."<br>
			<img class='ico' src='includes/imgs/atk-icon.png' style='height:14px;' alt='Atk' /> ".$edata['atk']."<br>
			<img class='ico' src='includes/imgs/def-icon.png' style='height:14px;' alt='Def' /> ".$edata['def']."<br>
			<p>
	");

			?>
		</td>
	</tr>
</table>
<center>
<?
if(isset($_POST['fight'])){
	Attack($_SESSION['pid'],$eid);
}
?>
</center>

<? require_once('includes/footer.php'); ?>