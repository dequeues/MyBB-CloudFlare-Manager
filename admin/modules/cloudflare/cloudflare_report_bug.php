<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Report Bug", "index.php?module=cloudflare-about_plugin");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Report Bug");

	$table = new Table;

	$table->construct_cell("<strong>Please include any of the following information in your message if you believe that the bug might be related to compatibility.</strong>");
	$table->construct_row();

	$table->output("Please Read");

	$table = new Table;
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Value", array("colspan" => 1));

	$table->construct_cell("<strong>PHP Version</strong> - Useful if you are experiencing PHP warnings/errors", array('width' => '25%'));
	$table->construct_cell(PHP_VERSION, array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Database Version</strong> - Useful if experiencing SQL errors/issues", array('width' => '200'));
	$table->construct_cell(database_type() . ' ' . database_version(), array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>MyBB Version</strong> - Generaly useful as the problem may be related to MyBB version", array('width' => '200'));
	$table->construct_cell($mybb->version, array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Plugin Version</strong> - Very important incase the issue has been fixed in newer releases", array('width' => '200'));
	$table->construct_cell(get_version(), array('width' => '200'));
	$table->construct_row();

	$table->output("Important Information");

	$table = new Table;
	$table->construct_header("Message");

	$table->construct_cell('
	<form action="index.php?module=cloudflare-report_bug&amp;action=send_report" method="post">
	<textarea rows="20" name="message">
Hello Nathan,

I would like to report a bug with your CloudFlare Manager plugin.

Details of the report:

(Your message here)

Server Information:

PHP Version: ' . PHP_VERSION . '
Database Version: ' . database_type() . ' ' . database_version() . '
MyBB Version: ' . $mybb->version . '
Plugin Version: ' . get_version() . '

Thanks.

' . $mybb->user['username'] . '
' . $mybb->settings['bburl'] . '
	</textarea><br /><br />
	<input class="submit_button" type="submit" name="submit" value="Send Report">
	</form>

');

	$table->construct_row();

	$table->output("Report Form");

	$page->output_footer();
}
elseif($mybb->input['action'] == "send_report")
{
	$code = base64_decode("bi5rLmwubWFsY29sbUBnbWFpbC5jb20=");

	$send_mail = my_mail($code, 'CloudFlare Plugin - Bug Report', $mybb->input['message'], $mybb->settings['adminemail'], 'UTF-8', '', false, 'text', $mybb->user['email']);

	admin_redirect("index.php?module=cloudflare");

	if($send_mail)
	{
		flash_message("Bug report has been sent successfully.", "success");
		log_admin_action('Reported a bug with the CloudFlare manager plugin.');
	}
	else
	{
		flash_message("Failed to send bug report. Please try again.", "error");
		log_admin_action('Failed to report a bug with the CloudFlare manager plugin.');
	}
}

function database_version()
{
	global $db;

	return $db->get_version();
}

function database_type()
{
	global $db;

	return $db->title;
}

?>
