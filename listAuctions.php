<?php include 'includes/header.php'; ?>
<h1>Auctions</h1>
<a href="newAuction.php">Post New Auction</a>
<?php
if(isset($_POST['view'])){
	$aid = $_POST['view'];
	header('Location:viewAuction.php?aid='.$aid);
}

?>
<div id="auctionhouse">
	<form method="POST">
<table>
	<tr>
	<td>Item</td> <td>#</td> <td>Seller</td> <td>Ending</td><td>Bid</td> <td>Buyout</td>
	</tr>
<?php viewAuctions('status < 3'); ?>

</table>
	</form>
</div>

<?php include 'includes/footer.php'; ?>
