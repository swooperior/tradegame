<?php
require('includes/header.php');
$auction = mysqli_real_escape_string($GLOBALS['link'],$_GET['aid']);
$aData = getAData($auction);
$endtime = strtotime($aData['date']) + ($aData['dur']*3600);
$timenow = strtotime(time());
$ending = $timenow + $endtime;
if($aData['highbidder'] == 0){
	$highbidder = "No bids, minimum bid: <img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' />".$aData['bid'];
}else{
	$highbidder = 'High Bidder: ';
	$highbidder .= getPData($aData['highbidder'])['name'].', Bid: <img src="includes/imgs/silver-icon.png" style="height:14px;" alt="Silver" />'.$aData['bid'];
}
?>
<h1><?php echo(getIData($aData['iid'])['name']);?></h1>
<p><?php echo(print_item($aData['iid'])); ?></p>
<p>Seller: <?php echo(getPData($aData['pid'])['name']); ?></p>
<p><?php echo($highbidder); ?></p>
<p>Auction ending: <?php echo(timeuntil($ending)); ?></p>
<form id="bid" method="POST">
	<img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' /><input type="text" placeholder="<?php echo $aData['bid'];?>" id="bid" name="bid" size="9"/>
	<input type="hidden" value="<?php echo $auction;?>" id="aid" name="auction"/>
	<input type="submit" value="Place Bid" name="submit" id="submit"><br>
	<img src='includes/imgs/silver-icon.png' style='height:14px;' alt='Silver' /><input type="text" value="<?php echo $aData['buyout'];?>" name="buyout" size="9" disabled/>
	<input type="submit" value="Buyout" name="buynow" id="submit">
</form>
<?php
if(isset($_POST['submit']) && isset($_POST['bid'])){
	$bid = mysqli_real_escape_string($GLOBALS['link'],$_POST['bid']);
	$aid = $auction;
	bidAuction($aid,$bid);
	header('Refresh; 0');
}
if(isset($_POST['buynow'])){
	buyAuction($auction);
}
require('includes/footer.php');
?>
