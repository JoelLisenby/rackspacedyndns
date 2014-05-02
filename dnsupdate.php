<?php

$domain = "yourdomain.com"; // domain
$subdomain = "subdomain.yourdomain.com"; // subdomain you want your ip to point to
$username = "user"; // rackspace username
$apikey = "askglorh2oh14oh14ngo4i1o4n1o41o4"; // rackspace apikey

$ip = trim(file_get_contents("https://www.icanhazip.com/"));
$lastip = trim(file_get_contents("lastip.txt"));
$ipchanged = false;

if($ip !== $lastip) {
	$ipchanged = true;
	echo "ip changed. ". $lastip ." to ". $ip ."\n";
	file_put_contents("lastip.txt", $ip);
} else {
	echo "ip not changed. ". $ip ."\n";
}

if($ipchanged) {
	$authdata = array (
		"auth" =>
		array(
			"RAX-KSKEY:apiKeyCredentials" =>
			array(
				"username" => $username,
				"apiKey" => $apikey
			)
		)
	);

	$authjson = json_encode($authdata);

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => 'https://identity.api.rackspacecloud.com/v2.0/tokens',
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $authjson,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Content-Length: '.strlen($authjson)
		)
	));

	$token_resp = json_decode(curl_exec($curl));

	curl_close($curl);

	$domains = rackget($token_resp->access->token->id, $token_resp->access->serviceCatalog[13]->endpoints[0]->publicURL ."/domains");

	foreach($domains->domains as $rdomain) {
		if($rdomain->name == $domain) {
			$domainid = $rdomain->id;
		}
	}

	$records = rackget($token_resp->access->token->id, $token_resp->access->serviceCatalog[13]->endpoints[0]->publicURL ."/domains/".$domainid);

	foreach($records->recordsList->records as $record) {
		if($record->name == $subdomain) {
			$recordid = $record->id;
		}
	}

	$mod_data = array (
		"id" => $recordid,
		"data" => $ip
	);

	$json_mod_data = json_encode($mod_data);

	$mod = rackmod($token_resp->access->token->id, $token_resp->access->serviceCatalog[13]->endpoints[0]->publicURL ."/domains/".$domainid."/records/".$recordid, $json_mod_data);
}

function rackmod($token, $url, $data) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_CUSTOMREQUEST => 'PUT',
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
			'X-Auth-Token: '. $token,
			'Content-Type: application/json',
			'Content-Length: '.strlen($data)
		)
	));
	$resp = curl_exec($curl);
	$array = json_decode($resp);
	return $array;
}

function rackget($token, $url) {
	$curl = curl_init();
	curl_setopt_array($curl, array(
	        CURLOPT_RETURNTRANSFER => 1,
	        CURLOPT_URL => $url,
	        CURLOPT_HTTPHEADER => array(
	                'Accept: application/json',
	                'X-Auth-Token: '. $token,
	                'Content-Type: application/json',
                	'Content-Length: 0'
        	)
	));
	$resp = curl_exec($curl);
	$array = json_decode($resp);
	return $array;
}
