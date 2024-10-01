<?php
/*
 * Modify Note
 */
include("NcdKeyProc.php");
$SleepTime = 5;
for (;;) {
	$command = "SELECT FireAlarmURL FROM systemConf";
	$queryId = FUN_SQL_QUERY($command, $database);
	$query_num= FUN_SQL_NUM_ROWS($queryId);
	$result=404;
	$errorMsg="";
	if ( $query_num ) {
		$row = FUN_SQL_FETCH_ARRAY( $queryId);
		$FireAlarmURL	= $row["FireAlarmURL"];
		if ( $FireAlarmURL ) {
			// Post Url Process
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $FireAlarmURL);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//			curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv2 );	// Mike[2021/09/14] CURL_SSLVERSION_SSL, 2		// Mike[2023/06/21] delete for HTTPS test
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE );
			curl_setopt($ch, CURLOPT_TIMEOUT, 1);	// Mike[2022/01/22] set to zero for no timeout
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: 0') );
			$data = curl_exec($ch);
			$result= curl_errno($ch);
			if ( $result ) {
				$errorMsg= curl_error($ch);
				syslog(LOG_INFO, "** FireAlarmPolling ** $FireAlarmURL =>($result)$errorMsg");		// for test	
			}
			else {
//				syslog(LOG_INFO, "** FireAlarmPolling ** $FireAlarmURL => $data");		// for test	
				$responseArray = json_decode($data, true);
//				syslog(LOG_INFO, "** FireAlarmPolling ** Response: ".print_r($responseArray, TRUE));		// for test	
				if ( $responseArray != NULL ) {	// Fire Alarm Active
					foreach( $responseArray as $response ) {
	//					syslog(LOG_INFO, "** FireAlarmPolling ** Response: ".print_r($response, TRUE));		// for test	
						if ( $response != NULL && array_key_exists('ID',$response) ) {	// Fire Alarm Active
							$EventNo= C_FUSION_EVENT;
							$EventId= C_FIRE_ALARM_EVENT_ID;
							$objectId= C_FIRE_ALARM_EVENT_ID;
							$taskId= $response['ID'];
							$taskMessage= $response['Location']."-".$response['CallMethod'];
//							syslog(LOG_INFO, "** FireAlarmPolling ** FusionEventProc(".C_FIRE_ALARM_EVENT_ID.", ".C_FUSION_EVENT.", ".$response['ID'].", $taskMessage, ".$response['ID'].", NULL, NULL, NULL, NULL)");		// for test	
							FusionEventProc($EventId, $EventNo, $objectId, $taskMessage, $taskId, NULL, 0, 0, 0);	// ($EventId, $EventNo, $objectId, $taskMessage, $taskId, $FusionURL, $FusionPort, $callout, $outLine);
							$result=200;
						}
					}
				}
				else {										// Fire Alarm Stop
					$command = "SELECT ncd_no,taskId, proc_fg FROM ncd_active_event WHERE ncd_no='Fusion' AND taskId=99 AND proc_fg IS NULL";
					$queryId = FUN_SQL_QUERY($command, $database);
					$query_num= FUN_SQL_NUM_ROWS($queryId);
//					syslog(LOG_INFO, "** FireAlarmPolling ** $command->$query_num");								// for test
					if ( $query_num ) {
						$NcdNo="Fusion-99";
						syslog(LOG_INFO, "** FireAlarmPolling ** doNcdKeyProc($NcdNo, ".C_NCD_CANCEL_CALL_BUTTON.", NULL, NULL, NULL, NULL, NULL)");		// for test	
						[$result,$errorMsg] = doNcdKeyProc($NcdNo, C_NCD_CANCEL_CALL_BUTTON, NULL, NULL, NULL, NULL, NULL);		// ($NcdNo, $Key, $BedNo, $CalledNo, $AnswerTm, $AnswerNo, $NcdName)
					}
					$result=200;
				}
			}
		}
		else
			exit();
	}
	else
		exit();
	
	if ($result<>200) {
//		header('Content-type: application/json');
//		echo "{ 'resultCode': $result, 'errorMsg': '$errorMsg' }";
		$errorCount++;
		$SleepTime = 5 + 10*($errorCount/5); 
		if ( $SleepTime > 60*5 )
			$SleepTime > 60*5;
		syslog(LOG_INFO, "** FireAlarmPolling ** { 'resultCode': $result, 'errorMsg': '$errorMsg' }($errorCount)");		// for test	
	}
	else {
//		header("HTTP/1.1 200 OK");
		$errorCount= 0;
		$SleepTime = 5;
	}
	sleep($SleepTime);
}
?>