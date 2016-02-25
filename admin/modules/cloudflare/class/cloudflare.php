<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class cloudflare {

	public $zone = '';
	private $api_key = '';
	public $email = '';
	public $api_url = 'https://api.cloudflare.com/client/v4/';
	public $zone_id;

	public function __construct(MyBB $mybb, $zone_id) {
		$this->zone = $mybb->settings['cloudflare_domain'];
		$this->api_key = $mybb->settings['cloudflare_api'];
		$this->email = $mybb->settings['cloudflare_email'];
		$this->zone_id = $zone_id;
		if (!$zone_id)
		{
			$this->get_cloudflare_zone_id();
		}
	}

	public function request($request_data, $custom_url = false)
	{
		$ch = curl_init();

		if (isset($request_data['method']))
		{
			if ($request_data['method'] == 'POST')
			{
				curl_setopt($ch, CURLOPT_POST, 1);
			}

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_data['method']);

			if (isset($request_data['post_fields']))
			{
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data['post_fields']));
			}
		}

		if (!$custom_url)
		{
			$url = $this->api_url . $request_data['endpoint'];

			if (isset($request_data['url_parameters']))
			{
				$url = $url . "?". http_build_query($request_data['url_parameters']);
			}
		}
		else
		{
			$url = $custom_url;
		}

		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "MyBB CloudFlare Manager Plugin");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (!$custom_url)
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER,
				array(
					"X-Auth-Key: {$this->api_key}",
					"X-Auth-Email: {$this->email}",
					'Content-Type: application/json'
				)
			);
		}

		$http_result = curl_exec($ch);

		$error = curl_error($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($custom_url)
		{
			return htmlspecialchars($http_result);
		}

		return json_decode($http_result);
	}

	public function get_cloudflare_zone_id()
	{
		global $cache;
		$data = $this->request(
			array(
				'endpoint' => 'zones',
				'url_parameters' => array (
					'name' => $this->zone
				)
			)
		);

		if (is_null($data) || sizeof($data->errors) > 0)
		{
			return array('errors' => $this->get_all_errors($data));
		}
		else
		{
			$this->zone_id = $data->result[0]->id;
			$cache->update('cloudflare_zone_id', $data->result[0]->id);
			return $data->result[0]->id;
		}
	}

	public function dns_status()
	{
		$dns = dns_get_record($this->zone, DNS_NS);

		foreach($dns as $ns)
		{
			if(strpos($ns['target'], ".ns.cloudflare.com"))
			{
				return true;
			}
		}
		return false;
	}

	public function objectToArray($d) {
		if(is_object($d)) {
			$d = get_object_vars($d);
		}

		if(is_array($d)) {
			return array_map(array($this, 'objectToArray'), $d); // recursive
		} else {
			return $d;
		}
	}

	public function get_statistics($interval) // see https://api.cloudflare.com/#zone-analytics-dashboard
	{
		$data = $this->request (
			array (
				'endpoint' => "zones/{$this->zone_id}/analytics/dashboard",
				'url_parameters' => array (
					'since' => $interval,
				)
			)
		);
		return $data;
	}

	function dev_mode($setting = NULL)
	{
		$endpoint = "zones/{$this->zone_id}/settings/development_mode";

		if (is_null($setting))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint
				)
			);

			return $data;
		}
		else
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint,
					'method' => 'PATCH',
					'post_fields' => array (
						'value' => $setting
					)
				)
			);

			return $data;
		}
	}


	public function whitelist_ip($ip, $notes = '')
	{
		return $this->update_access_rule("whitelist", $ip, $notes);
	}

	public function blacklist_ip($ip, $notes = '')
	{
		return $this->update_access_rule("block", $ip, $notes);
	}

	public function challenge_ip($ip, $notes = '')
	{
		return $this->update_access_rule("challenge", $ip, $notes);
	}

	public function update_access_rule($mode, $ip, $notes = '')
	{
		$data = $this->request (
			array (
				'endpoint' => "zones/{$this->zone_id}/firewall/access_rules/rules",
				'method' => 'POST',
				'post_fields' => array (
					'mode' => $mode,
					'configuration' => array (
						'target' => 'ip',
						'value' => $ip,
					),
					'notes' => $notes
				)
			)
		);

		if (!$data->success)
		{
			$errors = array();
			foreach ($data->errors as $error)
			{
				$errors['errors'] = $error->message;
			}
			return $errors;
		}
		else
		{
			return array("success" => true);
		}
	}

	public function ipv46_setting($setting = NULL)
	{
		$endpoint = "/zones/{$this->zone_id}/settings/ipv6";

		if (is_null($setting))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint
				)
			);
			return $data;
		}

		$data = $this->request(
			array (
				'endpoint' => $endpoint,
				'method' => 'PATCH',
				'post_fields' => array (
					'value' => $setting
				)
			)
		);

		return $data;
	}

	public function get_access_rules()
	{
		$data = $this->request(
			array (
				'endpoint' => "/zones/{$this->zone_id}/firewall/access_rules/rules"
			)
		);
		return $data;
	}

	public function delete_firewall_rule($rule_id)
	{
		$data = $this->request(
			array (
				'endpoint' => "/zones/{$this->zone_id}/firewall/access_rules/rules/{$rule_id}",
				'method' => 'DELETE'
			)
		);

		return $data;
	}

	public function cache_level($setting = NULL)
	{
		$endpoint = "/zones/{$this->zone_id}/settings/cache_level";

		if (is_null($setting))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint
				)
			);
			return $data;
		}

		$data = $this->request(
			array (
				'endpoint' => $endpoint,
				'method' => 'PATCH',
				'post_fields' => array (
					'value' => $setting
				)
			)
		);

		return $data;
	}

	public function purge_cache($urls = NULL)
	{
		$endpoint = "/zones/{$this->zone_id}/purge_cache";

		if (is_null($files))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint,
					'method' => 'DELETE',
					'post_fields' => array (
						'purge_everything' => true
					)
				)
			);

			return $data;
		}

		if (is_array($files))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint,
					'method' => 'DELETE',
					'post_fields' => array (
						'files' => 'urls'
					)
				)
			);
		}
	}

	public function get_latest_version()
	{
		$data = $this->request("", 'https://raw.githubusercontent.com/dequeues/MyBB-CloudFlare-Manager/master/inc/plugins/cloudflare.php');
		preg_match('/define\(\'CLOUDFLARE_MANAGER_VERSION\', \'(.*?)\'\);/', $data, $matches);
		return $matches[1];
	}

	public function security_level_setting($setting = NULL)
	{
		$endpoint = "/zones/{$this->zone_id}/settings/security_level";
		if (is_null($setting))
		{
			$data = $this->request(
				array (
					'endpoint' => $endpoint
				)
			);
			return $data;
		}

		$data = $this->request(
			array (
				'endpoint' => $endpoint,
				'method' => 'PATCH',
				'post_fields' => array (
					'value' => $setting
				)
			)
		);

		return $data;
	}

	public function get_all_errors($raw)
	{
		$errors = array();
		if (is_array($raw->errors))
		{
			foreach ($raw->errors as $error)
			{
				$errors[] = $error->message;
			}
		}
		return $errors;
	}
}
