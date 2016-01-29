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
	global $page, $plugins, $cache;

	$page->active_module = "cloudflare";

	$actions = array(
		'overview' => array('active' => 'overview', 'file' => 'cloudflare_overview.php'),
		'purge_cache' => array('active' => 'purge_cache', 'file' => 'cloudflare_purge_cache.php'),
		'purge_preloader_cache' => array('active' => 'purge_preloader_cache', 'file' => 'cloudflare_purge_preloader_cache.php'),
		'dev_mode' => array('active' => 'dev_mode', 'file' => 'cloudflare_dev_mode.php'),
		'cache_lvl' => array('active' => 'cache_lvl', 'file' => 'cloudflare_cache_lvl.php'),
		'security_lvl' => array('active' => 'security_lvl', 'file' => 'cloudflare_security_lvl.php'),
		'statistics' => array('active' => 'statistics', 'file' => 'cloudflare_statistics.php'),
		'recent_visitors' => array('active' => 'recent_visitors', 'file' => 'cloudflare_recent_visitors.php'),
		'blacklist' => array('active' => 'blacklist', 'file' => 'cloudflare_blacklist.php'),
		'whitelist' => array('active' => 'whitelist', 'file' => 'cloudflare_whitelist.php'),
		'help' => array('active' => 'help', 'file' => 'cloudflare_help.php'),
		'knowledge_base' => array('active' => 'knowledge_base', 'file' => 'cloudflare_knowledge_base.php'),
		'website' => array('active' => 'website', 'file' => 'cloudflare_website.php'),
		'about_plugin' => array('active' => 'about_plugin', 'file' => 'cloudflare_about_plugin.php'),
		'check_for_updates' => array('active' => 'check_for_updates', 'file' => 'cloudflare_check_for_updates.php'),
		'report_bug' => array('active' => 'report_bug', 'file' => 'cloudflare_report_bug.php'),
		'news' => array('active' => 'news', 'file' => 'cloudflare_news.php'),
		'networkmap' => array('active' => 'networkmap', 'file' => 'cloudflare_networkmap.php'),
		'dns_active' => array('active' => 'dns_active', 'file' => 'cloudflare_dns_active.php'),
		'dns_not_active' => array('active' => 'dns_not_active', 'file' => 'cloudflare_dns_not_active.php'),
		'challenge' => array('active' => 'challenge', 'file' => 'cloudflare_challenge.php'),
		'change_log' => array('active' => 'change_log', 'file' => 'cloudflare_change_log.php'),
		'recent_visitors_48' => array('active' => 'recent_visitors_48', 'file' => 'cloudflare_recent_visitors_48.php'),
		'update_snapshot' => array('active' => 'update_snapshot', 'file' => 'cloudflare_update_snapshot.php'),
		'ipv46' => array('active' => 'ipv46', 'file' => 'cloudflare_ipv46.php'),
		'topthreats' => array('active' => 'topthreats', 'file' => 'cloudflare_topthreats.php'),
		'whois' => array('active' => 'whois', 'file' => 'cloudflare_whois.php'),
	);

	$actions = $plugins->run_hooks("admin_cloudflare_action_handler", $actions);

	$sub_menu = array();
	$sub_menu['Access'] = array(
		10 => array("id" => "whitelist", "title" => "White List", "link" => "index.php?module=cloudflare-whitelist"),
		20 => array("id" => "blacklist", "title" => "Black List", "link" => "index.php?module=cloudflare-blacklist"),
		30 => array("id" => "challenge", "title" => "Challenge", "link" => "index.php?module=cloudflare-challenge"),
		40 => array("id" => "ipv46", "title" => "IPv6 Support", "link" => "index.php?module=cloudflare-ipv46"),
		50 => array("id" => "whois", "title" => "Whois Lookup", "link" => "index.php?module=cloudflare-whois")
	);

	$sub_menu['Security'] = array (
		10 => array("id" => "cache_lvl", "title" => "Cache Level", "link" => "index.php?module=cloudflare-cache_lvl"),
		20 => array("id" => "purge_cache", "title" => "Purge Cache", "link" => "index.php?module=cloudflare-purge_cache"),
		30 => array("id" => "purge_preloader_cache", "title" => "Purge Preloader Cache", "link" => "index.php?module=cloudflare-purge_preloader_cache")
	);

	$sub_menu['Data'] = array (
		10 => array("id" => "website", "title" => "Official Website", "link" => "index.php?module=cloudflare-website"),
		20 => array("id" => "help", "title" => "Help Page", "link" => "index.php?module=cloudflare-help"),
		30 => array("id" => "knowledge_base", "title" => "Knowledge Base", "link" => "index.php?module=cloudflare-knowledge_base")
	);

	$sub_menu['Cache'] = array (
		10 => array("id" => "about_plugin", "title" => "About Plugin", "link" => "index.php?module=cloudflare-about_plugin"),
		20 => array("id" => "check_for_updates", "title" => "Check for Updates", "link" => "index.php?module=cloudflare-check_for_updates"),
		30 => array("id" => "change_log", "title" => "Change Log", "link" => "index.php?module=cloudflare-change_log"),
		40 => array("id" => "report_bug", "title" => "Report Bug", "link" => "index.php?module=cloudflare-report_bug")
	);

	$sub_menu['CloudFlare Support'] = array (
		10 => array("id" => "statistics", "title" => "Statistics", "link" => "index.php?module=cloudflare-statistics"),
		20 => array("id" => "recent_visitors", "title" => "Recent Visitors", "link" => "index.php?module=cloudflare-recent_visitors"),
		30 => array("id" => "update_snapshot", "title" => "Update Snapshot", "link" => "index.php?module=cloudflare-update_snapshot")
	);

	$sub_menu['About Plugin'] = array (
		10 => array("id" => "security_lvl", "title" => "Security Level", "link" => "index.php?module=cloudflare-security_lvl"),
		20 => array("id" => "topthreats", "title" => "Top Threats", "link" => "index.php?module=cloudflare-topthreats")
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
		$zone_id = get_cloudflare_zone_id();
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

function cloudflare_threat_score($ip)
{
	global $mybb;

	$url = "https://www.cloudflare.com/api_json.html";

	$data = array(
		"a" => "ip_lkup",
		"u" => $mybb->settings['cloudflare_email'],
		"z" => $mybb->settings['cloudflare_domain'],
		"tkn" => $mybb->settings['cloudflare_api'],
		"ip" => $ip
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MyBB/CloudFlare-Plugin(ThreatScore)");
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$http_result = curl_exec($ch);
	$error = curl_error($ch);

	$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

	curl_close($ch);

	if($http_code != 200)
	{
		echo "Error: $error\n";
	}
	else
	{
		$json = json_decode($http_result);
		//die("<div id='debug'>" . print_r(objectToArray($json)) . "</div>");

		$data = objectToArray($json->response);

		if(!$data[$ip])
		{
			return 'None';
		}

		$replace = array('BAD:', 'CLEAN:', 'SE:');
		$with = array('', '', '');

		$result = str_replace($replace, $with, $data[$ip]);

		return $result;
   }
}

function cloudflare_dev_mode($zoneid, $trigger = false)
{
	global $mybb, $cloudflare;

	$endpoint = "zones/{$zoneid}/settings/development_mode";
	if (!$trigger)
	{
		$data = $cloudflare->request(
			array (
				'endpoint' => $endpoint
			)
		);

		return $data;
	}

	else
	{
		$data = $cloudflare->request(
			array (
				'endpoint' => $endpoint,
				'method' => 'PATCH',
				'patch_data' => array (
					'value' => 'on'
				)
			)
		);

		return $data;
	}
}

function cloudflare_security_level()
{
	global $mybb;

	$url = "https://www.cloudflare.com/api_json.html";

	$data = array(
		"a" => "stats",
		"email" => $mybb->settings['cloudflare_email'],
		"z" => $mybb->settings['cloudflare_domain'],
		"tkn" => $mybb->settings['cloudflare_api'],
		"interval" => 10,
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MyBB/CloudFlare-Plugin(SecurityLevel)");
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$http_result = curl_exec($ch);
	$error = curl_error($ch);

	$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

	curl_close($ch);

	if($http_code != 200)
	{
		echo "Error: $error\n";
	}
	else
	{
		$json = json_decode($http_result);
		//echo "<div id='debug'>" . print_r($json) . "</div>";

		//die(print_r($json));
		return objectToArray($json->response->result->objs[0]->userSecuritySetting);
   }
}

function cloudflare_ipv46_setting()
{
	global $mybb;

	$url = "https://www.cloudflare.com/api_json.html";

	$data = array(
		"a" => "stats",
		"email" => $mybb->settings['cloudflare_email'],
		"z" => $mybb->settings['cloudflare_domain'],
		"tkn" => $mybb->settings['cloudflare_api'],
		"interval" => 10,
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MyBB/CloudFlare-Plugin(IPv46Setting)");
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$http_result = curl_exec($ch);
	$error = curl_error($ch);

	$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

	curl_close($ch);

	if($http_code != 200)
	{
		echo "Error: $error\n";
	}
	else
	{
		$json = json_decode($http_result);
		//echo "<div id='debug'>" . print_r($json) . "</div>";

		return objectToArray($json->response->result->objs[0]->ipv46);
   }
}

function cloudflare_cache_lvl_setting()
{
	global $mybb;

	$url = "https://www.cloudflare.com/api_json.html";

	$data = array(
		"a" => "stats",
		"email" => $mybb->settings['cloudflare_email'],
		"z" => $mybb->settings['cloudflare_domain'],
		"tkn" => $mybb->settings['cloudflare_api'],
		"interval" => 10,
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MyBB/CloudFlare-Plugin(CacheLvlSetting)");
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$http_result = curl_exec($ch);
	$error = curl_error($ch);

	$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

	curl_close($ch);

	if($http_code != 200)
	{
		echo "Error: $error\n";
	}
	else
	{
		$json = json_decode($http_result);
		//echo "<div id='debug'>" . print_r($json) . "</div>";

		return objectToArray($json->response->result->objs[0]->cache_lvl);
   }
}

function cloudflare_admin_permissions()
{
	global $plugins;

	$admin_permissions = array(
		"overview"		=> "Can manage CloudFlare overview?",
		"dev_mode"		=> "Can manage CloudFlare development mode?",
		"security_lvl"	=> "Can manage CloudFlare security level?",
		"blacklist"		=> "Can manage CloudFlare blacklist?",
		"whitelist"		=> "Can manage CloudFlare whitelist?",
		"challenge"		=> "Can manage CloudFlare challenge?",
		"ipv46"			=> "Can manage CloudFlare IPv46?",
		"whois"			=> "Can manage CloudFlare Whois lookup?",
		"statistics"	=> "Can manage CloudFlare statistics?",
		"recent_visitors"	=> "Can manage CloudFlare recent visitors?",
		"statistics"	=> "Can manage CloudFlare statistics?",
		"outbound"	=> "Can manage CloudFlare outbound links?",
		"update_snapshot"	=> "Can manage CloudFlare update snapshot?",
		"topthreats"	=> "Can manage CloudFlare top threats?",
		"cache_lvl"	=> "Can manage CloudFlare cache level?",
		"purge_cache"	=> "Can manage CloudFlare purge cache?",
		"purge_preloader_cache"	=> "Can manage CloudFlare purge preloader cache?",
		"report_bug"	=> "Can manage CloudFlare report bug?",
	);

	$admin_permissions = $plugins->run_hooks("admin_cloudflare_permissions", $admin_permissions);

	return array("name" => "CloudFlare Manager", "permissions" => $admin_permissions, "disporder" => 60);
}

function threatscore2color($score)
{
	switch(true)
	{
	       case ($score > 49):
                        return '#CC0000';
                  break;
	       case ($score > 24):
                        return '#F3611B';
                  break;
	       case ($score > 9):
                        return '#AE5700';
                  break;
                   default:
                        return '';
                  break;
	}
}

function local_whois_available()
{
	$disabled = explode(', ', ini_get('disable_functions'));
	return !in_array('shell_exec', $disabled);
}

?>
