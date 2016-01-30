<?php


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Manage Firewall", "index.php?module=cloudflare-manage_firewall");
$page->output_header("CloudFlare Manager - Manage Firewall");

function main_page()
{
	global $cloudflare, $mybb;
	$request = $cloudflare->get_access_rules();
	$table = new Table;
	$table->construct_header("Mode");
	$table->construct_header("IP Address");
	$table->construct_header("Notes");
	$table->construct_header("Modify");

	foreach($request->result as $rule)
	{
		$table->construct_cell($rule->mode);
		$table->construct_cell($rule->configuration->value);
		$table->construct_cell($rule->notes);
		$table->construct_cell("<a href=\"index.php?module=cloudflare-manage_firewall&action=modify_rule_by_ip&ip={$rule->configuration->value}&my_post_key={$mybb->post_code}&current_mode={$rule->mode}&current_notes={$rule->notes}\">Modify</a>&nbsp;/&nbsp;<a href=\"index.php?module=cloudflare-manage_firewall&action=delete_rule_by_id&rule_id={$rule->id}&ip_address={$rule->configuration->value}&my_post_key={$mybb->post_code}\">Delete</a>");
		$table->construct_row();
	}

	$table->output("Firewall Rules");

}

if ($mybb->input['action'] == 'modify_rule_by_ip')
{
	if (isset($mybb->input['update_rule']))
	{
		if(!verify_post_check($mybb->input['my_post_key']))
		{
			flash_message($lang->invalid_post_verify_key2, 'error');
			admin_redirect("index.php?module=cloudflare-manage_firewall");
		}

		$request = $cloudflare->update_access_rule($mybb->get_input('mode'), $mybb->get_input('ip_address'), $mybb->get_input('notes'));

		if (!empty($request['success']))
		{
			flash_message("Updated the firewall rule with IP {$mybb->get_input('ip_address')}", "success");
			admin_redirect("index.php?module=cloudflare-manage_firewall");
		}
		else
		{
			flash_message($request['errors'], "error");
			admin_redirect("index.php?module=cloudflare-manage_firewall");
		}
	}

	$form = new Form('index.php?module=cloudflare-manage_firewall&amp;action=modify_rule_by_ip', 'post');
	$form_container = new FormContainer("Modify Firewall Rule");
	$form_container->output_row("IP Address", "The IP address you would like to whitelist", $form->generate_text_box('ip_address', $mybb->get_input('ip')));
	$form_container->output_row('Mode', '', $form->generate_select_box("mode", array("whitelist" => "Whitelist", "block" => "Blacklist", "challenge" => "Challenge"), $mybb->get_input('current_mode')));
	$form_container->output_row("Notes", "Any notes you would like to add", $form->generate_text_box('notes', $mybb->get_input('current_notes')));
	echo $form->generate_hidden_field('update_rule', 'update');
	$form_container->end();
	$buttons[] = $form->generate_submit_button("Submit");
	$form->output_submit_wrapper($buttons);
	$form->end();
}
elseif ($mybb->input['action'] == 'delete_rule_by_id')
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-manage_firewall");
	}

	$request = $cloudflare->delete_firewall_rule($mybb->get_input('rule_id'));

	if (!empty($request->success))
	{
		flash_message("Updated the firewall rule with IP {$mybb->get_input('ip_address')}", "success");
		admin_redirect("index.php?module=cloudflare-manage_firewall");
	}
	else
	{
		flash_message($request->errors[0]->message, "error");
		admin_redirect("index.php?module=cloudflare-manage_firewall");
	}
	
}
else
{
	main_page();
}

?>