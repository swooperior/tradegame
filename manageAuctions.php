<?php require('includes/header.php'); ?>
<h2>Your Listings</h2>

<?php

viewAuctions('pid ="'.$_SESSION['pid'].'" AND status < 3');

require ('includes/footer.php'); ?>