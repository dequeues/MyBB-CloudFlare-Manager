<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Recent Visitors", "index.php?module=cloudflare-recent_visitors_48");

if(!$mybb->input['action'] && !$mybb->input['onlyshow'])
{
	$page->output_header("CloudFlare Manager - Recent Visitors");

	$sub_tabs['recent_24'] = array(
		'title' => "Last 24 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors",
		'description' => "Information about visitors in the last 24 hours."
	);

	$sub_tabs['recent_48'] = array(
		'title' => "Last 48 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors_48",
		'description' => "Information about visitors in the last 48 hours."
	);

	$page->output_nav_tabs($sub_tabs, 'recent_48');

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	if($mybb->settings['cloudflare_showdns'] == "1")
	{
		$table->construct_header("DNS", array("colspan" => 1));
	}

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("", "48")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

	$quantity = $count;
	$page = intval($mybb->input['page']);
	$perpage = 20;

	if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$profile_page = "index.php?module=cloudflare-recent_visitors_48";

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

   	foreach(array_slice($array['ips'], $start, $perpage) as $n => $data)
   	{
		if($data['classification'] == "regular")
		{
			$data['classification'] = "<span style=\"color:#52D017; font-weight:bold;\">Regular </span>";
		}
		elseif($data['classification'] == "crawler")
		{
			$data['classification'] = "<span style=\"color:#F433FF; font-weight:bold;\">Spider/Bot</span>";
		}
		elseif($data['classification'] == "threat")
		{
			$data['classification'] = "<span style=\"color:#FF0000; font-weight:bold;\">Threat </span>";
		}
		else
		{
			$data['classification'] = "<span style=\"color:#000000; font-weight:bold;\">Unknown</span> ";
		}

		if($session->ipaddress == $data['ip'] || $mybb->user['regip'] == $data['ip'] || $mybb->user['lastip'] == $data['ip'])
		{
			$isyou = "(You)";
		}
		else
		{
			$isyou = "";
		}

		if(isset($mybb->input['page']) && $mybb->input['page'] !== 1)
		{
			$num = ($mybb->input['page'] * 10);
		}
		else
		{
			$num = 0;
		}

		$i = ++$number + $num;

		$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
		$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&amp;server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> " . $isyou, array('width' => '25%'));
		$table->construct_cell($data['classification'], array('width' => '25%'));
		$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));

		if(function_exists('geoip_record_by_name'))
		{
			$ip_record = @geoip_record_by_name($data['ip']);
			if($ip_record)
			{
				$ipaddress_location = null;
				if($ip_record['city'])
				{
					$ipaddress_location .= htmlspecialchars_uni($ip_record['city']).$lang->comma.' ';
				}
				$ipaddress_location .= htmlspecialchars_uni($ip_record['country_name']);
				$table->construct_cell('<a href="https://maps.google.com/maps?q='.urlencode($ipaddress_location).'" target="_blank">'.$ipaddress_location.'</a>', array('width' => '25%'));
			}
			else {
				$table->construct_cell('N/A', array('width' => '25%'));
			}
		}

		if($mybb->settings['cloudflare_showdns'] == "1")
		{
			$table->construct_cell(gethostbyaddr($data['ip']), array('width' => '25%'));
		}

		$popup = new PopupMenu("rv_options_" . $number . $count, "Options");
		$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=Black List");
		$popup->add_item("White List", "index.php?module=cloudflare-whitelist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=White List");
		$controls = $popup->fetch();

		$table->construct_cell($controls, array('width' => '5%'));

		$table->construct_row();
	}

	$table->output("Recent Visitors Data - 48 Hours (" . my_number_format($count) . " Total)");

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

	$types = array(
		"all" => "Show All",
		"" => "----------",
		"regular" => "Regular",
		"bot" => "Spider/Bot",
		"threat" => "Threat",
	);

	$form = new Form("index.php?module=cloudflare-recent_visitors_48", "post");
	$form_container = new FormContainer("Filter Visitors");
	$form_container->output_row("Type:", "", $form->generate_select_box('onlyshow', $types, $mybb->input['onlyshow'], array('id' => 'onlyshow')), 'onlyshow');

	$form_container->end();
	$buttons[] = $form->generate_submit_button("Filter Visitors");
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}
elseif(!$mybb->input['action'] && $mybb->input['onlyshow'] == "regular")
{
	$page->output_header("CloudFlare Manager - Recent Visitors");

	$sub_tabs['recent_24'] = array(
		'title' => "Last 24 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors",
		'description' => "Information about visitors in the last 24 hours."
	);

	$sub_tabs['recent_48'] = array(
		'title' => "Last 48 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors_48",
		'description' => "Information about visitors in the last 48 hours."
	);

	$page->output_nav_tabs($sub_tabs, 'recent_48');

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	if($mybb->settings['cloudflare_showdns'] == "1")
	{
		$table->construct_header("DNS", array("colspan" => 1));
	}

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("r", "48")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

	$quantity = $count;
	$page = intval($mybb->input['page']);
	$perpage = 20;

	if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$profile_page = "index.php?module=cloudflare-recent_visitors_48&amp;onlyshow=regular";

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

   	foreach(array_slice($array['ips'], $start, $perpage) as $n => $data)
   	{
		if($data['classification'] == "regular")
		{
			$data['classification'] = "<span style=\"color:#52D017; font-weight:bold;\">Regular </span>";
		}
		elseif($data['classification'] == "crawler")
		{
			$data['classification'] = "<span style=\"color:#F433FF; font-weight:bold;\">Spider/Bot</span>";
		}
		elseif($data['classification'] == "threat")
		{
			$data['classification'] = "<span style=\"color:#FF0000; font-weight:bold;\">Threat </span>";
		}
		else
		{
			$data['classification'] = "<span style=\"color:#000000; font-weight:bold;\">Unknown</span> ";
		}

		if($session->ipaddress == $data['ip'] || $mybb->user['regip'] == $data['ip'] || $mybb->user['lastip'] == $data['ip'])
		{
			$isyou = "(You)";
		}
		else
		{
			$isyou = "";
		}

		if(isset($mybb->input['page']) && $mybb->input['page'] !== 1)
		{
			$num = ($mybb->input['page'] * 10);
		}
		else
		{
			$num = 0;
		}

		$i = ++$number + $num;

		$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
		$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&amp;server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> " . $isyou, array('width' => '25%'));
		$table->construct_cell($data['classification'], array('width' => '25%'));
		$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));

		if(function_exists('geoip_record_by_name'))
		{
			$ip_record = @geoip_record_by_name($data['ip']);
			if($ip_record)
			{
				$ipaddress_location = null;
				if($ip_record['city'])
				{
					$ipaddress_location .= htmlspecialchars_uni($ip_record['city']).$lang->comma.' ';
				}
				$ipaddress_location .= htmlspecialchars_uni($ip_record['country_name']);
				$table->construct_cell('<a href="https://maps.google.com/maps?q='.urlencode($ipaddress_location).'" target="_blank">'.$ipaddress_location.'</a>', array('width' => '25%'));
			}
			else {
				$table->construct_cell('N/A', array('width' => '25%'));
			}
		}

		if($mybb->settings['cloudflare_showdns'] == "1")
		{
			$table->construct_cell(gethostbyaddr($data['ip']), array('width' => '25%'));
		}

		$popup = new PopupMenu("rv_options_" . $number . $count, "Options");
		$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=Black List");
		$popup->add_item("White List", "index.php?module=cloudflare-whitelist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&submit=White List");
		$controls = $popup->fetch();

		$table->construct_cell($controls, array('width' => '5%'));

		$table->construct_row();
	}

	$table->output("Recent Visitors Data - 48 Hours (" . my_number_format($count) . " Total)");

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

	$types = array(
		"all" => "Show All",
		"" => "----------",
		"regular" => "Regular",
		"bot" => "Spider/Bot",
		"threat" => "Threat",
	);

	$form = new Form("index.php?module=cloudflare-recent_visitors_48", "post");
	$form_container = new FormContainer("Filter Visitors");
	$form_container->output_row("Type:", "", $form->generate_select_box('onlyshow', $types, $mybb->input['onlyshow'], array('id' => 'onlyshow')), 'onlyshow');

	$form_container->end();
	$buttons[] = $form->generate_submit_button("Filter Visitors");
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}
elseif(!$mybb->input['action'] && $mybb->input['onlyshow'] == "bot")
{
	$page->output_header("CloudFlare Manager - Recent Visitors");

	$sub_tabs['recent_24'] = array(
		'title' => "Last 24 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors",
		'description' => "Information about visitors in the last 24 hours."
	);

	$sub_tabs['recent_48'] = array(
		'title' => "Last 48 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors_48",
		'description' => "Information about visitors in the last 48 hours."
	);

	$page->output_nav_tabs($sub_tabs, 'recent_48');

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	if($mybb->settings['cloudflare_showdns'] == "1")
	{
		$table->construct_header("DNS", array("colspan" => 1));
	}

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("s", "48")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

	$quantity = $count;
	$page = intval($mybb->input['page']);
	$perpage = 20;

	if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$profile_page = "index.php?module=cloudflare-recent_visitors_48&amp;onlyshow=bot";

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

   	foreach(array_slice($array['ips'], $start, $perpage) as $n => $data)
   	{
		if($data['classification'] == "regular")
		{
			$data['classification'] = "<span style=\"color:#52D017; font-weight:bold;\">Regular</span>";
		}
		elseif($data['classification'] == "crawler")
		{
			$data['classification'] = "<span style=\"color:#F433FF; font-weight:bold;\">Spider/Bot</span>";
		}
		elseif($data['classification'] == "threat")
		{
			$data['classification'] = "<span style=\"color:#FF0000; font-weight:bold;\">Threat</span>";
		}
		else{
			$data['classification'] = "<span style=\"color:#000000; font-weight:bold;\">Unknown</span> ";
		}

		if($session->ipaddress == $data['ip'] || $mybb->user['regip'] == $data['ip'] || $mybb->user['lastip'] == $data['ip'])
		{
			$isyou = "(You)";
		}
		else
		{
			$isyou = "";
		}

		if(isset($mybb->input['page']) && $mybb->input['page'] !== 1)
		{
			$num = ($mybb->input['page'] * 10);
		}
		else
		{
			$num = 0;
		}

		$i = ++$number + $num;

		$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
		$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&amp;server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> " . $isyou, array('width' => '25%'));
		$table->construct_cell($data['classification'], array('width' => '25%'));
		$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));

		if(function_exists('geoip_record_by_name'))
		{
			$ip_record = @geoip_record_by_name($data['ip']);
			if($ip_record)
			{
				$ipaddress_location = null;
				if($ip_record['city'])
				{
					$ipaddress_location .= htmlspecialchars_uni($ip_record['city']).$lang->comma.' ';
				}
				$ipaddress_location .= htmlspecialchars_uni($ip_record['country_name']);
				$table->construct_cell('<a href="https://maps.google.com/maps?q='.urlencode($ipaddress_location).'" target="_blank">'.$ipaddress_location.'</a>', array('width' => '25%'));
			}
			else {
				$table->construct_cell('N/A', array('width' => '25%'));
			}
		}

		if($mybb->settings['cloudflare_showdns'] == "1")
		{
			$table->construct_cell(gethostbyaddr($data['ip']), array('width' => '25%'));
		}

		$popup = new PopupMenu("rv_options_" . $number . $count, "Options");
		$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=Black List");
		$popup->add_item("White List", "index.php?module=cloudflare-whitelist&action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=White List");
		$controls = $popup->fetch();

		$table->construct_cell($controls, array('width' => '5%'));

		$table->construct_row();
	}

	$table->output("Recent Visitors Data - 48 Hours (" . my_number_format($count) . " Total)");

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

	$types = array(
		"all" => "Show All",
		"" => "----------",
		"regular" => "Regular",
		"bot" => "Spider/Bot",
		"threat" => "Threat",
	);

	$form = new Form("index.php?module=cloudflare-recent_visitors_48", "post");
	$form_container = new FormContainer("Filter Visitors");
	$form_container->output_row("Type:", "", $form->generate_select_box('onlyshow', $types, $mybb->input['onlyshow'], array('id' => 'onlyshow')), 'onlyshow');

	$form_container->end();
	$buttons[] = $form->generate_submit_button("Filter Visitors");
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}
elseif(!$mybb->input['action'] && $mybb->input['onlyshow'] == "threat")
{
	$page->output_header("CloudFlare Manager - Recent Visitors");

	$sub_tabs['recent_24'] = array(
		'title' => "Last 24 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors",
		'description' => "Information about visitors in the last 24 hours."
	);

	$sub_tabs['recent_48'] = array(
		'title' => "Last 48 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors_48",
		'description' => "Information about visitors in the last 48 hours."
	);

	$page->output_nav_tabs($sub_tabs, 'recent_48');

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	if($mybb->settings['cloudflare_showdns'] == "1")
	{
		$table->construct_header("DNS", array("colspan" => 1));
	}

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("t", "48")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

	$quantity = $count;
	$page = intval($mybb->input['page']);
	$perpage = 20;

	if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$profile_page = "index.php?module=cloudflare-recent_visitors_48&amp;onlyshow=threat";

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

   	foreach(array_slice($array['ips'], $start, $perpage) as $n => $data)
   	{
		if($data['classification'] == "regular")
		{
			$data['classification'] = "<span style=\"color:#52D017; font-weight:bold;\">Regular</span>";
		}
		elseif($data['classification'] == "crawler")
		{
			$data['classification'] = "<span style=\"color:#F433FF; font-weight:bold;\">Spider/Bot</span>";
		}
		elseif($data['classification'] == "threat")
		{
			$data['classification'] = "<span style=\"color:#FF0000; font-weight:bold;\">Threat</span>";
		}
		else
		{
			$data['classification'] = "<span style=\"color:#000000; font-weight:bold;\">Unknown</span> ";
		}

		if($session->ipaddress == $data['ip'] || $mybb->user['regip'] == $data['ip'] || $mybb->user['lastip'] == $data['ip'])
		{
			$isyou = "(You)";
		}
		else
		{
			$isyou = "";
		}

		if(isset($mybb->input['page']) && $mybb->input['page'] !== 1)
		{
			$num = ($mybb->input['page'] * 10);
		}
		else
		{
			$num = 0;
		}

		$i = ++$number + $num;

		$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
		$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&amp;server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> " . $isyou, array('width' => '25%'));
		$table->construct_cell($data['classification'], array('width' => '25%'));
		$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));

		if(function_exists('geoip_record_by_name'))
		{
			$ip_record = @geoip_record_by_name($data['ip']);
			if($ip_record)
			{
				$ipaddress_location = null;
				if($ip_record['city'])
				{
					$ipaddress_location .= htmlspecialchars_uni($ip_record['city']).$lang->comma.' ';
				}
				$ipaddress_location .= htmlspecialchars_uni($ip_record['country_name']);
				$table->construct_cell('<a href="https://maps.google.com/maps?q='.urlencode($ipaddress_location).'" target="_blank">'.$ipaddress_location.'</a>', array('width' => '25%'));
			}
			else {
				$table->construct_cell('N/A', array('width' => '25%'));
			}
		}

		if($mybb->settings['cloudflare_showdns'] == "1")
		{
			$table->construct_cell(gethostbyaddr($data['ip']), array('width' => '25%'));
		}

		$popup = new PopupMenu("rv_options_" . $number . $count, "Options");
		$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=Black List");
		$popup->add_item("White List", "index.php?module=cloudflare-whitelist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=White List");
		$controls = $popup->fetch();

		$table->construct_cell($controls, array('width' => '5%'));

		$table->construct_row();
	}

	$table->output("Recent Visitors Data - 48 Hours (" . my_number_format($count) . " Total)");

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

	$types = array(
		"all" => "Show All",
		"" => "----------",
		"regular" => "Regular",
		"bot" => "Spider/Bot",
		"threat" => "Threat",
	);

	$form = new Form("index.php?module=cloudflare-recent_visitors_48", "post");
	$form_container = new FormContainer("Filter Visitors");
	$form_container->output_row("Type:", "", $form->generate_select_box('onlyshow', $types, $mybb->input['onlyshow'], array('id' => 'onlyshow')), 'onlyshow');

	$form_container->end();
	$buttons[] = $form->generate_submit_button("Filter Visitors");
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}
elseif(!$mybb->input['action'] && $mybb->input['onlyshow'] == "all")
{
	$page->output_header("CloudFlare Manager - Recent Visitors");

	$sub_tabs['recent_24'] = array(
		'title' => "Last 24 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors",
		'description' => "Information about visitors in the last 24 hours."
	);

	$sub_tabs['recent_48'] = array(
		'title' => "Last 48 Hours",
		'link' => "index.php?module=cloudflare-recent_visitors_48",
		'description' => "Information about visitors in the last 48 hours."
	);

	$page->output_nav_tabs($sub_tabs, 'recent_48');

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	if($mybb->settings['cloudflare_showdns'] == "1")
	{
		$table->construct_header("DNS", array("colspan" => 1));
	}

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("", "48")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

	$quantity = $count;
	$page = intval($mybb->input['page']);
	$perpage = 20;

	if($page > 0)
	{
		$start = ($page - 1) * $perpage;
		$pages = $quantity / $perpage;
		$pages = ceil($pages);
		if($page > $pages || $page <= 0)
		{
			$start = 0;
			$page = 1;
		}
	}
	else
	{
		$start = 0;
		$page = 1;
	}

	$profile_page = "index.php?module=cloudflare-recent_visitors_48&amp;onlyshow=all";

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

   	foreach(array_slice($array['ips'], $start, $perpage) as $n => $data)
   	{
		if($data['classification'] == "regular")
		{
			$data['classification'] = "<span style=\"color:#52D017; font-weight:bold;\">Regular</span>";
		}
		elseif($data['classification'] == "crawler")
		{
			$data['classification'] = "<span style=\"color:#F433FF; font-weight:bold;\">Spider/Bot</span>";
		}
		elseif($data['classification'] == "threat")
		{
			$data['classification'] = "<span style=\"color:#FF0000; font-weight:bold;\">Threat</span>";
		}
		else
		{
			$data['classification'] = "<span style=\"color:#000000; font-weight:bold;\">Unknown</span> ";
		}

		if($session->ipaddress == $data['ip'] || $mybb->user['regip'] == $data['ip'] || $mybb->user['lastip'] == $data['ip'])
		{
			$isyou = "(You)";
		}
		else
		{
			$isyou = "";
		}

		if(isset($mybb->input['page']) && $mybb->input['page'] !== 1)
		{
			$num = ($mybb->input['page'] * 10);
		}
		else
		{
			$num = 0;
		}

		$i = ++$number + $num;

		$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
		$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&amp;server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> " . $isyou, array('width' => '25%'));
		$table->construct_cell($data['classification'], array('width' => '25%'));
		$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));

		if(function_exists('geoip_record_by_name'))
		{
			$ip_record = @geoip_record_by_name($data['ip']);
			if($ip_record)
			{
				$ipaddress_location = null;
				if($ip_record['city'])
				{
					$ipaddress_location .= htmlspecialchars_uni($ip_record['city']).$lang->comma.' ';
				}
				$ipaddress_location .= htmlspecialchars_uni($ip_record['country_name']);
				$table->construct_cell('<a href="https://maps.google.com/maps?q='.urlencode($ipaddress_location).'" target="_blank">'.$ipaddress_location.'</a>', array('width' => '25%'));
			}
			else {
				$table->construct_cell('N/A', array('width' => '25%'));
			}
		}

		if($mybb->settings['cloudflare_showdns'] == "1")
		{
			$table->construct_cell(gethostbyaddr($data['ip']), array('width' => '25%'));
		}

		$popup = new PopupMenu("rv_options_" . $number . $count, "Options");
		$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=Black List");
		$popup->add_item("White List", "index.php?module=cloudflare-whitelist&amp;action=run&amp;my_post_key={$mybb->post_code}&amp;address=" . $data['ip'] . "&amp;submit=White List");
		$controls = $popup->fetch();

		$table->construct_cell($controls, array('width' => '5%'));

		$table->construct_row();
	}

	$table->output("Recent Visitors Data - 48 Hours (" . my_number_format($count) . " Total)");

	echo multipage($quantity, (int)$perpage, (int)$page, $profile_page);

	$types = array(
		"all" => "Show All",
		"" => "----------",
		"regular" => "Regular",
		"bot" => "Spider/Bot",
		"threat" => "Threat",
	);

	$form = new Form("index.php?module=cloudflare-recent_visitors_48", "post");
	$form_container = new FormContainer("Filter Visitors");
	$form_container->output_row("Type:", "", $form->generate_select_box('onlyshow', $types, $mybb->input['onlyshow'], array('id' => 'onlyshow')), 'onlyshow');

	$form_container->end();
	$buttons[] = $form->generate_submit_button("Filter Visitors");
	$form->output_submit_wrapper($buttons);
	$form->end();

	$page->output_footer();
}

?>
