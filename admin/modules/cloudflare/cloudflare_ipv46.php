<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("IPv6 Support", "index.php?module=cloudflare-ipv46");
$page->output_header("CloudFlare Manager - IPv6 Support");

$ipv6_mode_enabled = ($cloudflare->ipv46_setting()->result->value == 'on' ? true : false);

function main_page($enabled)
{
	require_once(MYBB_ROOT . "admin/inc/class_form.php");

	$form = new Form('index.php?module=cloudflare-ipv46&amp;action=change', 'post');
	$form_container = new FormContainer("IPv6 Support");
	$form_container->output_row('IPv6 Support', 'Enable IPv6 support and gateway', $form->generate_yes_no_radio('enable_ipv6', ($enabled ? "1" : "0")));
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
		admin_redirect("index.php?module=cloudflare-ipv6");
	}

	$request = $cloudflare->ipv46_setting($mybb->get_input('enable_ipv6') == '0' ? 'off' : 'on');

	if ($request->success)
	{
		$page->output_success("<p><em>IPv6 subdomain support has been enabled for this domain.</em></p>");
	}
	else
	{
		$page->output_inline_error($request->errors);
	}
}

main_page($ipv6_mode_enabled);
$page->output_footer();

?>
