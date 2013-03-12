<?php
/*
 * Logs out a user & redirects to home page
 * 
 */
session_destroy();
header("location: ".SOA_ROOT);
die();
?>
