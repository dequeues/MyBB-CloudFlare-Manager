<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(get_version() == get_latest_version())
{
	flash_message("Congratulations! You are using the latest version of this plugin (" . get_latest_version() . ").", "success");
}
else
{
	flash_message("You are not using the latest version of this plugin. You are using " . get_version() . ", and the latest version is " . get_latest_version() . ".", "error");
}

admin_redirect("index.php?module=cloudflare");

?>
