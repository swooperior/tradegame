<html>
<head>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css" integrity="sha384-5sAR7xN1Nv6T6+dT2mhtzEpVJvfS3NScPQTrOxhwjIuvcA67KV2R5Jz6kr4abQsz" crossorigin="anonymous">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#000">
<link rel="stylesheet" type="text/css" href="/tradeGame/includes/style.css?ver=1.2">
</head>
<?php include 'dbconnect.php'; include 'functions.php';
error_reporting(0);
ini_set('display_errors', 0);
session_start();
if (isset($_SESSION['pid'])){
	$pdata = getPData($_SESSION['pid']);
	$percent = round(($pdata['exp'] / $pdata['reqxp']) * 100);
}
echo "<a href='index.php'><--</a> <br />"; //back button temp
?>
<body>
<div id="wrapper">
<div id="banner">
	tradeGame
</div>


<?php
//IF LOGGED IN VARIATOR ---------
if (isset($_SESSION['pid'])){
	echo($pdata['name']. " <a href='logout.php'>(x)</a> <br />");
?>
	<div id="statsarea";"> <?php echo("Level: ".$pdata['level']."(".$percent."%) <br> <img class='ico' src='includes/imgs/hp-icon.png' style='height:12px;'> ".$pdata['hp']."/".$pdata['maxhp']." <br> <img class='ico' src='includes/imgs/energy-icon.png' style='height:14px;'> ".$pdata['energy']."/".$pdata['maxenergy']) ?>
	<div id="wallet"><?php echo("<img class='ico' src='includes/imgs/silver-icon.png' style='height:15px;'> ".$pdata['silver'] ." | <img class='ico' src='includes/imgs/gold-icon.png' style='height:15px;'> ".$pdata['gold']) ?></div>
	</div>
<?php
} else{
	echo('<a href="register.php">Register</a> | <a href="login.php">Login</a> <br/>');
}
//---------------------------------
if(isset($_SESSION['pid'])){
?>
<div id="navigation">
	<br>
	<a href="player.php">Player</a>
	<br>
	<a href="hunt.php">Hunting</a>
	<br>
	<a href="auctionHouse.php">Auction House</a>
	<br>
	<a href="training.php">Training</a>
	<br>
	<a href="rankings.php">Blood Ranks</a>
	<br>
	<a href="raid.php">Raids</a>
	<br>
	<a href="test.php">Drop tables</a>
</div>
<?php } ?>
<div id="pagecontent">
