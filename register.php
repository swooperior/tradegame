<?php include 'includes/header.php';

if(isset($_SESSION['pid'])){
	header('Location: index.php');
}else{
?>
<form id="register" method="POST" action="register.php">
<input type="text" name="name" placeholder="Username" /><br>
<input type="text" name="email" placeholder="Email" /><br>
<input type="password" name="password" placeholder="Password" /><br>
<input type="submit" name="submit" value="Submit" />
</form>
<?php
}
	if(isset($_POST['submit'])){
		$joindate = time();
		$email = $_POST['email'];
		$name = $_POST['name']; 
		$pass = md5($_POST['password']);
		registerPlayer($joindate, $email, $name, $pass);
	}
include('includes/footer.php');

?>
