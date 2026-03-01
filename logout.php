<?php
session_start();
session_destroy();  // ends the session
header("Location: ../index.php");  // back to home
exit();
?>