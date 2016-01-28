<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Change Log", "index.php?module=cloudflare-change_log");

$page->output_header("CloudFlare Manager - Change Log For Version " . get_version());

$version = str_replace(" ", "", get_version());

$changelog = @file_get_contents("http://cf.mybbsecurity.net/changelog/" . $version);

if($changelog)
{
	echo nl2br($changelog);
}
else
{
	echo "Error: Cannot find change log for version " .  get_version() . ".";
}

$page->output_footer();

?>
