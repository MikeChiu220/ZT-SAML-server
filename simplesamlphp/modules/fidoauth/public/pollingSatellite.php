<?php
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');
require_once('common.php');

// 定義 API URL
$keeptrackURL = 'https://api.keeptrack.space/v1/sat/';
$startSatId = 44000;
$endSatId = 56100;
$procOffsetNum = 0;
$searchId = $startSatId;
const   C_PROC_LIMIT_NUM  = 10;
const   C_START_SATELLITE_ID  = 44000;
const   C_END_SATELLITE_ID    = 56100;

global $searchId, $keeptrackURL;
$sqlComamnd = "SELECT satId FROM satellite_Info";
$queryResult = $db->query($sqlComamnd);
if ($queryResult) {
// Check for query errors
	$totalSateliteNum = $queryResult->rowCount();
//  echo "$sqlComamnd\n ==> $totalSateliteNum\n";
  syslog(LOG_INFO, "Polling Satelite: $totalSateliteNum");
  $queryResult->closeCursor();
  $queryResult = $db->prepare( "SELECT satId FROM satellite_Info ORDER BY satId LIMIT ". C_PROC_LIMIT_NUM . " OFFSET :numOffset" );
	for ( ; ; ) {
    // 1. Polling C_PROC_LIMIT_NUM Satelite Status in the database
    $queryResult->bindValue(':numOffset', intval($procOffsetNum), PDO::PARAM_INT);
    if ( $queryResult->execute() )
    {
			$totalEvents = $queryResult->rowCount();
//      echo "$sqlComamnd\n ==> $totalEvents\n";
			while ($row = $queryResult->fetch(PDO::FETCH_ASSOC)) {
				$sateliteId = $row['satId'];
				$sateliteApi = $keeptrackURL . $sateliteId;
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $sateliteApi); # URL to post to
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
				// 禁用 SSL 证书验证
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
				$jsonData = curl_exec( $ch ); # run!
				curl_close($ch);
				if ($jsonData === false) {
					echo "HTTP request $sateliteApi failed. Error was: " . curl_error($ch)."\n";
					syslog(LOG_ERR, "HTTP request $sateliteApi failed. Error was: " . curl_error($ch));
					return;
				}
				$data = json_decode($jsonData, true);
        $owner = $data['owner']??'';
        $name =  $data['name']??'';
//    	  echo "$sateliteId. owner=" . $owner. ", name=" . $name . "\n";
				storeSateliteInfo($data);
			}
      $queryResult->closeCursor();
		}
    // 2. Search Satelite for check new add
    checcSearchSatelite();
    
    // 3. Sleep 5 sechnds for polling next 
		sleep(5);			// 5 second
    $procOffsetNum += C_PROC_LIMIT_NUM;
    if ($procOffsetNum >= $totalSateliteNum ) {
      $procOffsetNum = 0;
      syslog(LOG_INFO, "Polling Satelite finish Once");
    }
	}
}


// ---- For Check receive Satelite information process ----
function storeSateliteInfo($sateliteInfo) {
//    print_r($sateliteInfo);     // Mike for test

    global $db;
    // 提取所需的值
	$satId = $sateliteInfo['satId'];
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

	$sqlComamnd = "UPDATE satellite_Info SET name='$name'," .
									"altId='$altId'," .
									"altName='$altName'," .
									"tle1='$tle1'," .
									"tle2='$tle2'," .
									"bus='$bus'," .
									"country='$country'," .
									"manufacturer='$manufacturer'," .
									"mission='$mission'," .
									"owner='$owner'," .
									"rcs='$rcs'," .
									"vmag='$vmag'," .
									"type='$type'," .
									"intlDes='$intlDes'," .
									"inc='$inc'," .
									"raan='$raan'," .
									"period='$period'" .
									" WHERE satId='$satId';";
//	echo "$sqlComamnd\n";
  $query = $db->query($sqlComamnd);
}

// ---- For Check receive Satelite information process ----
function checcSearchSatelite() {
    global $db, $searchId, $keeptrackURL;
  
    $query = $db->prepare('SELECT satId FROM satellite_Info WHERE satId = ?');
    $query->execute([$searchId]);
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
      $ch = curl_init();
    	$sateliteApi = $keeptrackURL . $searchId;
    	curl_setopt($ch, CURLOPT_URL, $sateliteApi); # URL to post to
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
    	// 禁用 SSL 证书验证
    	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    	$jsonData = curl_exec( $ch ); # run!
    	curl_close($ch);
    	if ($jsonData === false) {
    		echo "HTTP request $sateliteApi failed. Error was: " . curl_error($ch)."\n";
    //	syslog(LOG_ERR, "HTTP request $sateliteApi failed. Error was: " . curl_error($ch));
    		return;
    	}
    	$data = json_decode($jsonData, true);
    //	print_r($data);     // Mike for test
    	// 檢查 Satelite 資料是否屬於 ONEWEB
    	if ($data) {
        $owner = $data['owner']??'';
        $name =  $data['name']??'';
    // echo "$searchId. owner=" . $owner. ", name=" . $name . "\n";
        if ( $owner == 'ONEWEB' || $owner == 'ONEWEBN' ) {
          $sqlComamnd = "INSERT INTO satellite_Info SET satId = $satId";
          $query = $db->query($sqlComamnd);
        }
    	}
    }
    $searchId += 1;
    if ( $searchId > C_END_SATELLITE_ID )
      $searchId = C_START_SATELLITE_ID;
}

?>