<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$page->add_breadcrumb_item("CloudFlare Manager", "index.php?module=cloudflare");
$page->add_breadcrumb_item("Security Level", "index.php?module=cloudflare-security_lvl");
$page->output_header("CloudFlare Manager - Security Level");

$security_levels = 	array('essentially_off' => 'Essentially Off',
	'low' => 'Low',
	'medium' => 'Medium',
	'high' => 'High',
	'under_attack' => 'Under Attack'
);

function main_page($current_setting)
{
	global $security_levels;
	$form = new Form('index.php?module=cloudflare-security_lvl&amp;action=change_security_level', 'post');
	$form_container = new FormContainer('Modify Security Level');
	$form_container->output_row('Security Level',
		'The Security Level you choose will determine which visitors will be presented with a challenge page<br />
		<ul>
			<li><b>Essentially Off:</b> Challenges only the most grievous offenders</li>
			<li><b>Low:</b> Challenges only the most threatening visitors</li>
			<li><b>Medium:</b> Challenges both moderate threat visitors and the most threatening visitors</li>
			<li><b>High:</b> Challenges all visitors that have exhibited threatening behavior within the last 14 days</li>
			<li><b>I\'m Under Attack!:</b> Should only be used if your website is under a DDoS attack</li>
				<ul><li>Visitors will receive an interstitial page while we analyze their traffic and behavior to make sure they are a legitimate human visitor trying to access your website</li></ul>
			</li>
		</ul>',
		$form->generate_select_box('sec_level',
			$security_levels,
			$current_setting
		)
	);
	$form_container->end();
	$buttons[] = $form->generate_submit_button('Submit');
	$form->output_submit_wrapper($buttons);
	$form->end();
}

$errors = [];
if ($mybb->input['action'] == 'change_security_level')
{
	$request = $cloudflare->security_level_setting($mybb->input['sec_level']);

	if ($request->success)
	{
		$page->output_success("The security level has now been set to {$security_levels[$mybb->input['sec_level']]}");
	}
	else
	{
		$page->output_error($request->errors[0]->message);
	}
}

if ($mybb->input['action'] == 'change_security_level' && empty($errors))
{
	$current_setting = $mybb->input['sec_level'];
}
else
{
	$request = $cloudflare->security_level_setting();
	$current_setting = $request->result->value;
}

$page->output_alert("The current security level is set as {$security_levels[$current_setting]}");
main_page($current_setting);

