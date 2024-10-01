<?php
	/*
	 * Modify Note
	 */
	include("connection.php");
	include("ncd_const.php");
	include("APIcmd.php");
	include("Function.php");
	$HIS_URL = $_GET['HIS_URL']??$argv[1];
	$StationID = $_GET['StationID']??$argv[2];
	$BedNo = $_GET['Bed']??$argv[3]??"";
	$StationDeskNum = $_GET['StationDeskNum']??$argv[4]??"";
//	syslog(LOG_INFO, "** GetHisAssign ** HIS_URL=$HIS_URL,StationID=$StationID,BedNo=$BedNo");		// for test

	$AccessUrl = $HIS_URL.'?BED='.$BedNo.'&BRN='.$StationID;
	// GET Url Process
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $AccessUrl);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//	curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv2 );	// Mike[2021/09/14] CURL_SSLVERSION_SSL, 2		// Mike[2023/06/21] delete for HTTPS test
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE );
	curl_setopt($ch, CURLOPT_TIMEOUT, 1);	// Mike[2022/01/22] set to zero for no timeout
	$data = curl_exec($ch);
	
	print_r($data);
	$response = json_decode($data);
	syslog(LOG_INFO, "** GetHisAssign ** Response Phone=".$response->Phone);		// for test	
	if ( $response->Phone ) {
		// ---- [ Mike[2023/08/03] Move into Sub.
		UpdateBedRingAssign($BedNo, $response->Phone, $StationDeskNum);
/*		$groupName="$BedNo##";
		// 1. Set ringing assignment to Group: 6+$stationBed
		$command="Update `phone` SET normal_rg_assign='$groupName' where phoneno='$BedNo'";
//		syslog(LOG_INFO, "** GetHisAssign ** ".$command);							// for test
		$qry_update=FUN_SQL_QUERY($command, $database);

		// 2. Set RingAssign = StationDeskNum + response phone number
		// Mike[2023/08/01] Modify; Check response phone number is phone number or not
		$command = "select phoneno from `phone` where phoneno='".$response->Phone."'";
		$qry_phone = FUN_SQL_QUERY($command,$database);
		$total_phone = FUN_SQL_NUM_ROWS($qry_phone);
		if ($total_record)
			$AssignRingNum = $response->Phone;						// yes, set ringing number = response number
		else
			$AssignRingNum = "Local/".$response->Phone."@default";	// no, set ring number = Local/{response number}@default for loop back dial plan process
		$RingAssign = "$StationDeskNum&$AssignRingNum";
//		$RingAssign = "$StationDeskNum&".$response->Phone;

		// 3. Check Group: BedId+## is exist or not for update or insert the ext_group of this Bed
		$command = "select appdata,name from `ext_group` where exten='$groupName'";			// Mike[2023/08/01] change * to appdata,name
		$qry_record = FUN_SQL_QUERY($command,$database);
		$total_record = FUN_SQL_NUM_ROWS($qry_record);
		syslog(LOG_INFO, "** GetHisAssign ** $command -> $total_record");		// for test
		if ($total_record) {
			// ---- { Mike[2023/08/01] Modify
			$row = FUN_SQL_FETCH_ARRAY($qry_record);
			$group_name = $row['name'];
			$appData= explode(",",$row['appdata']);
			$phoneno= $appData[1];
			$type	= $appData[2];
			$timeout= $appData[3];
			$name	= $appData[5];
			$SetCommand="SET `appdata`='group,$RingAssign,$type,$timeout,$groupName,$name'";
//			$SetCommand="SET `appdata`='group,$RingAssign,0,180,$groupName,$groupName'";		// Mike[2022/06/28] add ,$groupName
			// ---- }
			$command="Update `ext_group` $SetCommand where exten='".$groupName."'";
			CreateQueueMember($database,$groupName,'0',$RingAssign);
		}											// |___ Mike[2022/06/28] Remove '180',
		else {
			$SetExten= "exten='".$groupName."'";
			$SetName= "name='".$groupName."'";					// Mike[2022/06/09] Modify $bedId Ring Assignment to $groupName
			$SetAppData= "appdata ='group,$RingAssign,0,180,$groupName,$groupName'";			// Mike[2022/06/28] add ,$groupName
			$SetCommand="SET app='AELSub', context ='groupcalls',$SetExten,$SetAppData,$SetName";	
			$command="INSERT INTO `ext_group` $SetCommand";
			CreateQueue($database,$groupName,'0','180',$RingAssign);
		}
		syslog(LOG_INFO, "** GetHisAssign ** $command");	// for debug test
		$qry_record = FUN_SQL_QUERY($command,$database);
*/		// ---- ]
	}

?>