<? require_once('includes/header.php');require_once('includes/inventory_functions.php');


$numbots = 20;
$maxlvl = 10;
$email = rand(0,1000);
$botids = [];
for($i=0;$i <= $numbots;$i++){
	//Generate Name
	$name = ['Catalonia','Estoran','Jiovitus','Retor','RetroBoi','IcedUp','420blazed','forthewin','LIVE2WIN','PaulStanley','itried','dropBass','MoarViewsPls','Herewego','MagicMan','UniverseMan','Starfish'];
	$name = $name[rand(0,sizeof($name)-1)];
	$password = "c03e747a6afbbcbf8be7668acfebee5";
	//Randomize Level/stats
	$level = rand(1,10);
	$atk = rand(1,$level);
	$def = rand(1,$level);
	$maxhp = rand(100,$level+100);
	$silver = rand($level*10,$level*100);
	$joindate = time();


	if(mysqli_query($GLOBALS['link'],"INSERT INTO players(name,email,joindate,pass,level,atk,def,maxhp,silver) VALUES('$name','$email','$joindate','$password','$level','$atk','$def','$maxhp','$silver');")){
		echo($name." created <br>");
		$email++;
		$id = mysqli_fetch_array(mysqli_query($GLOBALS['link'],"SELECT pid FROM players ORDER BY joindate DESC;"));
		$id = $id[0];
		sleep(2);
		mysqli_query($GLOBALS['link'],"INSERT INTO `players-equipped`(id) VALUES('$id');")or die(mysqli_error($GLOBALS['link']));
		//Randomize inventory
		for($count = 0;$count < rand(1,5);$count++){
			$item = rand(0,15);
			giveItem($id,$item,1);
			use_item($item,$id);
			echo('\tItems given and used/equipped<br>');
		}
	}
	
}









require_once('includes/footer.php');
?>