die();
if(!$mybb->input['action'])
{
	$page->output_header("CloudFlare Manager - Security Level");

	$table = new Table;

	if(cloudflare_security_level() == 'Low')
	{
		$low = 'selected=selected';
		$medium = '';
		$high = '';
		$eoff = '';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'Medium')
	{
		$low = '';
		$medium = 'selected=selected';
		$high = '';
		$eoff = '';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'High')
	{
		$low = '';
		$medium = '';
		$high = 'selected=selected';
		$eoff = '';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'Essentially Off')
	{
		$low = '';
		$medium = '';
		$high = '';
		$eoff = 'selected=selected';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == "I’m under attack!")
	{
		$low = '';
		$medium = '';
		$high = '';
		$eoff = '';
		$attackmode = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Adjust your basic security level to modify CloudFlare\'s protection behavior.</strong><br /><br />
	<form action="index.php?module=cloudflare-security_lvl&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="attack"'.$attackmode.'>I’m under attack!</option>
  	<option name="high"'.$high.'>High</option>
  	<option name="medium"'.$medium.'>Medium</option>
  	<option name="low"'.$low.'>Low</option>
  	<option name="essentially_off"'.$eoff.'>Essentially Off</option>
</select><br /><br />
A <strong>low</strong> security setting will challenge only the most threatening visitors. A <strong>high</strong> security setting will challenge all visitors that have exhibited threatening behavior within the last 14 days. <strong>Essentially off</strong> will act only against the most grievous offenders. We recommend starting out at medium.
<br /><br />
<strong>I\'m Under Attack Mode</strong> should only be used when a site is having a DDoS attack. Visitors will receive an interstitial page for about five seconds while we analyze the traffic and behavior to make sure it is a legitimate human visitor trying to access your site.
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>

	');

	$table->construct_row();

	$table->output("Change Security Level");

	$page->output_footer();

}
elseif($mybb->input['action'] == "change")
{
	if(!verify_post_check($mybb->input['my_post_key']))
	{
		flash_message($lang->invalid_post_verify_key2, 'error');
		admin_redirect("index.php?module=cloudflare-security_lvl");
	}

	$page->output_header("CloudFlare Manager - Security Level");

	if($mybb->input['type'] == "High")
	{
		$type = "high";
	}
	elseif($mybb->input['type'] == "Medium")
	{
		$type = "med";
	}
	elseif($mybb->input['type'] == "Low")
	{
		$type = "low";
	}
	elseif($mybb->input['type'] == "Essentially Off")
	{
		$type = "eoff";
	}
	elseif($mybb->input['type'] == 'I’m under attack!')
	{
		$type = 'help';
	}
	else
	{
		$type = "medium";
	}

	$request = $cloudflare->security_level($type);

	if($request == "success")
	{
		$page->output_success("<p><em>CloudFlare security level has sucessfully been changed to {$mybb->input['type']}.</em></p>");
		log_admin_action('Changed security level to '.htmlspecialchars_uni($mybb->input['type']).' on '.$mybb->settings['cloudflare_domain']);
	}
	elseif($request == "error")
	{
		flash_message("CloudFlare security level was not changed to {$mybb->input['type']}.", "error");
		log_admin_action('Failed to change security level to '.htmlspecialchars_uni($mybb->input['type']).' on '.$mybb->settings['cloudflare_domain']);
	}

	$table = new Table;

	if(cloudflare_security_level() == 'Low')
	{
		$low = 'selected=selected';
		$medium = '';
		$high = '';
	}
	elseif(cloudflare_security_level() == 'Medium')
	{
		$low = '';
		$medium = 'selected=selected';
		$high = '';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'High')
	{
		$low = '';
		$medium = '';
		$high = 'selected=selected';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'Essentially Off')
	{
		$low = '';
		$medium = '';
		$high = '';
		$eoff = 'selected=selected';
		$attackmode = '';
	}
	elseif(cloudflare_security_level() == 'I’m Under Attack')
	{
		$low = '';
		$medium = '';
		$high = '';
		$eoff = '';
		$attackmode = 'selected=selected';
	}

	$table->construct_cell('
	<strong>Adjust your basic security level to modify CloudFlare\'s protection behavior.</strong><br /><br />
	<form action="index.php?module=cloudflare-security_lvl&amp;action=change" method="post">
	<input type="hidden" value="'. $mybb->post_code .'" name="my_post_key">
	Level: <select name="type">
  	<option name="attack"'.$attackmode.'>I’m under attack!</option>
  	<option name="high"'.$high.'>High</option>
  	<option name="medium"'.$medium.'>Medium</option>
  	<option name="low"'.$low.'>Low</option>
  	<option name="essentially_off"'.$eoff.'>Essentially Off</option>
</select><br /><br />
A <strong>low</strong> security setting will challenge only the most threatening visitors. A <strong>high</strong> security setting will challenge all visitors that have exhibited threatening behavior within the last 14 days. <strong>Essentially off</strong> will act only against the most grievous offenders. We recommend starting out at medium.
<br /><br />
<strong>I\'m Under Attack Mode</strong> should only be used when a site is having a DDoS attack. Visitors will receive an interstitial page for about five seconds while we analyze the traffic and behavior to make sure it is a legitimate human visitor trying to access your site.
<br /><br />
	<input class="submit_button" type="submit" name="submit" value="Change">
	</form>

	');

	$table->construct_row();

	$table->output("Change Security Level");

	$page->output_footer();
}

?>
