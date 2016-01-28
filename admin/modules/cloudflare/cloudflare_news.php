<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("News", "index.php?module=cloudflare-news");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - News");

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

	$page->output_nav_tabs($sub_tabs, 'news');

	$table = new Table;

	$table->construct_cell(get_feed());
	$table->construct_row();

	$table->output("Latest News From the <a href=\"http://blog.cloudflare.com/\" target=\"_blank\">CloudFlare Blog</a>");

	$page->output_footer();
}

function get_feed()
{
	$feed = @simplexml_load_file("http://blog.cloudflare.com/rss.xml");

	if($feed)
	{
		foreach($feed->channel->item as $entry)
		{
			return "<span style=\"font-size: 16px;\"><strong>{$entry->title} - {$entry->pubDate}</strong></span>" . "<br /><br />{$entry->description}<br /><br />";
		}
	}
	else
	{
		return "Error: Could not retrieve data.";
	}
}

?>
