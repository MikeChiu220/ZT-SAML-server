<?php
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 定義 API URL
$IoTURL = 'http://192.168.10.201:3002/api/v1';
$apiDevicesList = $IoTURL . '/devices/list';
$apiPqcGatewayAlarms = $IoTURL . '/pqcGateway/alarms?page=%d&limit=%d&sortBy=%s';
$pageIndex = 1;
$pageLimit = 100;
$sortBy = 'createdAt';

// 呼叫 API: apiDevicesList 並獲取 JSON 資訊
$jsonData = file_get_contents($apiDevicesList);
if ($jsonData === false) {
    $error = error_get_last();
    syslog(LOG_ERR, "HTTP request $apiDevicesList failed. Error was: " . $error['message']);
    return;
}
$response = json_decode($jsonData, true);
//print_r($response);     // Mike for test
// 檢查 JSON 資料是否有效
if ( $response['success'] ) {
    $data = $response['data'];
    $devices = $data['devices'];
    foreach ($devices as $deviceInfo) {
        storeDeviceInfo($deviceInfo);
    }
}

// 呼叫 API: apiPqcGatewayStatusInd 並獲取 JSON 資訊
$ch = curl_init();
$headers = array('accept: */*');
$apiUrl = sprintf( $apiPqcGatewayAlarms, $pageIndex, $pageLimit, $sortBy);
curl_setopt($ch, CURLOPT_URL, $apiUrl); # URL to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers ); # custom headers, see above
$jsonData = curl_exec( $ch ); # run!
curl_close($ch);
// $jsonData = file_get_contents($apiPqcGatewayStatusInd);
$response = json_decode($jsonData, true);
// print_r($response);     // Mike for test
// 檢查 JSON 資料是否有效
if ($response['total']) {
    $alarms = $response['alarms'];
    // 多個設備的處理
    foreach ($alarms as $alarmInfo) {
        storeAalrmInfo($alarmInfo);
    }
}


