<?php

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

	public function request($request_data)
	{
		$ch = curl_init();

		if (isset($request_data['method']) &&  isset($request_data['post_data']) && $request_data['method'] == 'POST')
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data['post_data']));
		}

		if (isset($request_data['method']) && isset($request_data['patch_data']) && $request_data['method'] == 'PATCH')
		{
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data['patch_data']));
		}

		$url = $this->api_url . $request_data['endpoint'];

		if (isset($request_data['url_parameters']))
		{
			$url = $url . "?". http_build_query($request_data['url_parameters']);
		}

		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, "MyBB CloudFlare Manager Plugin");
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
			array(
				"X-Auth-Key: {$this->api_key}",
				"X-Auth-Email: {$this->email}",
				'Content-Type: application/json'
			)
		);

		$http_result = curl_exec($ch);

		$error = curl_error($ch);

		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return json_decode($http_result);
		if($http_code != 200)
		{
			//die("Error: $error\n");
		}
		else
		{
			$json = json_decode($http_result);
   		}
	}

	public function get_cloudflare_zone_id()
	{
		global $cache;
		$data = $this->request(
			array(
				'endpoint' => 'zones',
				'url_parameters' => array (
					'name' => $this->zone['cloudflare_domain']
				)
			)
		);
		$this->zone_id = $data->result[0]->id;
		$cache->update('cloudflare_zone_id', $data->result[0]->id);
		return (isset($data->result[0]->id) ? array("zone_id" => $data->result[0]->id) : array("error" => $data->errors[0]->message));
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

	public function get_statistics($interval) // see https://api.cloudflare.com/#zone-analytics-dashboard
	{
		$data = $this->request(
			array (
				'endpoint' => "zones/{$this->zone_id}/analytics/dashboard",
				'url_parameters' => array (
					'since' => $interval,
				)
			)
		);
		return $data;
	}

	public function whitelist($ip)
	{
		$data = array(
   			"a" => "wl",
        			"key" => $ip,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(WhiteList)');

		return $response->result;
	}

	public function blacklist($ip)
	{
		$data = array(
   			"a" => "ban",
        			"key" => $ip,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(BlackList)');

		return $response->result;
	}

	public function fetch_recent_visitors($type, $time)
	{
		$data = array(
   			"a" => "zone_ips",
        			"zid" => $this->fetch_zid(),
        			"email" => $this->email,
        			"tkn" => $this->api_key,
        			"hours" => $time,
        			"class" => $type,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(RecentVisitors)');

		return $response;
	}

	public function challenge($ip)
	{
		$data = array(
   			"a" => "zone_ips",
        			"zid" => $this->zone,
        			"key" => $ip,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(Challenge)');

		return $response->result;
	}

	public function remove_challenge($ip)
	{
		$data = array(
   			"a" => "nul",
        			"zid" => $this->zone,
        			"key" => $ip,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(RemoveChallenge)');

		return $response->result;
	}

	public function cache_level($level)
	{
		$data = array(
   			"a" => "cache_lvl",
        			"z" => $this->zone,
        			"v" => $level,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(CacheLevel)');

		return $response->result;
	}

	public function update_calls(datacache $cache)
	{
		$data = array(
   			"a" => "stats",
        			"z" => $this->zone,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
        			"calls_left" => "1200"
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(CallsLeftCheck)');

		$cache->update("cloudflare_calls",  $response->response->calls_left);
	}

	public function dev_mode($mode)
	{
		$data = array(
   			"a" => "devmode",
        			"z" => $this->zone,
        			"v" => $mode,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(DevMode)');

		return $response->result;
	}

	public function fetch_calls(datacache $cache)
	{
		return $cache->read("cloudflare_calls");
	}

	public function fetch_zid()
	{
		$data = array(
   			"a" => "zone_check",
        			"zones" => $this->zone,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(ZoneCheck)');

		$data = $response->response;
		$zones = $data->zones;
		$zone = $this->zone;

		return objectToArray($zones->$zone);
	}

	public function update_snapshot()
	{
		$data = array(
   			"a" => "zone_grab",
        			"zid" => $this->fetch_zid(),
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(UpdateSnapshot)');

		return $response->result;
	}

	public function purge_cache($mode)
	{
		$data = array(
   			"a" => "fpurge_ts",
        			"z" => $this->zone,
        			"v" => $mode,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(PurgeCache)');

		return $response->result;
	}

	public function purge_preloader_cache($mode)
	{
		$data = array(
   			"a" => "pre_purge",
        			"z" => $this->zone,
        			"v" => $mode,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(PurgePreloadercache)');

		return $response->result;
	}

	public function security_level($level)
	{
		$data = array(
   			"a" => "sec_lvl",
        			"z" => $this->zone,
        			"v" => $level,
        			"email" => $this->email,
        			"tkn" => $this->api_key,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(SecurityLevel)');

		print_r($response);

		return $response->result;
	}

	public function switch_ipv6($status)
	{
		$data = array(
   			"a" => "ipv46",
        			"zid" => $this->fetch_zid(),
        			"u" => $this->email,
        			"tkn" => $this->api_key,
			"z" => $this->zone,
			"v" => $status,
		);

		$response = $this->request($data, 'MyBB/CloudFlare-Plugin(IPv46)');

		return $response->result;
	}
}

function get_version()
{
	return cloudflare_info()['version'];
}