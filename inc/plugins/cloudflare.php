<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('pre_output_page', 'cloudflare_backlink');

define('CLOUDFLARE_MANAGER_VERSION', '2.2-stable');

function cloudflare_info()
{
	return array(
		'name'			=> 'CloudFlare Manager',
		'description'	=> 'An advanced plugin for managing CloudFlare from your forum\'s admin control panel.',
		'website'		=> 'https://github.com/dequeues/MyBB-CloudFlare-Manager',
		'author'		=> '</a>MyBB Security Group<br />Maintained by <a href="https://github.com/dequeues">Nathan (dequeues)</a>',
		'authorsite'	=> 'https://github.com/dequeues',
		'version'		=> CLOUDFLARE_MANAGER_VERSION,
		"compatibility" => '18*'
	);
}

function cloudflare_install()
{
	global $mybb, $db, $config;

	$setting_group = array(
		"name" => "cloudflare",
		"title" => "CloudFlare Manager",
		"description" => "Configures options for the CloudFlare Manager plugin.",
		"disporder" => "1",
	);

	$gid = $db->insert_query("settinggroups", $setting_group);
	$dispnum = 0;

	$parse = parse_url($mybb->settings['bburl']);
	$domain = $parse['host'];
	$domain = str_replace('www.', '', $domain);

	$settings = array(
		"cloudflare_domain" => array(
			"title"			=> "Domain",
			"description"	=> "The domain (of this forum) that is active under CloudFlare",
			"optionscode"	=> "text",
			"value"			=> $domain,
			"disporder"		=> ++$dispnum
		),
		"cloudflare_email" => array(
			"title"			=> "Email",
			"description"	=> "Your email address linked to your CloudFlare account",
			"optionscode"	=> "text",
			"value"			=> $mybb->user['email'],
			"disporder"		=> ++$dispnum
		),
		"cloudflare_api" => array(
			"title"			=> "API Key",
			"description"	=> "Your CloudFlare API key. You can get this key <a href=\"https://www.cloudflare.com/a/account/my-account\">here</a>",
			"optionscode"	=> "text",
			"value"			=> "",
			"disporder"		=> ++$dispnum
		),
		"cloudflare_showdns" => array(
			"title"			=> "Show DNS?",
			"description"	=> "Do you want to show the IP address host on Recent Visitors? *May slow down process if enabled.",
			"optionscode"	=> "yesno",
			"value"			=> "0",
			"disporder"		=> ++$dispnum
		),
		"cloudflare_backlink" => array(
			"title"			=> "Show \"Enhanced By CloudFlare\" message?",
			"description"	=> "Do you want to show the enchanced by CloudFlare message in your board footer? It helps to expand the CloudFlare network and speed up more websites on the internet.",
			"optionscode"	=> "yesno",
			"value"			=> "1",
			"disporder"		=> ++$dispnum
		)
	);


	foreach($settings as $name => $setting)
	{
		$setting['gid'] = $gid;
		$setting['name'] = $name;

		$db->insert_query("settings", $setting);
	}

	rebuild_settings();

	admin_redirect("index.php?module=config-settings&action=change&gid={$gid}");
}

function cloudflare_activate()
{
	global $db, $mybb;

	include MYBB_ROOT."/inc/adminfunctions_templates.php";

	$db->delete_query("templates", "title = 'cloudflare_postbit_spam'");

	find_replace_templatesets('footer', '#<!-- End powered by --><cfb>#', '<!-- End powered by -->');
	find_replace_templatesets('footer', '#<!-- End powered by -->#', '<!-- End powered by --><cfb>');

	rebuild_settings();
}

function cloudflare_deactivate()
{
	global $db, $mybb;

	include MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets('footer', '#<!-- End powered by --><cfb>#', '<!-- End powered by -->');

	$db->delete_query("templates", "title = 'cloudflare_postbit_spam'");

	rebuild_settings();
}

function cloudflare_is_installed()
{
    global $db;

	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."settinggroups WHERE name='cloudflare'");

	if($db->num_rows($query) == 0)
	{
		return false;
	}
	return true;
}

function cloudflare_uninstall()
{
	global $db;

	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='cloudflare'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name LIKE 'cloudflare_%'");

	$db->query("DELETE FROM ".TABLE_PREFIX."datacache WHERE title='cloudflare_calls'");

	rebuild_settings();
}

function cloudflare_backlink(&$page)
{
	global $mybb, $cfb;

	if($mybb->settings['cloudflare_backlink'] == 1)
	{
		$cfb = "Enhanced By <a href=\"http://www.cloudflare.com/\" target=\"_blank\">CloudFlare</a>.";
	}
	else
	{
		$cfb = "";
	}

	$page = str_replace("<cfb>", $cfb, $page);

	return $page;
}

?>
