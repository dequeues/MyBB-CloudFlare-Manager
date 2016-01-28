<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Challenge", "index.php?module=cloudflare-challenge");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Challenge");

	$table = new Table;

	$table->construct_cell('
	<strong>Forces the person to fill out a captcha to confirm they are human.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&amp;action=challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address won\'t be able to access your site until they have completed the captcha successfully or you have removed them from the challenge list.<br /><br />

	<input  class="submit_button" type="submit" name="submit" value="Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Challenge IP Address");

	$table = new Table;

	$table->construct_cell('
	<strong>Removes the need for a person to fill out a captcha.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&action=remove_challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address will be able to access your site after you have submitted this form.<br /><br />

	<input class="submit_button" type="submit" name="submit" value="Remove Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Remove Challenge");

	$page->output_footer();
}
elseif($mybb->input['action'] == "challenge")
{
	$page->output_header("CloudFlare Manager - Challenge");

	$request = $cloudflare->challenge($mybb->input['address']);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare has successfully challenged " . htmlspecialchars_uni($mybb->input['address']) . " on " . $mybb->settings['cloudflare_domain'] . ".</em></p>");
		log_admin_action('Challenged ' . htmlspecialchars_uni($mybb->input['address']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare did not successfully challenge " . htmlspecialchars_uni($mybb->input['address']) . " on " . $mybb->settings['cloudflare_domain'] . ".", "error");
		log_admin_action('Failed to challenge ' . htmlspecialchars_uni($mybb->input['address']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}

	$table = new Table;

	$table->construct_cell('
	<strong>Forces the person to fill out a captcha to confirm they are human.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&amp;action=challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address won\'t be able to access your site until they have completed the captcha successfully or you have removed them from the challenge list.<br /><br />

	<input  class="submit_button" type="submit" name="submit" value="Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Challenge IP Address");

	$table = new Table;

	$table->construct_cell('
	<strong>Removes the need for a person to fill out a captcha.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&amp;action=remove_challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address will be able to access your site after you have submitted this form.<br /><br />

	<input class="submit_button" type="submit" name="submit" value="Remove Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Remove Challenge");

	$page->output_footer();
}
elseif($mybb->input['action'] == "remove_challenge")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-challenge");
	}

	$page->output_header("Challenge");

	$request = $cloudflare->remove_challenge($mybb->input['address']);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare has successfully removed challenge for " . htmlspecialchars_uni($mybb->input['address']) . " on " . $mybb->settings['cloudflare_domain'] . ".</em></p>");
		log_admin_action('Removed challenge for ' . htmlspecialchars_uni($mybb->input['address']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare did not successfully remove challenge for " . htmlspecialchars_uni($mybb->input['address']) . " on " . $mybb->settings['cloudflare_domain'] . ".", "error");
		log_admin_action('Failed to remove challenge for ' . htmlspecialchars_uni($mybb->input['address']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}

	$table = new Table;

	$table->construct_cell('
	<strong>Forces the person to fill out a captcha to confirm they are human.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&amp;action=challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address won\'t be able to access your site until they have completed the captcha successfully or you have removed them from the challenge list.<br /><br />

	<input class="submit_button" type="submit" name="submit" value="Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Challenge IP Address");

	$table = new Table;

	$table->construct_cell('
	<strong>Removes the need for a person to fill out a captcha.</strong><br /><br />
	<form action="index.php?module=cloudflare-challenge&amp;action=remove_challenge" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Address: <input class="text_input" type="text" name="address"><br /><br />
The IP address will be able to access your site after you have submitted this form.<br /><br />

	<input class="submit_button" type="submit" name="submit" value="Remove Challenge">
	</form>
	');

	$table->construct_row();

	$table->output("Remove Challenge");

	$page->output_footer();
}

?>
