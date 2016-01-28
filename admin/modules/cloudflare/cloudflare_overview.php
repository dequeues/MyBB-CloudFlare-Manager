<?php


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("CloudFlare Overview", "index.php?module=cloudflare-overview");

if(!$mybb->input['action'])
{
	$plugins->run_hooks("admin_cloudflare_overview_start");

	$page->output_header("CloudFlare Manager - Overview");

	$sub_tabs['overview'] = array(
		'title' => "Overview",
		'link' => "index.php?module=cloudflare-overview",
		'description' => "A general overview and summary of statistics and updates."
	);

	$sub_tabs['news'] = array(
		'title' => "CloudFlare News",
		'link' => "index.php?module=cloudflare-news",
		'description' => "The latest news from the CloudFlare blog."
	);

	$page->output_nav_tabs($sub_tabs, 'overview');

	if(dns_status($mybb->settings['cloudflare_domain']) == true)
	{
		$dns_status = "<a href=\"index.php?module=cloudflare-dns_active\"><span style=\"color:green;font-weight:bold;\">Active</span></a>";
	}
	else
	{
		$dns_status = "<a href=\"index.php?module=cloudflare-dns_not_active\"><span style=\"color:red;font-weight:bold;\">Not Active</span></a>";
	}

	if($dns_status == "<a href=\"index.php?module=cloudflare-dns_not_active\"><span style=\"color:red;font-weight:bold;\">Not Active</span></a>")
	{
		flash_message("Your nameservers are not set correctly. Please change them to match the ones provided to you by CloudFlare.", "error");
	}


	$today = objectToArray(cloudflare_statistics(40)->response);

	foreach($today['result'] as $n => $data) {
		$stats = $data[0];

		$pageviews = $stats['trafficBreakdown'];
		$pageviews = $pageviews['pageviews'];

		$unique = $stats['trafficBreakdown'];
		$unique = $unique['uniques'];

		$threats = $unique['threat'];

		$unique = $unique['regular'] + $unique['crawler'] + $unique['threat'];
		$pageviews = $pageviews['regular'] + $pageviews['crawler'] + $pageviews['threat'];

		$bandwidth_today = $stats['bandwidthServed'];
		$total_bandwidth_today = $bandwidth_today['user'];
		$total_bandwidth_today = (int)$total_bandwidth_today * 1024;
		$total_bandwidth_today = get_friendly_size($total_bandwidth_today);

		$requests_today = $stats['requestsServed'];

		$total_requests_today = my_number_format($requests_today['cloudflare']);
		$sent_requests_today = my_number_format($requests_today['user']);
		$saved_requests_today = my_number_format($requests_today['cloudflare'] - $requests_today['user']);
	}

	$month = objectToArray(cloudflare_statistics(20)->response);

	foreach($month['result'] as $n => $data) {
		$stats = $data[0];

		$bandwidth_month = $stats['bandwidthServed'];
		$total_bandwidth_month = $bandwidth_month['user'];
		$total_bandwidth_month = (int)$total_bandwidth_month * 1024;

		$bandwidth_sent_month = $bandwidth_month['user'];
		$bandwidth_sent_month = $total_bandwidth_month - (int)$bandwidth_month['cloudflare'] * 1024;
		$saved_bandwidth_month = $total_bandwidth_month - $bandwidth_sent_month;
		//$bandwidth_percent_month_saved = round((4.4 / 98.5) * 100);

	}

		$bandwidth_percent_total_month = $total_bandwidth_month;
		$bandwidth_percent_month_saved = round(((float) get_friendly_size($saved_bandwidth_month / $bandwidth_percent_total_month)) * 100);

	echo '<div class="success" id="flash_message">CloudFlare has saved '.get_friendly_size($saved_bandwidth_month).' of bandwidth this month, '.$bandwidth_percent_month_saved.'% of your total bandwidth usage.</div>';

	$table = new Table;
	$table->construct_header("API Details", array("colspan" => 2));
	$table->construct_header("", array("colspan" => 2));

	$table->construct_cell("<strong>API URL</strong>", array('width' => '25%'));
	$table->construct_cell("https://www.cloudflare.com/api_json.html", array('width' => '25%'));
	$table->construct_cell("<strong>Plugin Version</strong>", array('width' => '200'));
	$table->construct_cell(get_version(), array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Domain</strong>", array('width' => '25%'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_domain']), array('width' => '25%'));
	$table->construct_cell("<strong>DNS Status</strong>", array('width' => '25%'));
	$table->construct_cell($dns_status, array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Email Address</strong>", array('width' => '25%'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_email']), array('width' => '25%'));
	$table->construct_cell("<strong>API Requests</strong>", array('width' => '25%'));
	$table->construct_cell(my_number_format($cloudflare->fetch_calls($cache)) . "/1,200 Left (<a href=\"index.php?module=cloudflare-check_calls&my_post_key={$mybb->post_code}\">Check</a>)", array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>API Key</strong>", array('width' => '200'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_api']), array('width' => '200'));
	$table->construct_cell("<strong>CloudFlare Settings</strong>", array('width' => '25%'));
	$table->construct_cell("<a href=\"https://www.cloudflare.com/cloudflare-settings.html?z=" . $mybb->settings['cloudflare_domain'] . "\" target=\"_blank\">View/Modify</a>", array('width' => '25%'));
	$table->construct_row();

	$table->output("General Information");

	$table = new Table;
	$table->construct_header("Page Views");
	$table->construct_cell(my_number_format($pageviews));

	$table->construct_header("Unique Visitors");
	$table->construct_cell(my_number_format($unique));

	$table->construct_header("Bandwidth Usage");
	$table->construct_cell($total_bandwidth_today);

	$table->construct_header("Threats");
	$table->construct_cell('<strong><span style="color: red;">'.my_number_format($threats).'</span></strong>');
	$table->construct_row();

	$table->output("Past Day's Traffic");

	$table->construct_header("Latest Tweet");
	$table->construct_cell(latest_news_twitter());
	$table->construct_row();

	$table->output("Latest From <a href=\"http://twitter.com/cloudflare\" target=\"_blank\">@CloudFlare</a>");

	$table->construct_header("Latest Tweet");
	$table->construct_cell(latest_status_twitter());
	$table->construct_row();

	$table->output("Latest From <a href=\"http://twitter.com/cloudflaresys\" target=\"_blank\">@CloudFlareSys</a>");

	$page->output_footer();
}

	function dns_status($domain)
	{
		$dns = dns_get_record($domain, DNS_NS);

		foreach($dns as $ns)
		{
			if(strpos($ns['target'], ".ns.cloudflare.com") == true)
			{
				return true;
			}
		}
		return false;
	}

	function latest_news_twitter()
	{
		$tweet = @simplexml_load_file("http://api.twitter.com/1/statuses/user_timeline/cloudflare.xml");

		if($tweet)
		{
			return preg_replace('/(^|\s)@([a-z0-9_]+)/i', '$1<a href="http://twitter.com/$2" target="_blank">@$2</a>', $tweet->status[0]->text) . ' (' . date('H:i, jS F', strtotime($tweet->status[0]->created_at)) . ')';
		}
		else
		{
			return "Error: Could not retrieve data.";
		}
	}

	function latest_status_twitter()
	{
		$tweet = @simplexml_load_file("http://api.twitter.com/1/statuses/user_timeline/cloudflaresys.xml");

		if($tweet)
		{
			return preg_replace('/(^|\s)@([a-z0-9_]+)/i', '$1<a href="http://twitter.com/$2" target="_blank">@$2</a>', $tweet->status[0]->text) . ' (' . date('H:i, jS F', strtotime($tweet->status[0]->created_at)) . ')';
		}
		else
		{
			return "Error: Could not retrieve data.";
		}
	}

?>
