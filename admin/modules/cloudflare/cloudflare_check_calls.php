<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

if(verify_post_check($mybb->input['my_post_key']))
{
	$cloudflare->update_calls($cache);

	flash_message("API Requests statistic updated successfully.", "success");
	log_admin_action('Updated the API Requests statistic for ' . htmlspecialchars_uni($mybb->settings['cloudflare_domain']));
}

admin_redirect("index.php?module=cloudflare");

?>
