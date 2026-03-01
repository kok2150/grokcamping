<?php
$password = 'test123';  // ← change this to your desired password
echo password_hash($password, PASSWORD_DEFAULT);
?>