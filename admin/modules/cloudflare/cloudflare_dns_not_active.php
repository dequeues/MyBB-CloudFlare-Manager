<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

flash_message("Your nameservers are not set correctly.", "error");

admin_redirect("index.php?module=cloudflare");


?>
