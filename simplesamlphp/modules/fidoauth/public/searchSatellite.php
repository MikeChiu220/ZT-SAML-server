<?php
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 定義 API URL
$keeptrackURL = 'https://api.keeptrack.space/v1/sat/';
$startSatId = 44000;
$endSatId = 56100;

// 呼叫 API: 獲取 Satelite JSON 資訊
for ( $sateliteId= $startSatId; $sateliteId<= $endSatId; $sateliteId++ ) {
  $ch = curl_init();
	$sateliteApi = $keeptrackURL . $sateliteId;
	curl_setopt($ch, CURLOPT_URL, $sateliteApi); # URL to post to
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
	// 禁用 SSL 证书验证
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$jsonData = curl_exec( $ch ); # run!
	curl_close($ch);
	if ($jsonData === false) {
		echo "HTTP request $sateliteApi failed. Error was: " . curl_error($ch)."\n";
//		syslog(LOG_ERR, "HTTP request $sateliteApi failed. Error was: " . curl_error($ch));
		return;
	}
	$data = json_decode($jsonData, true);
//	print_r($data);     // Mike for test
	// 檢查 Satelite 資料是否屬於 ONEWEB
	if ($data) {
    $owner = $data['owner']??'';
    $name =  $data['name']??'';
	  echo "$sateliteId. owner=" . $owner. ", name=" . $name . "\n";
    if ( $owner == 'ONEWEB' || $owner == 'ONEWEBN' )
		  storeSateliteInfo($data);
	}
/*
*/
}


// ---- For Check receive Satelite information process ----
function storeSateliteInfo($sateliteInfo) {
//    print_r($sateliteInfo);     // Mike for test

    global $db;
    // 提取所需的值
	$satId = $sateliteInfo['satId'];
/*
	$name = $sateliteInfo['name']??'';
	$altId = $sateliteInfo['altId']??'';
	$altName = $sateliteInfo['altName']??'';
	$tle1 = $sateliteInfo['tle1']??'';
	$tle2 = $sateliteInfo['tle2']??'';
	$bus = $sateliteInfo['bus']??'';
	$country = $sateliteInfo['country']??'';
	$manufacturer = $sateliteInfo['manufacturer']??'';
	$mission = $sateliteInfo['mission']??'';
	$owner = $sateliteInfo['owner']??'';
	$rcs = $sateliteInfo['rcs']??'';
	$vmag = $sateliteInfo['vmag']??'';
	$type = $sateliteInfo['type']??'';
	$intlDes = $sateliteInfo['intlDes']??'';
	$inc = $sateliteInfo['inc']??'';
	$raan = $sateliteInfo['raan']??'';
	$period = $sateliteInfo['period']??'';
*/
    // 查找或創建新UT
    $query = $db->prepare('SELECT satId FROM satellite_Info WHERE satId = ?');
    $query->execute([$satId]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $sqlComamnd = "INSERT INTO satellite_Info SET satId = $satId";
        $query = $db->query($sqlComamnd);
        echo "$sqlComamnd\n";
    }
}

?>