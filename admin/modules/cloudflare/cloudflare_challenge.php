<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Challenge", "index.php?module=cloudflare-challenge");

$page->output_header("CloudFlare Manager - Challenge");

function main_page()
{
	require_once(MYBB_ROOT . "admin/inc/class_form.php");

	$form = new Form("index.php?module=cloudflare-challenge&amp;action=add_ip", "post");
	$form_container = new FormContainer("Challenge an IP");
	$form_container->output_row("IP Address", "The IP address won't be able to access your site until they have completed the captcha successfully or you have removed them from the challenge list.", $form->generate_text_box('ip_address'));
	$form_container->output_row("Notes", "Any notes you would like to add", $form->generate_text_box('notes'));
	$form_container->end();
	$buttons[] = $form->generate_submit_button("Submit");
	$form->output_submit_wrapper($buttons);
	$form->end();
}

if ($mybb->input['action'] == "add_ip")
{
	$request = $cloudflare->challenge_ip($mybb->input['ip_address'], $mybb->input['notes']);

	if (isset($request['success']))
	{
		$page->output_success("<p><em>CloudFlare has successfully challenged {$mybb->input['ip_address']} on {$mybb->settings['cloudflare_domain']}.</em></p>");
	}
	else
	{
		$page->output_inline_error($request['errors']);
	}
}

main_page();

$page->output_footer();

?>
