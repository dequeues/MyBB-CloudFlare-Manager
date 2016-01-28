<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("About Plugin", "index.php?module=cloudflare-about_plugin");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - About Plugin");

	$table = new Table;
	$table->construct_header("Item", array("colspan" => 1));
	$table->construct_header("Value", array("colspan" => 1));

	$table->construct_cell("<strong>Author</strong>", array('width' => '25%'));
	$table->construct_cell("<strong><a href=\"http://community.mybb.com/user-27579.html\" target=\"_blank\">Nathan Malcolm</a></strong>", array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Release Date</strong>", array('width' => '200'));
	$table->construct_cell("December 20 2012", array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Compatibility</strong>", array('width' => '200'));
	$table->construct_cell("MyBB 1.6 Series", array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Version</strong>", array('width' => '200'));
	$table->construct_cell(get_version() . " (Latest: " . get_latest_version() . ")", array('width' => '200'));
	$table->construct_row();

	$table->output("About This Plugin");

	$page->output_footer();
}

?>
