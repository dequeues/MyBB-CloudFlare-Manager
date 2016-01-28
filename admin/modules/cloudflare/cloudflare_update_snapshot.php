<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Update Snapshot", "index.php?module=cloudflare-update_snapshot");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Update Snapshot");

	echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-update_snapshot&amp;action=update">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will update the thumbnail snapshot of your site that is used on the CloudFlare challenge page. Press Yes to continue.<br> <small>Please note, there is no way to undo this operation. CloudFlare may take up to one hour to update.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

	$page->output_footer();

}
elseif($mybb->input['action'] == "update")
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
		admin_redirect("index.php?module=cloudflare-update_snapshot");
	}

	$page->output_header("CloudFlare Manager - Update Snapshot");

	$request = $cloudflare->update_snapshot();

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare has successfully updated your website snapshot.</em></p>");
		log_admin_action('Updated the CloudFlare snapshot of '.$mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare could not sucesffully update your website snapshot.", "error");
		log_admin_action('Failed to update the CloudFlare snapshot of '.$mybb->settings['cloudflare_domain']);
	}

	echo '<div id="inner">
<form method="post" action="index.php?module=cloudflare-update_snapshot&amp;action=update">
<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
<div class="confirm_action">
<p>This process will update the thumbnail snapshot of your site that is used on the CloudFlare challenge page. Press Yes to continue.<br> <small>Please note, there is no way to undo this operation. CloudFlare may take up to one hour to update.</small></p>
<br>
<p class="buttons">
<input type="submit" class="submit_button button_yes" value="Yes"><input type="submit" name="no" class="submit_button button_no" value="No"></p>
</div>
</form></div>';

	$page->output_footer();
}

?>
