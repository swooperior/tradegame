<?php 
function create_token($form){
	$_SESSION[$form.'_token'] = md5(uniqid(microtime(), true));
	return($_SESSION[$form.'_token']);
}
function check_token($token,$form){
	if($token == $_SESSION[$form.'_token']){
		return true;
	}else{
		return false;
	}
}
?>
