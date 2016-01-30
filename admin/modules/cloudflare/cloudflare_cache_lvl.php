<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Cache Level", "index.php?module=cloudflare-cache_lvl");
$page->output_header("CloudFlare Manager - Cache Level");

function main_page($current_cache_level)
{
	$form = new Form('index.php?module=cloudflare-cache_lvl&amp;action=change', 'post');
	$form_container = new FormContainer('Modify Cache Level');
	$form_container->output_row('Cache Level',
		"Cache Level functions based off the setting level. The basic setting will cache most static resources (i.e., css, images, and JavaScript). The simplified setting will ignore the query string when delivering a cached resource. The aggressive setting will cache all static resources, including ones with a query string. ",
		$form->generate_select_box('cache_level',
			array(
				'basic' => 'Basic',
				'simplified' => 'Simplified',
				'aggressive' => 'Aggressive'
			),
			$current_cache_level
		)
	);
	$form_container->end();
	$buttons[] = $form->generate_submit_button('Submit');
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$errors = [];
if ($mybb->input['action'] == 'change')
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-cache_lvl");
	}

	$request = $cloudflare->cache_level($mybb->get_input('cache_level'));
	
	if ($request->success)
	{
		$page->output_success("Cache level is now as {$mybb->get_input('cache_level')}");
	}
	else
	{
		$errors[] = $request->errors[0]->message;
		$page->output_error($request->errors[0]->message);
	}

}

if (!isset($mybb->input['cache_level']) && empty($errors))
{
	$request = $cloudflare->cache_level();
	$current_cache_level = $request->result->value;
}
else
{
	$current_cache_level = $mybb->input['cache_level'];
}

$page->output_alert("The cache level is currently set to {$current_cache_level}");

main_page($current_cache_level);

$page->output_footer();

?>
