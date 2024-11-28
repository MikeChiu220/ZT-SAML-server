<?php

use SimpleSAML\Configuration;
use SimpleSAML\Database;
$config = Configuration::getInstance();
// 讀取資料庫設定
$dsn = $config->getString('database.dsn', '');
$dbUsername = $config->getOptionalString('database.username', null);
$dbPassword = $config->getOptionalString('database.password', '');

$db = new PDO($dsn, $dbUsername, $dbPassword);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Helper function to convert from Base64URL encoding to regular Base64
function base64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

// Helper function to convert from regular Base64 to Base64URL encoding
function base64url_encode($data) {
    return strtr(base64_encode($data), '+/', '-_');
}

// ---- Fund for Check receive POP information process ----
function storeAalrmInfo($alarmInfo) {
    global $db;
    // 提取所需的值
    $id = $alarmInfo['id']??'';
    $deviceId = $alarmInfo['deviceId'];
    $deviceName = $alarmInfo['deviceName'];
    $alarmType = $alarmInfo['alarmType'];
    $alarmDescription = $alarmInfo['alarmDescription']??'';
    $alarmStatus = $alarmInfo['alarmStatus']??'';
    $notes = $alarmInfo['notes']??'';
    $archivedAt = $alarmInfo['archivedAt']??null;
    $createdAt = $alarmInfo['createdAt']??'';
    // 查找或創建新UT
    $query = $db->prepare('SELECT alarmStatus FROM EventLog WHERE deviceId = ? AND createdAt = ?');
    $query->execute([$deviceId, $createdAt]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sqlComamnd = 'INSERT INTO EventLog (deviceId,deviceName,alarmType,alarmDescription,alarmStatus,notes,archivedAt,createdAt)' . 
                                    ' VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
        $query = $db->prepare($sqlComamnd); 
        $query->execute([$deviceId, $deviceName, $alarmType, $alarmDescription, $alarmStatus, $notes, $archivedAt, $createdAt]);
        syslog(LOG_INFO, "** pollingIoT ** storeAalrmInfo - $sqlComamnd ($deviceId, $deviceName, $alarmType, $alarmDescription, $alarmStatus, $notes, $archivedAt, $createdAt)");		// Mike for test
    } else {
        $oldStatus = $result['alarmStatus'];
        if ($oldStatus == $alarmStatus) 
            return;
        // Status has changed, update status 
        $sqlComamnd = 'UPDATE EventLog SET deviceName=?, alarmType=?, alarmDescription=?,' . 
                                            ' alarmStatus=?, notes=?, archivedAt=?, createdAt=? WHERE deviceId = ?';
        $query = $db->prepare($sqlComamnd);
        $query->execute([$deviceName, $alarmType, $alarmDescription, $alarmStatus, $notes, $archivedAt, $createdAt, $deviceId]);
        syslog(LOG_INFO, "** pollingIoT ** storeAalrmInfo - $sqlComamnd ($deviceName, $alarmType, $alarmDescription, $alarmStatus, $notes, $archivedAt, $createdAt, $deviceId)");		// Mike for test
   }
}
?>