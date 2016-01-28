<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Top Threats", "index.php?module=cloudflare-topthreats");

if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Top Threats");

	$table = new Table;
	$table->construct_header("#", array("colspan" => 1));
	$table->construct_header("IP Address", array("colspan" => 1));
	$table->construct_header("Hits", array("colspan" => 1));
	$table->construct_header("Threat Score", array("colspan" => 1));

	if(function_exists('geoip_record_by_name'))
	{
		$table->construct_header("Location", array("colspan" => 1));
	}

	$table->construct_header("DNS", array("colspan" => 1));

	$table->construct_header("Options", array("colspan" => 1));

	$array = objectToArray($cloudflare->fetch_recent_visitors("t", "24")->response);

   	$count = 0;
	foreach($array['ips'] as $n => $data)
	{
		++$count;
	}

   	foreach($array['ips'] as $n => $data)
   	{
		$i = ++$number;

		if($i < 11)
		{

			$table->construct_cell("<strong>" . $i . "</strong>", array('width' => '1%'));
			$table->construct_cell("<a href=\"index.php?module=cloudflare-whois&amp;action=lookup&server=" . $data['ip'] . "\" target=\"_blank\">" . $data['ip'] . "</a> ", array('width' => '25%'));
			$table->construct_cell(my_number_format($data['hits']), array('width' => '25%'));
			$threat_score = cloudflare_threat_score($data['ip']);
			$table->construct_cell('<span style="color: '.threatscore2color($threat_score).'">'.$threat_score.'</span>', array('width' => '25%'));

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

			$dns = @gethostbyaddr($data['ip']);

			if($dns == $data['ip'])
			{
				$dns = 'N/A';
			}

			$dns = htmlspecialchars_uni($dns);

			$table->construct_cell($dns, array('width' => '25%'));

			$popup = new PopupMenu("rv_options_" . $number, "Options");
			$popup->add_item("Black List", "index.php?module=cloudflare-blacklist&amp;action=run&my_post_key={$mybb->post_code}&amp;address={$data['ip']}&amp;submit=Black List");
			$popup->add_item("White List", "index.php?module=cloudflare-whitelist&amp;action=run&my_post_key={$mybb->post_code}&amp;address={$data['ip'] }&amp;submit=White List");
			$controls = $popup->fetch();

			$table->construct_cell($controls, array('width' => '5%'));

			$table->construct_row();
		}
	}

	$table->output("Top Threats");

	$page->output_footer();
}

?>
