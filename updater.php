<?php
// Paste a url like this in your router settings (tested with AVM FRITZ!Box):
// https://example.com/updater.php?user=<username>&password=<pass>&host=<domain>&ip=<ipaddr>&ip6=<ip6addr>

// Turn off all error reporting
error_reporting(0);

// Configuration 
// all values are examples!
$config = [
	'token' => 'bymeX5d5G2pgmBW8f4KpsM5pCHJAP7YaGm9G2tEi',
	'domains' => [
		'dyndns.example.com' => [
			'user' => 'fritzbox',
			'password' => 'password',
			'zone_id' => 'xR8P3ZDvX8ZkWJe9kmQVN7WJx8qZq278',
			'ttl' => 120
		]
	]
];

// Validation
$user = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_GET, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
$domain = filter_input(INPUT_GET, 'host', FILTER_SANITIZE_SPECIAL_CHARS);
$ip = filter_input(INPUT_GET, 'ip', FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
$ip6 = filter_input(INPUT_GET, 'ip6', FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);

// Check all 
if (!$user || !$password || !$domain || !$ip || !$ip6) {
	die('Incomplete or invalid data.');
}

// Check if domain exists
if (!isset($config['domains'][$domain])) {
	die('Domain not allowed or does not exist.');
}

// Check if user and password are correct
if ($user !== $config['domains'][$domain]['user'] || $password !== $config['domains'][$domain]['password']) {
	die('Incorrect username or password for the specified host.');
}

// Set the zone_id & ttl from config
$zone_id = $config['domains'][$domain]['zone_id'];
$ttl = $config['domains'][$domain]['ttl'];

// Headers for Cloudflare API
$headers = [
	'Authorization: Bearer '.$config['token'],
	'Content-Type: application/json'
];

// Update A and AAAA records
function updateDNSRecord($type, $ip, $domain, $zone_id, $ttl, $headers) {
	$ch = curl_init('https://api.cloudflare.com/client/v4/zones/'.$zone_id.'/dns_records?type='.$type.'&name='.$domain);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	
	$response = curl_exec($ch);
	$data = json_decode($response, true);

	if (isset($data['result'][0]['id'])) {
		$record_id = $data['result'][0]['id'];
		
		$updateCh = curl_init('https://api.cloudflare.com/client/v4/zones/'.$zone_id.'/dns_records/'.$record_id);
		curl_setopt($updateCh, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($updateCh, CURLOPT_CUSTOMREQUEST, 'PUT');
		curl_setopt($updateCh, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($updateCh, CURLOPT_POSTFIELDS, json_encode([
			'type' => $type,
			'name' => $domain,
			'content' => $ip,
			'ttl' => $ttl
		]));

		$updateResponse = curl_exec($updateCh);
		$updateData = json_decode($updateResponse, true);

		if ($updateData['success']) {
			return true;
		}
	}

	return false;
}

$updateIPv4 = updateDNSRecord('A', $ip, $domain, $zone_id, $ttl, $headers);
$updateIPv6 = updateDNSRecord('AAAA', $ip6, $domain, $zone_id, $ttl, $headers);

if ($updateIPv4 && $updateIPv6) {
	echo 'DNS records updated successfully.';
} else {
	echo 'Error updating DNS records.';
}
?>
