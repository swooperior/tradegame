<?php
include 'includes/header.php';
//Login script
if(isset($_POST['login'])){
	//$token = create_token('login');
	echo("login set.");
	$name = $_POST['name'];
	$pass = md5($_POST['pass']);
	echo("Login attempt...");
	loginPlayer($name, $pass, $_POST['token']);
	header('Location: index.php');
}


?>

<form id="loginForm" method="POST" action="login.php">
<input type="text" placeholder="Username" name="name" required /><br>
<input type="password" placeholder="Password" name="pass" required />
<input type="Submit" name="login" value="Login" />
<input type="hidden" value="<?php echo(create_token('login')); ?>" name="token" />
</form>

<?php
include 'includes/footer.php';
?>
