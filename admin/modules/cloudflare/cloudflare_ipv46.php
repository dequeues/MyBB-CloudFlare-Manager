<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("IPv6 Support", "index.php?module=cloudflare-ipv46");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - IPv6 Support");

	$table = new Table;

	if(cloudflare_ipv46_setting() == '3')
	{
		$full = 'selected=selected';
		$safe = '';
		$off = '';
	}
	elseif(cloudflare_ipv46_setting() == '5')
	{
		$full = '';
		$safe = 'selected=selected';
		$off = '';
	}
	else
	{
		$on = '';
		$off = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Enable IPv6 support for the 1% of internet users who can\'t access your site.</strong><br /><br />
	<form action="index.php?module=cloudflare-ipv46&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="3"'.$full.'>Full</option>
  	<option name="5"'.$safe.'>Safe</option>
  	<option name="0"'.$off.'>Off</option>
</select><br /><br />
CloudFlare set out to solve the Internet\'s biggest challenges. One of the challenges a lot of people talk about, but few people are doing anything about, is the transition from IPv4 to IPv6. That changes today.<br /><br />

The IPv4 protocol was designed in the 1970s. It was built to accommodate about 4 billion devices connecting to the network. That seemed like a lot at the time, but the explosive growth of the Internet means we\'re closing in on that number. In order to grow, a new protocol was created: IPv6.<br /><br />

Unfortunately, the IPv4 and IPv6 networks are incompatible. Unless you have a gateway of some kind, if you\'re on one you can\'t visit websites on the other. And, even more unfortunately, the gateway solutions typically are hardware-based and cost tens of thousands of dollars per website to deploy. This means that most the world\'s websites are unavailable for the 1% of the Internet that is already using IPv6. And the percentage of users on IPv6 is only going to grow.<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>
	');

	$table->construct_row();

	$table->output("Enable IPv6 Support");

	$page->output_footer();
}
elseif($mybb->input['action'] == "change")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-ipv6");
	}

	$page->output_header("CloudFlare Manager - IPv6 Support");

	if($mybb->input['type'] == "Full")
	{
		$type = 3;
	}
	elseif($mybb->input['type'] == "Safe")
	{
		$type = 5;
	}
	elseif($mybb->input['type'] == "Off")
	{
		$type = 0;
	}
	else
	{
		$type = 0;
	}

	$request = $cloudflare->switch_ipv6($type);

	if($request == "success" && $type == 3)
	{
		$page->output_success("<p><em>IPv6 support has been enabled for this domain.</em></p>");
	}
	elseif($request == "success" && $type == 0)
	{
		$page->output_success("<p><em>IPv6 support has been disabled for this domain.</em></p>");
	}
	elseif($request == "error" && $type == 3)
	{
		flash_message("CloudFlare could not enable IPv6 support on this domain.", "error");
	}
	elseif($request == "error" && $type == 0)
	{
		flash_message("CloudFlare could not disable IPv6 support on this domain.", "error");
	}
	elseif($request == "success" && $type == 5)
	{
		$page->output_success("<p><em>IPv6 subdomain support has been enabled for this domain.</em></p>");
	}
	elseif($request == "error" && $type == 5)
	{
		flash_message("CloudFlare could not disable IPv6 subdomain support on this domain.", "error");
	}

	$table = new Table;

	if(cloudflare_ipv46_setting() == '3')
	{
		$full = 'selected=selected';
		$safe = '';
		$off = '';
	}
	elseif(cloudflare_ipv46_setting() == '5')
	{
		$full = '';
		$safe = 'selected=selected';
		$off = '';
	}
	else
	{
		$on = '';
		$off = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Enable IPv6 support for the 1% of internet users who can\'t access your site.</strong><br /><br />
	<form action="index.php?module=cloudflare-ipv46&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="3"'.$full.'>Full</option>
  	<option name="5"'.$safe.'>Safe</option>
  	<option name="0"'.$off.'>Off</option>
</select><br /><br />
CloudFlare set out to solve the Internet\'s biggest challenges. One of the challenges a lot of people talk about, but few people are doing anything about, is the transition from IPv4 to IPv6. That changes today.<br /><br />

The IPv4 protocol was designed in the 1970s. It was built to accommodate about 4 billion devices connecting to the network. That seemed like a lot at the time, but the explosive growth of the Internet means we\'re closing in on that number. In order to grow, a new protocol was created: IPv6.<br /><br />

Unfortunately, the IPv4 and IPv6 networks are incompatible. Unless you have a gateway of some kind, if you\'re on one you can\'t visit websites on the other. And, even more unfortunately, the gateway solutions typically are hardware-based and cost tens of thousands of dollars per website to deploy. This means that most the world\'s websites are unavailable for the 1% of the Internet that is already using IPv6. And the percentage of users on IPv6 is only going to grow.<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>
	');

	$table->construct_row();

	$table->output("Enable IPv6 Support");

	$page->output_footer();
}

?>
