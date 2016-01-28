<?php


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("White List", "index.php?module=cloudflare-whitelist");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - White List");

	$table = new Table;

	$table->construct_cell('
	<strong>White list an ip address.</strong><br /><br />
	<form action="index.php?module=cloudflare-whitelist&amp;action=run" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
White Listing an IP address means that the computer won\'t be denied access by CloudFlare if it has been falsely blocked by the network.<br /><br />

<span style="color:red;font-weight:bold;">Currently you can only white list a single ip address from this module. You cannot white list an ip range or country. To do that please visit your CloudFlare dashboard.</span>
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="White List">
	</form>

	');

	$table->construct_row();

	$table->output("White List");

	$page->output_footer();
}
elseif($mybb->input['action'] == "run")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-whitelist");
	}

	$page->output_header("CloudFlare Manager - White List");

	$request = $cloudflare->whitelist($mybb->input['address']);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare has successfully whitelisted {$mybb->input['address']} on {$mybb->settings['cloudflare_domain']}".".</em></p>");
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare could not successfully whitelist {$mybb->input['address']} on {$mybb->settings['cloudflare_domain']}".".", "error");
	}

	$table = new Table;

	$table->construct_cell('
	<strong>White list an ip address.</strong><br /><br />
	<form action="index.php?module=cloudflare-whitelist&amp;action=run" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
White Listing an IP address means that the computer won\'t be denied access by CloudFlare if it has been falsely blocked by the network.<br /><br />

<span style="color:red;font-weight:bold;">Currently you can only white list a single ip address from this module. You cannot white list an ip range or country. To do that please visit your CloudFlare dashboard.</span>
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="White List">
	</form>

	');

	$table->construct_row();

	$table->output("White List");

	$page->output_footer();
}

?>
