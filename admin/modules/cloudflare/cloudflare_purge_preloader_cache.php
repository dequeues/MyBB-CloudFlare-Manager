<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Purge Preloader Cache", "index.php?module=cloudflare-purge_preloader_cache");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Purge Preloader Cache");

	echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-purge_preloader_cache&amp;action=purge">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will purge all resources cached by CloudFlare. This will include javascript, stylesheets and images. Press Yes to continue.<br> <small>Please note, there is no way to undo this operation. CloudFlare can take up to 3 hours to recache resources again.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

	$page->output_footer();

}
elseif($mybb->input['action'] == "purge")
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
		admin_redirect("index.php?module=cloudflare-purge_preloader_cache");
	}

	$page->output_header("CloudFlare Manager - Purge Preloader Cache");

	$request = $cloudflare->purge_preloader_cache(1);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare preloader cache has been purged of all resources.</em></p>");
		log_admin_action('Purged the CloudFlare preloader cache for '.$mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare preloader cache was not purged successfully.", "error");
		log_admin_action('Failed to purge the CloudFlare preloader cache for '.$mybb->settings['cloudflare_domain']);
	}

		echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-purge_preloader_cache&amp;action=purge">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will purge all resources cached by CloudFlare. This will include javascript, stylesheets and images. Press Yes to continue.<br> <small>Please note, there is no way to undo this operation. CloudFlare can take up to 3 hours to recache resources again.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

	$page->output_footer();

}

?>
