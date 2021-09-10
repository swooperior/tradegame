<?php
include('includes/header.php');
?>
<h1>Rankings</h1>
<div id="rankings">
<?php
$rq = mysqli_query($GLOBALS['link'], "SELECT * FROM players ORDER BY rating DESC, silver DESC");
?>
<a href="attackLog.php">View Attack Log</a>
<table>
<tr><td>Rank</td><td>Rating</td><td>Name</td><td>Level</td><td>Defence</td><td>Silver</td></tr>
<?php
$count = 1;
while($row = mysqli_fetch_assoc($rq)){
	echo("<tr><div  class='tooltip'><td>$count</td><td>".$row['rating']."</td><td><a href='player.php?pid=".$row['pid']."'>".shield_check($row['pid'],0)." ".$row['name']."</a></td><td>".$row['level']."</td><td>".$row['def']."</td><td>".$row['silver']."</td></div></div></tr>");
	$count++;
}
?>
</table>
</div>

<?php
include('includes/footer.php');
?>
