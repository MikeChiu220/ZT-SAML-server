<?php
/*
 * Send POST request with JSON Data 
 * Iput:url = post HTTP URL 
 *		data = post data array
 */
function postHttpJson($url, $data)
{
/*  Using file_get_contents
	$options = array(
		'http' => array(
		    'method'	=> 'POST',
//		    'method'	=> 'GET',
		    'content'	=> json_encode( $data ),
			'header'	=>  "Content-Type: application/json\r\n" .
			"Accept: application/json\r\n"
		)
	);
	$context  = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
*/
/*  Using curl for impove performance
*/
    global $errorMsg;				// Mike[2022/06/30]
	
//	echo "postHttpJson-curl_init($url)";		// Mike[2022/09/16] For test
	$ch = curl_init($url);
//	echo "postHttpJson-json_encode";		// Mike[2022/09/16] For test
	$jsonData=json_encode( $data );
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
											'Content-Type: application/json'
//											,'Content-Length: ' . strlen($jsonData)
											)
	);
//	echo "postHttpJson-curl_exec($ch)";		// Mike[2022/09/16] For test
	$result = curl_exec($ch);
	if ($result == FALSE) {							// Mike[2022/06/30]
		$errorMsg='Curl error: '.curl_error($ch);	// /
		echo "postHttpJson-curl_exec($ch): $errorMsg";		// Mike[2022/09/16] For test
    }
//	else
//		echo "postHttpJson-curl_exec($ch): Sucess";		// Mike[2022/09/16] For test
		

	curl_close($ch);

	return $result;
}

/*
 * Send PATCH request with JSON Data
 * Iput:url = patch HTTP URL 
 *		data = post data array
 */
function patchHttpJson($url, $data)
{
/*  Using curl for impove performance
*/
    global $errorMsg;				// Mike[2022/06/30]
	
	$ch = curl_init($url);
	$jsonData=json_encode( $data );
//	echo "patchHttpJson($url, $jsonData)";		// Mike[2022/11/04] For test
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//					'Connection: keep-alive',
//					'Accept: application/json, text/javascript, */*; q=0.01',
//					'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
					'Content-Type: application/json'
//					'Origin: http://192.168.2.6:8000',
//					'Referer: http://192.168.2.6:8000/',
//					'Accept-Encoding: gzip, deflate',
//					'Accept-Language: zh-TW,zh;q=0.9,en-US;q=0.8,en;q=0.7'											
											)
	);
	$result = curl_exec($ch);
	if ($result == FALSE) {							// Mike[2022/06/30]
		syslog( LOG_INFO, "patchHttpJson-curl_exec() error: ".curl_error($ch) );		// Mike[2023/06/05] For test
    }
	else
		syslog( LOG_INFO, "patchHttpJson-curl_exec() -> ".$resultArray['status'] );	// Mike[2023/06/05] For test
		

	curl_close($ch);

	return $result;
}
/*
 * Remove Auto recall for NCD 
 * Iput:url = patch HTTP URL 
 *		data = post data array
 */
function getHttpJson($url)
{
/*  Using curl for impove performance
*/
    global $errorMsg;				// Mike[2022/06/30]
	
	echo "getHttpJson($url)";		// Mike[2022/11/04] For test
	$ch = curl_init($url);
//	$jsonData=json_encode( $data );
//	curl_setopt($ch, CURLOPT_POST, 1);
//	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
//	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
											'Content-Type: application/json'
											)
	);
	$result = curl_exec($ch);
	if ($result == FALSE) {							// Mike[2022/06/30]
		$errorMsg='Curl error: '.curl_error($ch);	// /
		echo "getHttpJson-curl_exec($ch): $errorMsg";		// Mike[2022/09/16] For test
		$resultArray = "";
    }
	else
		$resultArray = json_decode($result,true);

	curl_close($ch);

	return $resultArray;
}

/*
 * Generate NCD Config File
 * Iput:arrayConf = array include seting config information 
 *					'FixIp', 'NetMask', 'GatewayIp', 'auth_id', 'passwd', 'dp_number', 'display_name', 'domain_server', 
 *					'registrar', 'sip_identity', 'autoconfig_server_name', 'autoupdate_server_name',
 *					'autoconfig_username', 'autoconfig_password', 'autoupdate_username', 'autoupdate_password', 'dipSwitch'
 *					'telpar_broadcast_group'
 * Date: Mike[2023/04/10]
 */
