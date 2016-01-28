<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Development Mode", "index.php?module=cloudflare-dev_mode");

if(cloudflare_dev_mode() == 0)
{
	$dev_mode = "never";
}
elseif(cloudflare_dev_mode() > TIME_NOW)
{
	$dev_mode = "future";
}
elseif(cloudflare_dev_mode() < TIME_NOW)
{
	$dev_mode = "past";
}

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Development Mode");

if($dev_mode == "future")
{
	echo '<div id="inner">'.$mybb->settings['cloudflare_domain'].' is currently in development mode. This will expire at '.date("H:i", cloudflare_dev_mode()).'</div>';
}
elseif($dev_mode == "past" || $dev_mode == "never")
{
	echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-dev_mode&amp;action=change">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will put your site in Development Mode. This means that your website will slow down, but it gives you the chance to make changes to resources such as CSS and javascript. Press Yes to continue.<br> <small>Please note, this mode will expire in 3 hours time.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

}

	$page->output_footer();
}
elseif($mybb->input['action'] == "change")
{
	if($mybb->request_method == "post")
	{
		if($mybb->input['no'])
		{
			admin_redirect("index.php?module=cloudflare");
		}
	}

	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-dev_mode");
	}

	$page->output_header("CloudFlare Manager - Development Mode");

	$request = $cloudflare->dev_mode(1);

	if($request == "success")
	{
		$page->output_success("<p><em>" . $mybb->settings['cloudflare_domain'] . " is now running in Development Mode.</em></p>");
		log_admin_action('Put '.$mybb->settings['cloudflare_domain'].' under development mode');
	}
	elseif($request == "error")
	{
		flash_message($mybb->settings['cloudflare_domain'] . " failed to boot into Development Mode.", "error");
		log_admin_action('Failed to put '.$mybb->settings['cloudflare_domain'].' under development mode');
	}

	echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-dev_mode&amp;action=change">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will put your site in Development Mode. When Development Mode is on the cache is bypassed. This means that your website will slow down, but it gives you the chance to make changes to resources such as CSS and javascript. Press Yes to continue.<br> <small>Please note, this mode will expire in 3 hours time.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

	$page->output_footer();
}

?>
