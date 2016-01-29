<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Black List", "index.php?module=cloudflare-blacklist");
$page->output_header("CloudFlare Manager - Blacklist");

function main_page()
{
	require_once(MYBB_ROOT . "admin/inc/class_form.php");

	$form = new Form("index.php?module=cloudflare-blacklist&amp;action=run", "post");
	$form_container = new FormContainer("Blacklist an IP");
	$form_container->output_row("IP Address", "The IP address you would like to blacklist<br /><b>Only a single IP is currently supported!</b>", $form->generate_text_box('ip_address'));
	$form_container->output_row("Notes", "Any notes you would like to add", $form->generate_text_box('notes'));
	$form_container->end();
	$buttons[] = $form->generate_submit_button("Submit");
	$form->output_submit_wrapper($buttons);
	$form->end();
}

if($mybb->input['action'] == "run")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-blacklist");
	}

	$request = $cloudflare->blacklist_ip($mybb->input['ip_address'], $mybb->input['notes']);

	if(isset($request['success']))
	{
		$page->output_success("<p><em>CloudFlare has successfully blacklisted {$mybb->input['ip_address']} on {$mybb->settings['cloudflare_domain']}.</em></p>");
	}
	else
	{
		$page->output_inline_error($request['errors']);
	}
}

main_page();

$page->output_footer();

?>
