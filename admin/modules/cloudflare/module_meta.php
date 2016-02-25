<?php


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


$zone_id = ($cache->read('cloudflare_zone_id') ? $cache->read('cloudflare_zone_id') : false);
require_once("class/cloudflare.php");
$cloudflare = new cloudflare($mybb, $zone_id);

function cloudflare_meta()
{
	global $mybb, $page, $plugins, $cache, $cloudflare;
	if($mybb->input['module'] == 'cloudflare')
	{
		if(cloudflare_is_installed() == false)
		{
			flash_message('CloudFlare Manager hasn\'t been installed. Please install it before continuing.', 'error');
			admin_redirect("index.php?module=config-plugins");
			exit;
		}
	}

	if (!$cache->read("cloudflare_zone_id"))
	{
		$cloudflare->get_cloudflare_zone_id();
	}

	$sub_menu = array();
	$sub_menu['10'] = array("id" => "overview", "title" => "CloudFlare Overview", "link" => "index.php?module=cloudflare-overview");
	$sub_menu['20'] = array("id" => "dev_mode", "title" => "Development Mode", "link" => "index.php?module=cloudflare-dev_mode");

	$sub_menu = $plugins->run_hooks("admin_cloudflare_menu", $sub_menu);

	$page->add_menu_item("CloudFlare Manager", "cloudflare", "index.php?module=cloudflare", 60, $sub_menu);

	return true;
}

function cloudflare_action_handler($action)
{
	global $page, $plugins, $cache, $cloudflare;

	$page->active_module = "cloudflare";

	$actions = array(
		'overview' => array('active' => 'overview', 'file' => 'cloudflare_overview.php'),
		'purge_cache' => array('active' => 'purge_cache', 'file' => 'cloudflare_purge_cache.php'),
		'dev_mode' => array('active' => 'dev_mode', 'file' => 'cloudflare_dev_mode.php'),
		'cache_lvl' => array('active' => 'cache_lvl', 'file' => 'cloudflare_cache_lvl.php'),
		'security_lvl' => array('active' => 'security_lvl', 'file' => 'cloudflare_security_lvl.php'),
		'blacklist' => array('active' => 'blacklist', 'file' => 'cloudflare_blacklist.php'),
		'whitelist' => array('active' => 'whitelist', 'file' => 'cloudflare_whitelist.php'),
		'about_plugin' => array('active' => 'about_plugin', 'file' => 'cloudflare_about_plugin.php'),
		'check_for_updates' => array('active' => 'check_for_updates', 'file' => 'cloudflare_check_for_updates.php'),
		'report_bug' => array('active' => 'report_bug', 'file' => 'cloudflare_report_bug.php'),
		'news' => array('active' => 'news', 'file' => 'cloudflare_news.php'),
		'networkmap' => array('active' => 'networkmap', 'file' => 'cloudflare_networkmap.php'),
		'dns_active' => array('active' => 'dns_active', 'file' => 'cloudflare_dns_active.php'),
		'dns_not_active' => array('active' => 'dns_not_active', 'file' => 'cloudflare_dns_not_active.php'),
		'challenge' => array('active' => 'challenge', 'file' => 'cloudflare_challenge.php'),
		'ipv46' => array('active' => 'ipv46', 'file' => 'cloudflare_ipv46.php'),
		'manage_firewall' => array('active' => 'manage_firewall', 'file' => 'cloudflare_manage_firewall.php')
	);

	$actions = $plugins->run_hooks("admin_cloudflare_action_handler", $actions);

	$sub_menu = array();
	$sub_menu['Access'] = array(
		10 => array("id" => "manage_firewall", "title" => "Manage Firewall", "link" => "index.php?module=cloudflare-manage_firewall"),
		20 => array("id" => "whitelist", "title" => "Whitelist", "link" => "index.php?module=cloudflare-whitelist"),
		30 => array("id" => "blacklist", "title" => "Blacklist", "link" => "index.php?module=cloudflare-blacklist"),
		40 => array("id" => "challenge", "title" => "Challenge", "link" => "index.php?module=cloudflare-challenge"),
		50 => array("id" => "ipv46", "title" => "IPv6 Support", "link" => "index.php?module=cloudflare-ipv46"),
	);

	$sub_menu['Cache'] = array (
		10 => array("id" => "cache_lvl", "title" => "Cache Level", "link" => "index.php?module=cloudflare-cache_lvl"),
		20 => array("id" => "purge_cache", "title" => "Purge Cache", "link" => "index.php?module=cloudflare-purge_cache"),
	);

	$sub_menu['About Plugin'] = array (
		10 => array("id" => "about_plugin", "title" => "About Plugin", "link" => "index.php?module=cloudflare-about_plugin"),
		20 => array("id" => "check_for_updates", "title" => "Check for Updates", "link" => "index.php?module=cloudflare-check_for_updates"),
		40 => array("id" => "report_bug", "title" => "Report Bug", "link" => "index.php?module=cloudflare-report_bug")
	);

	$sub_menu['Security'] = array (
		10 => array("id" => "security_lvl", "title" => "Security Level", "link" => "index.php?module=cloudflare-security_lvl"),
	);

	if(!isset($actions[$action]))
	{
		$page->active_action = "overview";
	}

	foreach($sub_menu as $title => $menu)
	{
		$sidebar = new SideBarItem($title);
		$sidebar->add_menu_items($menu, $actions[$action]['active']);
		$page->sidebar .= $sidebar->get_markup();
	}


	if (!$cache->read('cloudflare_zone_id'))
	{
		$zone_id = $cloudflare->get_cloudflare_zone_id();
		if (isset($zone_id['error']))
		{
			$page->active_action = "overview";
			return "cloudflare_overview.php";
		}
	}

	if(isset($actions[$action]))
	{
		$page->active_action = $actions[$action]['active'];
		return $actions[$action]['file'];
	}
	else
	{
		return "cloudflare_overview.php";
	}

}


function cloudflare_admin_permissions()
{
	global $plugins;

	$admin_permissions = array(
		"overview"		=> "Can manage CloudFlare overview?",
		"dev_mode"		=> "Can manage CloudFlare development mode?",
		"manage_firewall" => "Can manage the firewall?",
		"security_lvl"	=> "Can manage CloudFlare security level?",
		"blacklist"		=> "Can manage CloudFlare blacklist?",
		"whitelist"		=> "Can manage CloudFlare whitelist?",
		"challenge"		=> "Can manage CloudFlare challenge?",
		"ipv46"			=> "Can manage CloudFlare IPv46?",
		"cache_lvl"	=> "Can manage CloudFlare cache level?",
		"purge_cache"	=> "Can manage CloudFlare purge cache?",
		"report_bug"	=> "Can manage CloudFlare report bug?",
	);

	$admin_permissions = $plugins->run_hooks("admin_cloudflare_permissions", $admin_permissions);

	return array("name" => "CloudFlare Manager", "permissions" => $admin_permissions, "disporder" => 60);
}

?>
