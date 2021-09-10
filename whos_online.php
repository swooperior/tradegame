<?php
require('includes/header.php');
$allSessions = [];
$sessionNames = scandir(session_save_path());

foreach($sessionNames as $sessionName) {
    $sessionName = str_replace("sess_","",$sessionName);
    if(strpos($sessionName,".") === false) { //This skips temp files that aren't sessions
        session_id($sessionName);
        session_start();
        $allSessions[$sessionName] = $_SESSION;
        session_abort();
    }
}
print_r($allSessions);
require('includes/footer.php');
?>
