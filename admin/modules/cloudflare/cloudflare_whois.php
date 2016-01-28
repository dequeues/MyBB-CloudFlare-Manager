<?php


// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Whois Lookup", "index.php?module=cloudflare-whois");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Whois Lookup");

	$table = new Table;

	$table->construct_cell('
	<strong>Lookup an IP address via whois.</strong><br /><br />
	<form action="index.php?module=cloudflare-whois&amp;action=lookup" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Server: <input class="text_input" type="text" name="server"><br />
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Lookup">
	</form>

	');

    $table->construct_row();

	$table->output("Whois Lookup");

	$page->output_footer();
}
elseif($mybb->input['action'] == "lookup")
{
	$page->output_header("CloudFlare Manager - Whois Lookup");

	if(!local_whois_available())
	{
		$content = @file_get_contents('http://cf.mybbsecurity.net/whois.php?server='.$mybb->input['server']);
	}
	else
	{
		$content = @shell_exec(escapeshellcmd('whois ' . $mybb->input['server']));
	}

	log_admin_action('Performed a whois lookup on '.htmlspecialchars_uni($mybb->input['server']));

	if(!$content)
	{
		$content = 'There was an error looking up the specified server.';
	}
	else
	{
		$content = nl2br(htmlspecialchars_uni($content));
	}

	echo $content;

	$page->output_footer();
}

?>