function GenerateConfFile($arrayConf){
	$arrayCheckFields = array ( 'FixIp', 'NetMask', 'GatewayIp', 'auth_id', 'passwd', 'dp_number', 'display_name', 'domain_server', 
								'registrar', 'sip_identity', 'autoconfig_server_name', 'autoupdate_server_name',
								'autoconfig_username', 'autoconfig_password', 'autoupdate_username', 'autoupdate_password',
								'telpar_broadcast_group'
								 );
	$dipSwitch = str_pad($arrayConf['dipSwitch'], 3, '0', STR_PAD_LEFT );
	$createNcd7Cfg="/home/ip-ncd7/FTP/".$dipSwitch."_config.cfg";	// Create config file
	if (file_exists($createNcd7Cfg)) { 
		$ncd7ConfFile = $createNcd7Cfg;
		$createFlag = 1;
	}
	else {
		$ncd7ConfFile = DEF_NCD7_CFG;
		$createFlag = 1;
	}
//	syslog(LOG_INFO, "Create file: $createNcd7Cfg for {$arrayConf['auth_id']} with IP ({$arrayConf['FixIp']})");		// for test
	$handle = fopen( $ncd7ConfFile, "r" );
	if ($handle) {
		// 1. Read config file and replace the setting
		$index = 0;
		while (($line[$index] = fgets($handle, 200)) !== false) {
			$field= explode("=", $line[$index]);
			for ( $indexCheck= 0; $indexCheck < sizeof($arrayCheckFields); $indexCheck++ ) {
				if ( $field[0] == $arrayCheckFields[$indexCheck] && array_key_exists($field[0], $arrayConf)) {
					$line[$index] = $field[0]."=".$arrayConf[$field[0]]."\n";
//					syslog(LOG_INFO, "line[$index]: {$line[$index]}");		// for test
					break;
				}
			}
			$index++;
		}
		fclose($handle);
		// 2. Write config file 
		$totalIndex = $index;
		$handle = fopen( $createNcd7Cfg, "w" );
		if ($handle) {
			for ( $index= 0; $index < $totalIndex; $index++ ) {
				fwrite( $handle, $line[$index] );
			}
			fclose($handle);
			if ( $createFlag ) {
				chmod( $createNcd7Cfg, 0666 );
//				chown( $createNcd7Cfg, 'ip-ncd7' );
			}
		}
	}
}
/*
 * Create Sip Account
 * Iput:arrayConf = array include SIP account seting information 
 *					'phoneno', 'passwd', 'pin', 'realname', 'toll', 'language', 'blf', 'codecs', 'dipSwitch'
 * Date: Mike[2023/04/10]
 */
function CreateSipAccount($arrayConf){
    global $database;

	if ($arrayConf['blf'] =='') {
		$call_limit=3;
	}
	else {
		$call_limit=$arrayConf['blf'];
	}
	if($call_limit == 3) 
		$blfFlag = 'no';
	else
		$blfFlag = 'yes';
	$command="select phoneno from `phone` WHERE phoneno='".$arrayConf['phoneno']."'";
	$query = FUN_SQL_QUERY($command,$database);
	$phone_num = FUN_SQL_NUM_ROWS($query);
	if ( $phone_num ) {		// Account already exist, do update
		$command="UPDATE `phone` SET passwd='".$arrayConf['passwd']."'";
		$command .= ",telecom_pin='".$arrayConf['pin']."'";
		$command .= ",realname='".$arrayConf['realname']."'";
		$command .= ",toll='".$arrayConf['toll']."'";
		$command .= ",dipSwitch='".$arrayConf['dipSwitch']."'";
		$command .= " WHERE phoneno='".$arrayConf['phoneno']."'";
		$query = FUN_SQL_QUERY($command,$database);

		$command = "UPDATE `sip_buddies` SET secret='".$arrayConf['passwd']."'";
		$command .= ",`call-limit`='".$call_limit."'";
		$command .= ",language='".$arrayConf['language']."'";
		$command .= ",callerid='".$arrayConf['phoneno']."'";
		$command .= ",allow='".$arrayConf['codecs']."', allowsubscribe='$blfFlag'";
		$command .= " WHERE name='".$arrayConf['phoneno']."'";
		$query = FUN_SQL_QUERY($command,$database);
	}
	else {					// Account not exist, do insert
		$command="INSERT INTO `phone` SET phoneno='".$arrayConf['phoneno']."'";
		$command .= ",passwd='".$arrayConf['passwd']."'";
		$command .= ",telecom_pin='".$arrayConf['pin']."'";
		$command .= ",realname='".$arrayConf['realname']."'";
		$command .= ",protocol='SIP'";
		$command .= ",toll='".$arrayConf['toll']."'";
		$command .= ",dipSwitch='".$arrayConf['dipSwitch']."'";
		$query = FUN_SQL_QUERY($command,$database);

		$command="INSERT INTO `ext_internal` SET exten='".$arrayConf['phoneno']."',app='AELSub',appdata='ext,".$arrayConf['phoneno']."'";
		$query = FUN_SQL_QUERY($command,$database);

		$fp=fopen("/etc/asterisk/sip.conf","r");
		if ($fp) {
			while (!feof($fp)) {
				$contents = fgets($fp);
				if (preg_match("/^\w+/",$contents,$none)) {
					$c_name=strtok($contents, "=");
					if ($c_name != false && $c_name == "bindport"){
						$udpport = strtok(" \t\n");
						break;
					} 
				}
			}
			fclose($fp);
		}

		$command = "INSERT INTO `sip_buddies` SET name='".$arrayConf['phoneno']."'";
		$command .= ",`call-limit`='".$call_limit."'";
		$command .= ",callgroup='0',pickupgroup='0',pagegroup='0'";
		$command .= ",language='".$arrayConf['language']."'";
		$command .= ",port='".$udpport."'";
		$command .= ",secret='".$arrayConf['passwd']."'";
		$command .= ",callerid='".$arrayConf['phoneno']."'";
		$command .= ",allow ='".$arrayConf['codecs']."',qualify='yes', allowsubscribe='$blfFlag'";		
		$query = FUN_SQL_QUERY($command,$database);
	}
}
?>