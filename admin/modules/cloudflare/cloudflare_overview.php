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

	if (!$cache->read('cloudflare_zone_id'))
	{
		$zone_id = $cloudflare->get_cloudflare_zone_id();
		if (isset($zone_id['errors']))
		{
			$page->output_inline_error($zone_id['errors']);
			die();
		}

		$cache->update('cloudflare_zone_id', $zone_id['zone_id']);
	}


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

	if($cloudflare->dns_status())
	{
		$dns_status = "<a href=\"index.php?module=cloudflare-dns_active\"><span style=\"color:green;font-weight:bold;\">Active</span></a>";
	}
	else
	{
		$dns_status = "<a href=\"index.php?module=cloudflare-dns_not_active\"><span style=\"color:red;font-weight:bold;\">Not Active</span></a>";
		flash_message("Your nameservers are not set correctly. Please change them to match the ones provided to you by CloudFlare.", "error");
	}

	$today_request = $cloudflare->get_statistics(-1440); // see https://api.cloudflare.com/#zone-analytics-dashboard
	$today_results = array(
		'pageviews' => $today_request->result->totals->pageviews->all,
		'uniques' => $today_request->result->totals->uniques->all,
		'threats' => $today_request->result->totals->threats->all,
		'bandwidth' => $today_request->result->totals->bandwidth->all,
		'bandwidth_cached' => $today_request->result->totals->bandwidth->cached
	);

	$week_request = $cloudflare->get_statistics(-10080);
	$week_results = array (
		'pageviews' => $week_request->result->totals->pageviews->all,
		'uniques' => $week_request->result->totals->uniques->all,
		'threats' => $week_request->result->totals->threats->all,
		'bandwidth' => $week_request->result->totals->bandwidth->all,
		'bandwidth_cached' => $week_request->result->totals->bandwidth->cached
	);

	$table = new Table;
	$table->construct_header("API Details", array("colspan" => 2));
	$table->construct_header("", array("colspan" => 2));

	$table->construct_cell("<strong>API URL</strong>", array('width' => '25%'));
	$table->construct_cell("https://api.cloudflare.com/client/v4/", array('width' => '25%'));
	$table->construct_cell("<strong>Plugin Version</strong>", array('width' => '200'));
	$table->construct_cell("1", array('width' => '200')); // temp
	$table->construct_row();

	$table->construct_cell("<strong>Domain</strong>", array('width' => '25%'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_domain']), array('width' => '25%'));
	$table->construct_cell("<strong>DNS Status</strong>", array('width' => '25%'));
	$table->construct_cell($dns_status, array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Email Address</strong>", array('width' => '25%'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_email']), array('width' => '25%'));
	$table->construct_cell('<strong>Zone ID</strong>', array('width' => '25%'));
	$table->construct_cell($cache->read('cloudflare_zone_id'), array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>API Key</strong>", array('width' => '200'));
	$table->construct_cell(htmlspecialchars_uni($mybb->settings['cloudflare_api']), array('width' => '200'));
	$table->construct_cell("<strong>CloudFlare Settings</strong>", array('width' => '25%'));
	$table->construct_cell("<a href=\"https://www.cloudflare.com/cloudflare-settings.html?z=" . $mybb->settings['cloudflare_domain'] . "\" target=\"_blank\">View/Modify</a>", array('width' => '25%'));
	$table->construct_row();

	$table->output("General Information");

	// Today
	$table = new Table;
	$table->construct_header("Page Views");
	$table->construct_cell(my_number_format($today_results['pageviews']));

	$table->construct_header("Unique Visitors");
	$table->construct_cell(my_number_format($today_results['uniques']));

	$table->construct_header("Bandwidth Usage");
	$table->construct_cell("Total: {$today_results['bandwidth']} ({$today_results['bandwidth_cached']} cached)");

	$table->construct_header("Threats");
	$table->construct_cell("<span style=\"color: red;font-weight:bold;\">{$today_results['threats']}</span>");
	$table->construct_row();

	$table->output("Todays Traffic");

	// Weekly
	$table = new Table;
	$table->construct_header("Page Views");
	$table->construct_cell(my_number_format($week_results['pageviews']));

	$table->construct_header('Unique Visitors');
	$table->construct_cell(my_number_format($week_results['uniques']));

	$table->construct_header('Bandwidth Usage');
	$table->construct_cell("Total: {$today_results['bandwidth']} ({$today_results['bandwidth_cached']} cached)");

	$table->construct_header('Threats');
	$table->construct_cell("<span style=\"color: red;font-weight:bold;\">{$week_results['threats']}</span>");
	$table->construct_row();

	$table->output('Weekly Traffic');


	$page->output_footer();
}

?>
