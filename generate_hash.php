<?php
$admin_password = "lexybeeadmin2025";
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
echo $hashed_password;
?>
