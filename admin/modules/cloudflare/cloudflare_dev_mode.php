<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Development Mode", "index.php?module=cloudflare-dev_mode");
$page->output_header("CloudFlare Manager - Development Mode");

function main_page($in_dev_mode, $time_remaining = 0)
{
	global $page;
	if($in_dev_mode)
	{
		$page->output_alert("CloudFlare is currently in development mode. This will expire in ". gmdate("H:i:s", $time_remaining));
	}

	$form = new Form('index.php?module=cloudflare-dev_mode&amp;action=change', 'post');
	$form_container = new FormContainer('Change development mode');
	$form_container->output_row('Development Mode',
		"This will bypass CloudFlare's accelerated cache and slow down your site, but is useful if you are making changes to cacheable content (like images, css, or JavaScript) and would like to see those changes right away.",
		$form->generate_on_off_radio('dev_mode', ($in_dev_mode ? 1 : 0))
	);
	$form_container->end();
	$buttons[] = $form->generate_submit_button('Submit');
	$form->output_submit_wrapper($buttons);
	$form->end();
}

if ($mybb->input['action'] == 'change')
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-dev_mode");
	}

	$new_setting = ($mybb->get_input('dev_mode') == '0' ? 'off' : 'on');
	$request = $cloudflare->dev_mode($new_setting);
	if ($request->success)
	{
		$page->output_success("Turned development mode {$new_setting}", 'success');
	}
	else
	{
		$page->output_error($request->errors[0]->message);
	}
}

$dev_request = $cloudflare->dev_mode();
$in_dev = ($dev_request->result->value == "off" ? false : true);
main_page($in_dev, $dev_request->result->time_remaining);
$page->output_footer();

?>
