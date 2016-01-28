<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Statistics", "index.php?module=cloudflare-statistics");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Statistics");

    $year = objectToArray(cloudflare_statistics()->response);

	foreach($year['result'] as $n => $data)
	{
		$stats = $data[0];

		$pageviews = $stats['trafficBreakdown'];
		$pageviews = $pageviews['pageviews'];

		$unique = $stats['trafficBreakdown'];
		$unique = $unique['uniques'];

		$bandwidth_year = $stats['bandwidthServed'];
		$total_bandwidth_year = $bandwidth_year['user'];
		$total_bandwidth_year = (int)$total_bandwidth_year * 1024;

		$bandwidth_sent_year = $bandwidth_year['user'];
		$bandwidth_sent_year = $total_bandwidth_year - (int)$bandwidth_year['cloudflare'] * 1024;
		$saved_bandwidth_year = $total_bandwidth_year - $bandwidth_sent_year;

		$requests_year = $stats['requestsServed'];
		$total_requests_year = $requests_year['cloudflare'];
		$sent_requests_year = $requests_year['user'];
		$saved_requests_year = $requests_year['cloudflare'] - $requests_year['user'];
	}

	$month = objectToArray(cloudflare_statistics(20)->response);

	foreach($month['result'] as $n => $data)
	{
		$stats = $data[0];

		$bandwidth_month = $stats['bandwidthServed'];
		$total_bandwidth_month = $bandwidth_month['user'];
		$total_bandwidth_month = (int)$total_bandwidth_month * 1024;

		$bandwidth_sent_month = $bandwidth_month['user'];
		$bandwidth_sent_month = $total_bandwidth_month - (int)$bandwidth_month['cloudflare'] * 1024;
		$saved_bandwidth_month = $total_bandwidth_month - $bandwidth_sent_month;

		$requests_month = $stats['requestsServed'];
		$total_requests_month = $requests_month['cloudflare'];
		$sent_requests_month = $requests_month['user'];
		$saved_requests_month = $requests_month['cloudflare'] - $requests_month['user'];
	}

	$week = objectToArray(cloudflare_statistics(30)->response);

	foreach($week['result'] as $n => $data)
	{
		$stats = $data[0];

		$bandwidth_week = $stats['bandwidthServed'];
		$total_bandwidth_week = $bandwidth_week['user'];
		$total_bandwidth_week = (int)$total_bandwidth_week * 1024;

		$bandwidth_sent_week = $bandwidth_week['user'];
		$bandwidth_sent_week = $total_bandwidth_week - (int)$bandwidth_week['cloudflare'] * 1024;
		$saved_bandwidth_week = $total_bandwidth_week - $bandwidth_sent_week;

		$requests_week = $stats['requestsServed'];
		$total_requests_week = $requests_week['cloudflare'];
		$sent_requests_week = $requests_week['user'];
		$saved_requests_week =$requests_week['cloudflare'] - $requests_week['user'];
	}

	// Calculate the percentages for the page views data
	$pageviews_percent = $pageviews['regular'] + $pageviews['crawler'] + $pageviews['threat'];
	$pageviews_percent_regular = round(($pageviews['regular'] / $pageviews_percent) * 100);
	$pageviews_percent_crawler = round(($pageviews['crawler'] / $pageviews_percent) * 100);
	$pageviews_percent_threat = round(($pageviews['threat'] / $pageviews_percent) * 100);

	// Calculate the percentages for the unique visitors data
	$unique_percent = $unique['regular'] + $unique['crawler'] + $unique['threat'];
	$unique_percent_regular = round(($unique['regular'] / $unique_percent) * 100);
	$unique_percent_crawler = round(($unique['crawler'] / $unique_percent) * 100);
	$unique_percent_threat = round(($unique['threat'] / $unique_percent) * 100);

	// Calculate the percentages for the bandwidth usage
	// Let's clean up the data
	$bandwidth_percent_total_week = $total_bandwidth_week;
	$bandwidth_percent_week_sent = round(((float) get_friendly_size($bandwidth_sent_week / $bandwidth_percent_total_week)) * 100);
	$bandwidth_percent_week_saved = round(((float) get_friendly_size($saved_bandwidth_week / $bandwidth_percent_total_week)) * 100);

	$bandwidth_percent_total_month = $total_bandwidth_month;
	$bandwidth_percent_month_sent = round(((float) get_friendly_size($bandwidth_sent_month / $bandwidth_percent_total_month)) * 100);
	$bandwidth_percent_month_saved = round(((float) get_friendly_size($saved_bandwidth_month / $bandwidth_percent_total_month)) * 100);

	$bandwidth_percent_total_year = $total_bandwidth_year;
	$bandwidth_percent_year_sent = round(((float) get_friendly_size($bandwidth_sent_year / $bandwidth_percent_total_year)) * 100);
	$bandwidth_percent_year_saved = round(((float) get_friendly_size($saved_bandwidth_year / $bandwidth_percent_total_year)) * 100);

	// Calculate the percentages for the requests
	$requests_percent_total_week = $total_requests_week;
	$requests_percent_week_sent = round(($sent_requests_week / $requests_percent_total_week) * 100);
	$requests_percent_week_saved = round(($saved_requests_week / $requests_percent_total_week) * 100);

	$requests_percent_total_month = $total_requests_month;
	$requests_percent_month_sent = round(($sent_requests_month / $requests_percent_total_month) * 100);
	$requests_percent_month_saved = round(($saved_requests_month / $requests_percent_total_month) * 100);

	$requests_percent_total_year = $total_requests_year;
	$requests_percent_year_sent = round(($sent_requests_year / $requests_percent_total_year) * 100);
	$requests_percent_year_saved = round(($saved_requests_year / $requests_percent_total_year) * 100);

	$table = new Table;
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Regular", array("colspan" => 1));
	$table->construct_header("Spider", array("colspan" => 1));
	$table->construct_header("Threat", array("colspan" => 1));

	$table->construct_cell("<strong>Unique Visitors</strong>", array('width' => '25%'));
	$table->construct_cell(number_format($unique['regular'])." ({$unique_percent_regular}%)", array('width' => '25%'));
	$table->construct_cell(number_format($unique['crawler'])." ({$unique_percent_crawler}%)", array('width' => '200'));
	$table->construct_cell(number_format($unique['threat'])." ({$unique_percent_threat}%)", array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Page Views</strong>", array('width' => '25%'));
	$table->construct_cell(number_format($pageviews['regular'])." ({$pageviews_percent_regular}%)", array('width' => '25%'));
	$table->construct_cell(number_format($pageviews['crawler'])." ({$pageviews_percent_crawler}%)", array('width' => '200'));
	$table->construct_cell(number_format($pageviews['threat'])." ({$pageviews_percent_threat}%)", array('width' => '200'));
	$table->construct_row();

	$table->output("Traffic Statistics For The Last Year");

	$table = new Table;
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Week", array("colspan" => 1));
	$table->construct_header("Month", array("colspan" => 1));
	$table->construct_header("Year", array("colspan" => 1));

	$table->construct_cell("<strong>Total</strong>", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($total_bandwidth_week)." (100%)", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($total_bandwidth_month)." (100%)", array('width' => '200'));
	$table->construct_cell(get_friendly_size($total_bandwidth_year)." (100%)", array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Sent By CloudFlare</strong>", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($bandwidth_sent_week)." ({$bandwidth_percent_week_sent}%)", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($bandwidth_sent_month)." ({$bandwidth_percent_month_sent}%)", array('width' => '200'));
	$table->construct_cell(get_friendly_size($bandwidth_sent_year)." ({$bandwidth_percent_year_sent}%)", array('width' => '200'));
	$table->construct_row();

	$table->construct_cell("<strong>Saved By CloudFlare</strong>", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($saved_bandwidth_week)." ({$bandwidth_percent_week_saved}%)", array('width' => '25%'));
	$table->construct_cell(get_friendly_size($saved_bandwidth_month)." ({$bandwidth_percent_month_saved}%)", array('width' => '200'));
	$table->construct_cell(get_friendly_size($saved_bandwidth_year)." ({$bandwidth_percent_year_saved}%)", array('width' => '200'));
	$table->construct_row();

	$table->output("Bandwidth Usage");

	$table = new Table;
	$table->construct_header("Type", array("colspan" => 1));
	$table->construct_header("Week", array("colspan" => 1));
	$table->construct_header("Month", array("colspan" => 1));
	$table->construct_header("Year", array("colspan" => 1));

	$table->construct_cell("<strong>Total</strong>", array('width' => '25%'));
	$table->construct_cell(number_format($total_requests_week)." (100%)", array('width' => '25%'));
	$table->construct_cell(number_format($total_requests_month)." (100%)", array('width' => '25%'));
	$table->construct_cell(number_format($total_requests_year)." (100%)", array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Sent By CloudFlare</strong>", array('width' => '25%'));
	$table->construct_cell(number_format($sent_requests_week)." ({$requests_percent_week_sent}%)", array('width' => '25%'));
	$table->construct_cell(number_format($sent_requests_month)." ({$requests_percent_month_sent}%)", array('width' => '25%'));
	$table->construct_cell(number_format($sent_requests_year)." ({$requests_percent_year_sent}%)", array('width' => '25%'));
	$table->construct_row();

	$table->construct_cell("<strong>Saved By CloudFlare</strong>", array('width' => '25%'));
	$table->construct_cell(number_format($saved_requests_week)." ({$requests_percent_week_saved}%)", array('width' => '25%'));
	$table->construct_cell(number_format($saved_requests_month)." ({$requests_percent_month_saved}%)", array('width' => '25%'));
	$table->construct_cell(number_format($saved_requests_year)." ({$requests_percent_year_saved}%)", array('width' => '25%'));
	$table->construct_row();

	$table->output("Requests");

	$page->output_footer();
}

?>
