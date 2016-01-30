<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Purge Cache", "index.php?module=cloudflare-purge_cache");
$page->output_header("CloudFlare Manager - Purge Cache");

function main_page()
{
	$form = new Form('index.php?module=cloudflare-purge_cache&amp;action=purge', 'post');
	$form_container = new FormContainer('Purge Cache');
	$form_container->output_row('Purge Cache',
		'Remove ALL files from CloudFlare\'s cache. This will include javascript, stylesheets and images. CloudFlare can take up to 3 hours to recache resources again<br /><b>Note: </b>This may have dramatic affects on your origin server load after performing this action.',
		$form->generate_yes_no_radio('purge_input', 0)
	);
	$form_container->end();
	$buttons[] = $form->generate_submit_button('Submit');
	$form->output_submit_wrapper($buttons);
	$form->end();
}

if($mybb->input['action'] == "purge")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-purge_cache");
	}

	if ($mybb->input['purge_input'] == "1")
	{
		$request = $cloudflare->purge_cache(true);
		if ($request->success)
		{
			$page->output_success('Cache has been purged');
		}
		else
		{
			$page->output_error($request->errors[0]->message);
		}
	}	
}

main_page();
$page->output_footer();
?>
