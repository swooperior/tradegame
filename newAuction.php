<?php include'includes/header.php';
$pid = $_SESSION['pid'];
$inventory = mysqli_query($GLOBALS['link'],"SELECT * FROM `players-inventory` WHERE pid='$pid'");

if(isset($_POST['submit'])){
	postAuction($_SESSION['pid'], $_POST['item'], $_POST['quant'], $_POST['duration'], $_POST['bid'], $_POST['buy']);
}

?>
<h1>Post an Auction Listing</h1>
<form method="POST">
Select Item:<select name="item" required>
<?php foreach($inventory as $item){ echo("<option value='".$item['item']."'>".getIData($item['item'])['name']." (".$item['amount'].")</option>");} ?>
</select>
<input type="text" name="quant" value="1" required/><br>
<img src='includes/imgs/silver-icon.png' style='height:15px;'><input type="text" name="bid" placeholder="Starting Bid" required /><br>
<img src='includes/imgs/silver-icon.png' style='height:15px;'><input type="text" name="buy" placeholder="Buyout" /><br>
Duration:<select name="duration" required>
<option value="6">6 Hours</option>
<option value="12">12 Hours</option>
<option value="24">24 Hours</option>
</select><br>
<p><i>Note that the auctioneer charges you 5% sale value for listing an item.</i></p>
<input type="submit" name="submit" value="Post" />
</form>
<?
include'includes/footer.php'
?>
