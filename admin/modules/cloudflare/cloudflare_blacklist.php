<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Black List", "index.php?module=cloudflare-blacklist");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Black List");

	$table = new Table;

	$table->construct_cell('
	<strong>Black list an ip address.</strong><br /><br />
	<form action="index.php?module=cloudflare-blacklist&amp;action=run" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
Black listing an IP address means that the computer will not be able to access your site unless you remove them from the list.<br /><br />

<span style="color:red;font-weight:bold;">Currently you can only black list a single ip address from this module. You cannot black list an ip range or country. To do that please visit your CloudFlare dashboard.</span>
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Black List">
	</form>
	');

	$table->construct_row();

	$table->output("Black List");

	$page->output_footer();
}
elseif($mybb->input['action'] == "run")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-blacklist");
	}

	$page->output_header("Black List");

	$request = $cloudflare->blacklist($mybb->input['address']);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare has successfully blacklisted " . htmlspecialchars_uni($mybb->input['address']) . " on " . $mybb->settings['cloudflare_domain'] . ".</em></p>");
		log_admin_action('Black listed '.htmlspecialchars_uni($mybb->input['address']).' on '.$mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare could not successfully blacklist " . htmlspecialchars_uni($mybb->input['address']) ." on " . $mybb->settings['cloudflare_domain'] . ".", "error");
		log_admin_action('Failed to black list '.htmlspecialchars_uni($mybb->input['address']).' on '.$mybb->settings['cloudflare_domain']);
	}

	$table = new Table;

	$table->construct_cell('
	<strong>Black list an ip address.</strong><br /><br />
	<form action="index.php?module=cloudflare-blacklist&amp;action=run" method="post">
	<input class="text_input" type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input type="text" name="address"><br /><br />
Black listing an IP address means that the computer will not be able to access your site unless you remove them from the list.<br /><br />

<span style="color:red;font-weight:bold;">Currently you can only black list a single ip address from this module. You cannot black list an ip range or country. To do that please visit your CloudFlare dashboard.</span>
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Black List">
	</form>
	');

	$table->construct_row();

	$table->output("Black List");

	$page->output_footer();
}

?>
