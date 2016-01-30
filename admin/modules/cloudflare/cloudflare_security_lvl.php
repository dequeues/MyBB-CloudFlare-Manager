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

$page->output_footer();

?>
