<?php
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 定義 API URL
$TinosURL = 'https://192.168.10.20';
$apiUT = $TinosURL . '/getUTData.jsp';
$apiPOP = $TinosURL . '/pop.jsp';

// 呼叫 API: getUTData 並獲取 JSON 資訊
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUT); # URL to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
// 禁用 SSL 证书验证
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$jsonData = curl_exec( $ch ); # run!
curl_close($ch);
// $jsonData = file_get_contents($apiUT);
if ($jsonData === false) {
    echo "HTTP request $apiUT failed. Error was: " . curl_error($ch)."\n";
    syslog(LOG_ERR, "HTTP request $apiUT failed. Error was: " . curl_error($ch));
    return;
}
$data = json_decode($jsonData, true);
//print_r($data);     // Mike for test

// 檢查 JSON 資料是否有效
if ($data) {
    foreach ($data as $utInfo) {
        if (is_array($utInfo))
            storeUtInfo($utInfo);
        else 
            storeUtInfo($data);
    }
}
else  {
    syslog(LOG_ERR, "HTTP request $apiUT response failed. Error was: " . curl_error($ch));
}

// 呼叫 API: pop 並獲取 JSON 資訊
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiPOP); # URL to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
// 禁用 SSL 证书验证
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$jsonData = curl_exec( $ch ); # run!
curl_close($ch);
// $jsonData = file_get_contents($apiPOP);
if ($jsonData === false) {
    $error = error_get_last();
    syslog(LOG_ERR, "HTTP request $apiPOP failed. Error was: " . $error['message']);
    return;
}
$data = json_decode($jsonData, true);
//print_r($data);     // Mike for test

// 檢查 JSON 資料是否有效
if (is_array($data)) {
    // 多個設備的處理
    foreach ($data as $popInfo) {
        storePopInfo($popInfo);
    }
}
else if ($data) {
    storePopInfo($data);
}


// ---- Fund for Check receive UT information process ----
function storeUtInfo($utInfo) {
//    print_r($utInfo);     // Mike for test

    global $db;
    // 提取所需的值
    $time = $utInfo['time'];
    $vendorid = $utInfo['vendorid']??'';
    $imei = $utInfo['imei'];
    $imsi = $utInfo['imsi']??'';
    $ip = $utInfo['ip']??'';
    $model = $utInfo['model']??'';
    $status = $utInfo['status']??'';
    $location = $utInfo['location']??'';
    // 查找或創建新UT
    $query = $db->prepare('SELECT ip,status,location FROM UT_Info WHERE imei = ?');
    $query->execute([$imei]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sqlComamnd = 'INSERT INTO UT_Info (vendorid,imei,imsi,ip,model,status,location) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $query = $db->prepare($sqlComamnd);
        $query->execute([$vendorid, $imei, $imsi, $ip, $model, $status, $location]);
        syslog(LOG_INFO, "** pollingTinos ** storeUtInfo - $sqlComamnd ($vendorid, $imei, $imsi, $ip, $model, $status, $location)");		// Mike for test
    } else {
        $oldIp = $result['ip'];
        $oldStatus = $result['status'];
        $oldLocation = $result['location'];
        if ($oldStatus != $status || $oldIp != $ip || $oldLocation != $location) {
            // Status has changed, update and store into event log
            $sqlComamnd = 'UPDATE UT_Info SET status=?,location=?,ip=? WHERE imei=?';
            $query = $db->prepare($sqlComamnd);
            $query->execute([$status, $location, $ip, $imei]);
            syslog(LOG_INFO, "** pollingTinos ** storeUtInfo - $sqlComamnd ($status, $location, $ip, $imei)");		// Mike for test
            if ($oldStatus == $status)
                return;
        }
        else
            return;
    }
    // store into event log
    $deviceName = 'UT-'.$imei;
    $alarmDescription = $deviceName . " ". $status;
    if ($status == 'online') {
        $alarmType = 'INFO';
    }
    else {
        $alarmType = 'FAULT';
    }
    $alarmInfo = array (
        'deviceId' => $imei,
        'deviceName' => $deviceName,
        'alarmType' => $alarmType,
        'alarmDescription' => $alarmDescription,
        'alarmStatus' => '',
        'notes' => '',
        'archivedAt' => null,
        'createdAt' => date('Y/m/d H:i:s')
    );
    storeAalrmInfo($alarmInfo);
}

// ---- Fund for Check receive POP information process ----
function storePopInfo($popInfo) {
    print_r($popInfo);     // Mike for test
    global $db;
    // 提取所需的值
    $time = $popInfo['time'];
    $name = $popInfo['name']??'';
    $status = $popInfo['status']??'';
    // 查找或創建新UT
    $query = $db->prepare('SELECT status FROM POP_Info WHERE name = ?');
    $query->execute([$name]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sqlComamnd = 'INSERT INTO POP_Info (name,status) VALUES (?, ?)';
        $query = $db->prepare($sqlComamnd);
        $query->execute([$name, $status]);
        syslog(LOG_INFO, "** pollingTinos ** storePopInfo - $sqlComamnd ($name, $status)");		// Mike for test
    } else {
        $oldStatus = $result['status'];
        if ($oldStatus == $status) 
            return;
        // Status has changed, update status 
        $sqlComamnd = 'UPDATE POP_Info SET status=? WHERE name=?';
        $query = $db->prepare($sqlComamnd);
        $query->execute([$status, $name]);
    }
    // store into event log
    $deviceName = 'POP-'.$name;
    $alarmDescription = $deviceName . " ". $status;
    if ($status == 'online') {
        $alarmType = 'INFO';
    }
    else {
        $alarmType = 'FAULT';
    }
    $alarmInfo = array (
        'deviceId' => $name,
        'deviceName' => $deviceName,
        'alarmType' => $alarmType,
        'alarmDescription' => $alarmDescription,
        'alarmStatus' => '',
        'notes' => '',
        'archivedAt' => null,
        'createdAt' => date('Y/m/d H:i:s')
    );
    storeAalrmInfo($alarmInfo);
}

?>