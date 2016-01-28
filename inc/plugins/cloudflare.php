<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook('pre_output_page', 'cloudflare_backlink');
$plugins->add_hook("postbit", "cloudflare_postbit");
$plugins->add_hook("moderation_start", "cloudflare_moderation_start");
$plugins->add_hook("get_ip", "cloudflare_fixip");


if(my_strpos($_SERVER['PHP_SELF'], 'showthread.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'cloudflare_postbit_spam';
}

function cloudflare_info()
{
	return array(
		'name'			=> 'CloudFlare Manager',
		'description'	=> 'An advanced plugin for managing CloudFlare from your forum\'s admin control panel.',
		'website'		=> 'http://www.mybbsecurity.net/',
		'author'		=> 'MyBB Security Group',
		'authorsite'	=> 'http://www.mybbsecurity.net/',
		'version'		=> '1.0-beta 3.1',
		"compatibility" => "16*"
	);
}

function cloudflare_install()
{
	global $mybb, $db, $cache, $config;

	$db->query("DROP TABLE IF EXISTS `".TABLE_PREFIX."cloudflare");

	@unlink(MYBB_ROOT.$config['admin_dir'].'/cloudflare_outbound.php');
	@unlink(MYBB_ROOT.$config['admin_dir'].'/cloudflare_neworkmap.php');

	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='cloudflare'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name LIKE 'cloudflare_%'");

	$cache->update("cloudflare_calls", 1200);

	$setting_group = array(
		"gid" => "NULL",
		"name" => "cloudflare",
		"title" => "CloudFlare Manager",
		"description" => "Configures options for the CloudFlare Manager plugin.",
		"disporder" => "1",
		"isdefault" => "0",
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
			"description"	=> "Your CloudFlare API key",
			"optionscode"	=> "text",
			"value"			=> 123456789,
			"disporder"		=> ++$dispnum
		),
		"cloudflare_postbit_spam" => array(
			"title"			=> "Allow moderators to report spam to CloudFlare?",
			"description"	=> "Enabling this will add a button to each post which will give the ability to report spam to CloudFlare. Note this will do nothing more than report the post as spam.",
			"optionscode"	=> "yesno",
			"value"			=> "1",
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

}

function cloudflare_activate()
{
	global $db, $mybb;

	include MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'cloudflare_spam\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'cloudflare_spam\']}')."#i", '', 0);

	$db->delete_query("templates", "title = 'cloudflare_postbit_spam'");

	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'cloudflare_spam\']}{$post[\'button_edit\']}');
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'cloudflare_spam\']}{$post[\'button_edit\']}');

	find_replace_templatesets('footer', '#<!-- End powered by --><cfb>#', '<!-- End powered by -->');
	find_replace_templatesets('footer', '#<!-- End powered by -->#', '<!-- End powered by --><cfb>');

	$insert_array = array(
		'title' => 'cloudflare_postbit_spam',
		'template' => $db->escape_string('<a href="{$mybb->settings[\'bburl\']}/moderation.php?action=cloudflare_report_spam&amp;pid={$post[\'pid\']}&amp;fid={$post[\'fid\']}&amp;my_post_key={$mybb->post_code}"><img src="{$theme[\'imglangdir\']}/postbit_cloudflare_spam.gif" alt="{$lang->spam}" /></a>'),
		'sid' => '-1',
		'version' => '',
		'dateline' => TIME_NOW
	);

	$db->insert_query("templates", $insert_array);

	change_admin_permission("cloudflare", "", 1);
}

function cloudflare_deactivate()
{
	global $db, $mybb;

	include MYBB_ROOT."/inc/adminfunctions_templates.php";

	find_replace_templatesets("postbit", "#".preg_quote('{$post[\'cloudflare_spam\']}')."#i", '', 0);
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'cloudflare_spam\']}')."#i", '', 0);

	find_replace_templatesets('footer', '#<!-- End powered by --><cfb>#', '<!-- End powered by -->');

	$db->delete_query("templates", "title = 'cloudflare_postbit_spam'");

	change_admin_permission("cloudflare", "", -1);
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

	$db->query("DROP TABLE IF EXISTS` ".TABLE_PREFIX."cloudflare");
	$db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='cloudflare'");
	$db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name LIKE 'cloudflare_%'");

	$db->query("DELETE FROM ".TABLE_PREFIX."datacache WHERE title='cloudflare_calls'");

	rebuild_settings();
}

function cloudflare_moderation_start()
{
	global $mybb, $db, $cache, $fid, $pid;

	if(!$mybb->settings['cloudflare_postbit_spam'] || $mybb->input['action'] != 'cloudflare_report_spam')
	{
		return;
	}

	if(!$mybb->input['pid'])
	{
		error($lang->error_invalidpost);
	}

	$pid = intval($mybb->input['pid']);

	if(!$mybb->input['fid'])
	{
		error($lang->error_invalidforum);
	}

	$fid = intval($mybb->input['fid']);

	if(!is_moderator($fid))
	{
		error_no_permission();
	}

	$query = $db->query("
		SELECT p.uid, p.username, u.email, p.message, p.ipaddress, p.tid
		FROM ".TABLE_PREFIX."posts p
		LEFT JOIN ".TABLE_PREFIX."users u ON (u.uid=p.uid)
		LEFT JOIN ".TABLE_PREFIX."forums f ON (f.fid=p.fid)
		WHERE p.pid = '{$pid}'
	");
	$post = $db->fetch_array($query);

	if(!$post)
	{
		error($lang->error_invalidpost);
	}

	if(!$mybb->input['my_post_key'])
	{
		error_no_permission();
	}

	verify_post_check($mybb->input['my_post_key']);

	$spammer = get_user($post['uid']);

	$data = array("a" => $spammer['username'],
                  "am" => $spammer['email'],
                  "ip" => $post['ipaddress'],
                  "con" => substr($post['message'], 0, 100));

	$data = urlencode(json_encode($data));

	cloudflare_report_spam($data);

	redirect(get_post_link($pid), "Spam successfully reported to CloudFlare. You may now ban the spammer.");
}

function cloudflare_postbit(&$post)
{
	global $templates, $mybb, $theme;

	if(!$mybb->settings['cloudflare_postbit_spam'] || !is_moderator($post['fid']))
	{
		return;
	}

	if(is_super_admin($post['uid']))
	{
		return;
	}

	eval("\$post['cloudflare_spam'] = \"".$templates->get("cloudflare_postbit_spam")."\";");
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

function cloudflare_report_spam($data)
{
	global $mybb;

	$url = "https://www.cloudflare.com/ajax/external-event.html";

	$data = array(
		"email" => $mybb->settings['cloudflare_email'],
		"evnt_t" => 'CF_USER_SPAM',
		"evnt_v" => $data,
		"t" => $mybb->settings['cloudflare_api'],
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	curl_setopt($ch, CURLOPT_USERAGENT, "MyBB/CloudFlare-Plugin(ReportSpam)");
	curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	$http_result = curl_exec($ch);
	$error = curl_error($ch);

	$http_code = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

	curl_close($ch);

	if($http_code != 200)
	{
		error($error);
	}
}

function cloudflare_fixip(&$ip)
{
        if(isset($_SERVER['HTTP_CF_CONNECTING_IP']))
        {
            $ip['ip'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
            return $ip['ip'];
        }
}

?>