// ---- Fund for Check receive UT information process ----
function storeDeviceInfo($deviceInfo) {
    global $db;
    // 提取所需的值
    $deviceId = $deviceInfo['deviceId'];
    $publicKey = $deviceInfo['publicKey']??'';
    $deviceType = $deviceInfo['deviceType']??'';
    $deviceName = $deviceInfo['deviceName']??'';
    $flowControlLevel = $deviceInfo['flowControlLevel']??'';
    $ipAddr = $deviceInfo['ipAddr']??'';
    $host = $deviceInfo['host']??'';
    $loginUser = $deviceInfo['loginUser']??'';
    $status = $deviceInfo['status']??'';
    $isRegistered = (int)$deviceInfo['isRegistered']??0;
    $isAuthenticated = (int)$deviceInfo['isAuthenticated']??0;
    $lastAuthenticated = $deviceInfo['lastAuthenticated']??'';
    $dateTime = new DateTime($lastAuthenticated);           // Create a DateTime object from the ISO date string
    $lastAuthenticated = $dateTime->format('Y-m-d H:i:s');  // Convert to MySQL DATETIME format
    $createdAt = $deviceInfo['createdAt'];
    $dateTime = new DateTime($createdAt);           // Create a DateTime object from the ISO date string
    $createdAt = $dateTime->format('Y-m-d H:i:s');  // Convert to MySQL DATETIME format
    $updatedAt = $deviceInfo['updatedAt'];    
    $dateTime = new DateTime($updatedAt);           // Create a DateTime object from the ISO date string
    $updatedAt = $dateTime->format('Y-m-d H:i:s');  // Convert to MySQL DATETIME format
    $gatewayId = $deviceInfo['connectedGatewayId']??'';
	$networkInfo = $deviceInfo['networkInfo']??'';
	$netSetCmd = '';
	if ( $deviceType == "pqc-gateway" && $networkInfo) {
		$networkRoute = $networkInfo['networkRoute']??'';
		$uploadTraffic = $networkInfo['uploadTraffic']??'';
		$downloadTraffic = $networkInfo['downloadTraffic']??'';
		$wansInfo = $networkInfo['networkInfo']??'';
		$i = 1;
		$netSetCmd = "networkRoute='$networkRoute'," . 
							"uploadTraffic='$uploadTraffic'," .
							"downloadTraffic='$downloadTraffic'";
		foreach ($wansInfo as $wanInfo) {
			$InterfaceName[$i] = $wanInfo['Interface']??'';
			$InterfaceHost[$i] = $wanInfo['host']??'';
			$InterfaceIpAddr[$i] = $wanInfo['ipAddr']??'';
			$netSetCmd = $netSetCmd . ",interface".$i."Name='".$InterfaceName[$i]."',"
									. "interface".$i."Host='".$InterfaceHost[$i]."',"
									. "interface".$i."IpAddr='".$InterfaceIpAddr[$i]."'";
			$i = $i + 1;
		}
		// 查找或創建新 Gateway 
		$query = $db->prepare('SELECT deviceId FROM gateway_Info WHERE deviceId = ?');
		$query->execute([$deviceId]);
		$result = $query->fetch(PDO::FETCH_ASSOC);
		if (!$result)
			$sqlComamnd = "INSERT INTO gateway_Info SET deviceId='$deviceId',deviceName='$deviceName',$netSetCmd";
		else
			$sqlComamnd = "UPDATE gateway_Info SET deviceName='$deviceName',$netSetCmd WHERE deviceId='$deviceId'";
		echo "** pollingIoT ** storeDeviceInfo - \n$sqlComamnd\n";		// Mike for test
		$result = $db->query($sqlComamnd);
		if (!$result) {
			syslog(LOG_ERR, "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true));		// Mike for test
			echo "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true);
		}
	}

//	echo "deviceId=$deviceId,publicKey=$publicKey,deviceType=$deviceType,deviceName=$deviceName,flowControlLevel=$flowControlLevel,ipAddr=$ipAddr,host=$host,loginUser=$loginUser,status=$status,isRegistered=$isRegistered,isAuthenticated=$isAuthenticated,lastAuthenticated=$lastAuthenticated,createdAt=$createdAt,updatedAt=$updatedAt\n";
    // 查找或創建新 iot device
    $query = $db->prepare('SELECT status,isRegistered,isAuthenticated,updatedAt FROM iotDevice_Info WHERE deviceId = ?');
    $query->execute([$deviceId]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sqlComamnd = "INSERT INTO iotDevice_Info SET deviceId='$deviceId',"
													. "publicKey='$publicKey',"
													. "deviceType='$deviceType',"
													. "deviceName='$deviceName',"
													. "flowControlLevel='$flowControlLevel',"
													. "ipAddr='$ipAddr',"
													. "host='$host',"
													. "loginUser='$loginUser',"
													. "status='$status',"
													. "isRegistered=$isRegistered," 
													. "isAuthenticated=$isAuthenticated,"
													. "lastAuthenticated='$lastAuthenticated',"
													. "createdAt='$createdAt',"
													. "updatedAt='$updatedAt'";
       $result = $db->query($sqlComamnd);
		if (!$result) {
			syslog(LOG_ERR, "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true));		// Mike for test
			echo "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true);
		}
/*
		$sqlComamnd = 'INSERT INTO iotDevice_Info (deviceId,publicKey,deviceType,deviceName,flowControlLevel,' . 
                                                'ipAddr,host,loginUser,status,isRegistered,' . 
                                                'isAuthenticated,lastAuthenticated,createdAt,updatedAt)' . 
                                                ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
		$query = $db->prepare($sqlComamnd);
        syslog(LOG_INFO, "** pollingIoT ** storeDeviceInfo - $sqlComamnd ($deviceId,,$deviceType,$deviceName,$flowControlLevel,$ipAddr,$host,$loginUser,$status,$isRegistered,$isAuthenticated,$lastAuthenticated,$createdAt,$updatedAt)");		// Mike for test
        $query->execute([$deviceId,$publicKey,$deviceType,$deviceName,$flowControlLevel,$ipAddr,$host,$loginUser,$status,$isRegistered,$isAuthenticated,$lastAuthenticated,$createdAt,$updatedAt]);
*/
    } else {
        $oldStatus = $result['status'];
        $sqlComamnd = "UPDATE iotDevice_Info SET publicKey='$publicKey',".
												"deviceType='$deviceType',".
												"deviceName='$deviceName',".
												"flowControlLevel='$flowControlLevel'," . 
												"ipAddr='$ipAddr',".
												"host='$host',".
												"loginUser='$loginUser',".
												"status='$status',".
												"isRegistered=$isRegistered," . 
												"isAuthenticated=$isAuthenticated,".
												"lastAuthenticated='$lastAuthenticated',".
												"createdAt='$createdAt',".
												"updatedAt='$updatedAt'" . 
												" WHERE deviceId = '$deviceId'";
       $result = $db->query($sqlComamnd);
		if (!$result) {
			syslog(LOG_ERR, "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true));		// Mike for test
			echo "** pollingIoT ** storeDeviceInfo - $sqlComamnd\n".print_r($db->errorInfo(),true);
		}
/*
        $sqlComamnd = 'UPDATE iotDevice_Info SET publicKey=?,deviceType=?,deviceName=?,flowControlLevel=?,ipAddr=?,' . 
                                            'host=?,loginUser=?,status=?,isRegistered=?,isAuthenticated=?,' . 
                                            'lastAuthenticated=?,createdAt=?,updatedAt=? WHERE deviceId = ?';
        $query = $db->prepare($sqlComamnd);
        syslog(LOG_INFO, "** pollingIoT ** storeDeviceInfo - $sqlComamnd ($deviceType,$deviceName,$flowControlLevel,$ipAddr,$host,$loginUser,$status,$isRegistered,$isAuthenticated,$lastAuthenticated,$createdAt,$updatedAt,$deviceId)");		// Mike for test
        $query->execute([$publicKey,$deviceType,$deviceName,$flowControlLevel,$ipAddr,$host,$loginUser,$status,$isRegistered,$isAuthenticated,$lastAuthenticated,$createdAt,$updatedAt,$deviceId]);
*/
        if ($oldStatus == $status) {
            return;
        }
    }
    // store into event log
    $alarmDescription = $deviceName . " ". $status;
    if ($status == 'online') {
        $alarmType = 'INFO';
    }
    else {
        $alarmType = 'FAULT';
    }
    $alarmInfo = array (
        'deviceId' => $deviceId,
        'deviceName' => $deviceName,
        'alarmType' => $alarmType,
        'alarmDescription' => $alarmDescription,
        'alarmStatus' => '',
        'notes' => '',
        'archivedAt' => null,
        'createdAt' => date('Y/m/d H:i:s')
    );
    storeAalrmInfo($alarmInfo);
/*
    $createdAt = date('Y/m/d H:i:s');
    $alarmStatus = '';
    $notes = '';
    $archivedAt = NULL;
    $sqlComamnd = 'INSERT INTO EventLog (deviceId,deviceName,alarmType,alarmDescription,alarmStatus,notes,archivedAt,createdAt)' . 
                                        ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
    $query = $db->prepare($sqlComamnd); 
    $query->execute([$deviceId, $deviceName, $alarmType, $alarmDescription, $alarmStatus, $notes, $archivedAt, $createdAt]);    */
}

?>