<?php
/* 
 * =============================================================================
 * Connect HIS to get Duty Lit & assign NCD ringing extension group
 * ---------------------------------------------------
 * --- Ver: 1.12.45 ---
 * Mike[2023/08/10] Change using StationDeskNum in database to appent the HIS number
 * --- Ver: 1.09 ---
 * Mike[2022/06/09] Mike[2022/06/28] Debug HIS get duty list assign extension group can't work correct
 * Mike[2022/06/28] Debug get duty list set extension group can't work correct
 * Mike[2022/06/30] Change queue ring group timeout from 60 seconds to 180 seconds
 * =============================================================================
 *
 */
include("connection.php");
include("Function.php");//載入相關function

// ---- Duty Hour define ---- 
const   C_DutyDayStart			= 8;
const   C_DutyNightStart		= 16;
const   C_DutyMidNightStart		= 24;

sendDutyListReq($database);

/*
 * Send Web API to HIS server for get shift assignment
 * Iput:NcdNo = NCD number 
 *		HisUrl = HIS Web API URL
 */
function sendDutyListReq($database)
{
	$command = "SELECT * FROM systemConf";
	$queryId = FUN_SQL_QUERY($command, $database);
	$query_num= FUN_SQL_NUM_ROWS($queryId);
	if ( $query_num ) 
	{
		$row = FUN_SQL_FETCH_ARRAY( $queryId);
		$hisURL=$row["hisURL"]??"";
		$StationID=$row["StationID"]??"";
		$searchTime=$row["searchTime"]??0;
		$StationDeskNum=$row["StationDeskNum"]??"6$StationID001&6$StationID002&6$StationID003";		// Mike[2023/08/10]
//		echo "hisURL=$hisURL,StationID=$StationID<br>"; 			// for test
		if ( $hisURL and $StationID ) 
		{
			// POST Url Process
			$postDataArr = array(
				'searchTime'=>$searchTime,
				'source'=>'CWNC',
			);	
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $hisURL);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postDataArr));
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			$response = curl_exec($ch);
			echo "** sendDutyListReq ** $hisURL($searchTime), rspLen=".strlen($response)."<br>\n";		// Mike[2022/06/27] for debug test
			syslog(LOG_INFO, "** sendDutyListReq ** $hisURL($searchTime), rspLen=".strlen($response));	// Mike[2022/06/27] for debug test
			
			// encode receive duty list
			$rspDutyList = json_decode($response);
	//		print_r($rspDutyList);			// for test

			if ($rspDutyList) {
				$errorMsg = $rspDutyList->errorMsg??'';
				if ($errorMsg)
					return($errorMsg);
				else {
					$rxListCount= count($rspDutyList->jsonDutyList);
					// Update searchTime
					$searchTime = $rspDutyList->jsonSearchTime;
					$command="UPDATE `systemConf` SET searchTime='$searchTime'";
					$query = FUN_SQL_QUERY($command,$database);
					
					// Get current Duty start time
					$HourNow = date("H");
					$DutyStart = date("Ymd");
					if ($HourNow >= C_DutyDayStart and $HourNow < C_DutyNightStart)
						$DutyStart .= " 080000";
					else if ($HourNow >= C_DutyNightStart and $HourNow < C_DutyMidNightStart)
						$DutyStart .= " 160000";
					else
						$DutyStart .= " 000000";
//					echo "Array Count=$rxListCount, DutyStart=$DutyStart<br>\n";								// Mike[2022/06/27] for debug test
					syslog(LOG_INFO, "** sendDutyListReq ** Array Count=$rxListCount, DutyStart=$DutyStart");	// Mike[2022/06/27] for debug test
					// Check update table duty_list
					for ( $i=0; $i< $rxListCount; $i++) {
						$dutyOn    =$rspDutyList->jsonDutyList[$i]->dutyOn;
						$stationBed=$rspDutyList->jsonDutyList[$i]->stationBed;
						$stationBedHeader = substr($stationBed, 0, 3);
//						echo $i.". stationBed=$stationBed($stationBedHeader/$StationID),dutyOn=$dutyOn($DutyStart)<br>\n";	// for test
			//			syslog(LOG_INFO, "** updateNurseSchedule ** $i. stationBed=$stationBed($stationBedHeader/$StationID),dutyOn=$dutyOn($DutyStart)");	// for debug test
						// Check this bed is belone this station or not
						if ( strcmp($stationBedHeader, $StationID) == 0 and  
							 strcmp($dutyOn, $DutyStart) == 0 ) {
							$createTime=$rspDutyList->jsonDutyList[$i]->createTime;
							$dutyOff   =$rspDutyList->jsonDutyList[$i]->dutyOff;
							$number1   =$rspDutyList->jsonDutyList[$i]->number1;
							$number2   =$rspDutyList->jsonDutyList[$i]->number2;
							$number3   =$rspDutyList->jsonDutyList[$i]->number3;
							$nurseId   =$rspDutyList->jsonDutyList[$i]->nurseId;
							$groupName="6$stationBed";
							$bedId = substr($stationBed, 3, 2);
							// 1. Set ringing assignment to Group: 6+$stationBed
							$command="Update `phone` SET normal_rg_assign='".$groupName."' where phoneno=".$bedId;
//							echo "$command<br>";						// for test/////
//							syslog(LOG_INFO, "** updateNurseSchedule ** $command");	// for debug test
							$query = FUN_SQL_QUERY($command,$database);
							$RingAssign = '';
							// ---- { Mike[2022/06/09] Mike[2022/08/09] Modify
//							$MobileNum=substr($number1,2);
							$number1=str_replace("8#","89", $number1);
							if ($number1)
								$RingAssign .= "Local/$number1@default&";
//								$RingAssign .= "Local/89$MobileNum@default&";
//								$RingAssign .= "Local/9$number1@default&";
							// ---- }
							$RingAssign .= $StationDeskNum;		// Mike[2023/08/10] "6".$StationID."001&6".$StationID."002&6".$StationID."003";
/*							if ($number2)
								$RingAssign .= "&$number2";
							if ($number3)
								$RingAssign .= "&$number3";
*/							
							// 2. Check Group: BedId+## is exist or not
//							$command = "select * from `ext_group` where exten='".$bedId."##'";		// Mike[2022/06/09] Modify $bedId## to $groupName
							$command = "select * from `ext_group` where exten='".$groupName."'";	// /
							$qry_record = FUN_SQL_QUERY($command,$database);
							$total_record = FUN_SQL_NUM_ROWS($qry_record);
							if ($total_record) {
								$SetCommand="SET `appdata`='group,$RingAssign,0,180,$groupName,$groupName'";		// Mike[2022/06/28] add ,$groupName
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
//							echo "$command\n";						// for test
							syslog(LOG_INFO, "** updateNurseSchedule ** $command");	// for debug test
							$qry_record = FUN_SQL_QUERY($command,$database);
						}
					}
				}
			}
		}
	}
}

?>