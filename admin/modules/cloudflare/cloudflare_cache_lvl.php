<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Cache Level", "index.php?module=cloudflare-cache_lvl");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Cache Level");

	$table = new Table;

	if(cloudflare_cache_lvl_setting() == 'basic')
	{
		$basic = 'selected=selected';
		$simplified = '';
		$agg = '';
	}
	elseif(cloudflare_cache_lvl_setting() == 'iqs')
	{
		$basic = '';
		$simplified = 'selected=selected';
		$agg = '';
	}
	elseif(cloudflare_cache_lvl_setting() == 'agg')
	{
		$basic = '';
		$simplified = '';
		$agg = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Adjust your caching level to modify CloudFlare\'s caching behavior.</strong><br /><br />
	<form action="index.php?module=cloudflare-cache_lvl&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="agg"'.$agg.'>Aggressive</option>
  	<option name="simplified"'.$simplified.'>Simplified</option>
  	<option name="basic"'.$basic.'>Basic</option>
</select><br /><br />
The <strong>basic</strong> setting will cache most static resources (i.e., css, images, and JavaScript). The <strong>aggressive</strong> setting will cache all static resources, including ones with a query string.<br /><br />

<strong>Basic:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif<br /><br />
<strong>Simplified:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif<s>?ignore=this-query-string</s><br /><br />
<strong>Aggressive:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif?with=query
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>
	');

	$table->construct_row();

	$table->output("Change Cache Level");

	$page->output_footer();
}
elseif($mybb->input['action'] == "change")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-cache_lvl");
	}

	$page->output_header("CloudFlare Manager - Cache Level");

	if($mybb->input['type'] == "Basic")
	{
		$type = "basic";
	}
	elseif($mybb->input['type'] == "Aggressive")
	{
		$type = "agg";
	}
	elseif($mybb->input['type'] == "Simplified")
	{
		$type = "iqs";
	}
	else
	{
		$type = "basic";
	}

	$request = $cloudflare->cache_level($type);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare cache level has sucessfully been changed to  " . htmlspecialchars_uni($mybb->input['type']) . ".</em></p>");
		log_admin_action('Changed the cache level to  ' . htmlspecialchars_uni($mybb->input['type']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare cache level could not be changed to " . htmlspecialchars_uni($mybb->input['type']) . ".", "error");
		log_admin_action('Failed to change the cache level to  ' . htmlspecialchars_uni($mybb->input['type']) . ' on ' . $mybb->settings['cloudflare_domain']);
	}

	$table = new Table;

	if(cloudflare_cache_lvl_setting() == 'basic')
	{
		$basic = 'selected=selected';
		$simplified = '';
		$agg = '';
	}
	elseif(cloudflare_cache_lvl_setting() == 'iqs')
	{
		$basic = '';
		$simplified = 'selected=selected';
		$agg = '';
	}
	elseif(cloudflare_cache_lvl_setting() == 'agg')
	{
		$basic = '';
		$simplified = '';
		$agg = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Adjust your caching level to modify CloudFlare\'s caching behavior.</strong><br /><br />
	<form action="index.php?module=cloudflare-cache_lvl&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="agg"'.$agg.'>Aggressive</option>
  	<option name="simplified"'.$simplified.'>Simplified</option>
  	<option name="basic"'.$basic.'>Basic</option>
</select><br /><br />
The <strong>basic</strong> setting will cache most static resources (i.e., css, images, and JavaScript). The <strong>aggressive</strong> setting will cache all static resources, including ones with a query string.<br /><br />

<strong>Basic:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif<br /><br />
<strong>Simplified:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif<s>?ignore=this-query-string</s><br /><br />
<strong>Aggressive:</strong> http://' . $mybb->settings['cloudflare_domain'] . '/images/logo.gif?with=query
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>

	');

	$table->construct_row();

	$table->output("Change Cache Level");

	$page->output_footer();
}

?>
