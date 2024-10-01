<?php
/*
 * Modify Note
 * --- Ver: 1.09 ---
 * Mike[2022/06/30] Debug create extension queue the ring type will be set to Linear
 * --- Ver: 1.07 ---
 * Mike[2022/03/28] Debug Extension group common ringing can't work
 * --- Ver: 1.06 ---
 * Mike[2022/01/25]	For using queue function to do Group call
 */
const	C_FIRE_ALARM_EVENT_ID	= 99;		// Mike[2023/08/24]

function Method_Type($method_no){
   $data=fopen("/var/www/conf/license.dat","r") or die("無法讀/var/www/conf/license.dat設定檔");
   $str=fgets($data);
   $str=fgets($data);
   $str1=fgets($data);
   if(preg_match("/\=(\d+)\-(\d+)\-(\d+)\-(\d+)/",$str,$none)){
      $method_01=$none[2];
	  $method_02=$none[4];
	  $method_03=$none[5];
	  $method_04=$none[7];
   }
   if(preg_match("/\=(\d+)\-(\d+)\-(\d+)\s(\d+)\:(\d+)\:(\d+)/",$str1,$none1)){
      $method_mon=$none1[1];
	  $method_day=$none1[2];
	  $method_year=$none1[3];
	  $method_hour=$none1[4];
	  $method_min=$none1[5];
	  $method_sec=$none1[6];	  
   }
   if ($method_no == "1"){
      $sum = $method_02 - 4799 - $method_min;
	  if ($sum == 49) 
         return "true";
	  else	 
         return "false";
   }	  
   if ($method_no == "2"){
      $sum = $method_03 - 6382 - $method_sec;
	  if ($sum == 49) 
         return "true";
	  else	 
         return "false";
   }  
}

function Deny_GroupPhone($database){
//==========上傳更新限撥群組============= Mike[2012/10/31] Del, no used and when add new extension will cause toll group_phone be error clear
//			$command = "select * from group_phone";
//			$qry_phone = FUN_SQL_QUERY($command, $database);
//			$phone_num = FUN_SQL_NUM_ROWS($qry_phone);
//			for($i=0;$i<$phone_num;$i++){//�}�l�C�L
//				$row = FUN_SQL_FETCH_ARRAY( $qry_phone);
//				$tmp_cmd = "select phoneno from phone where toll='".$row['id']."'";
//				$tmp_qry = FUN_SQL_QUERY($tmp_cmd,$database);
//				$tmp_num = FUN_SQL_NUM_ROWS($tmp_qry);
//				$update_str="";
//				for($j=0;$j<$tmp_num;$j++){
//					$tmp_row = FUN_SQL_FETCH_ARRAY($tmp_qry);
//					if($j==0)
//						$update_str.=$tmp_row['phoneno'];
//					else
//						$update_str.=",".$tmp_row['phoneno'];
//				}
//				$command="Update `group_phone` SET phoneno ='".$update_str."' where id='".$row['id']."'";
//				$query = FUN_SQL_QUERY($command,$database);
//			}

}

/*================================================
分機衝突
主要是這四個資料表不能衝突ext_internal(分機), ext_conf(會議室), ext_group(總機&群組撥號), ext_pickup, ext_pstn(路由)
==================================================*/
function PhoneConflict($exten,$state,$database){
	$err_index=0;
	if($state!=1){//除"分機"外,套用此條件
		if($state==4)
			$command = "select * from phone where substring(phoneno,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from phone where phoneno='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){
			$err_index=1;
			echo $command." | Check phone= ".$num."<br>";			// for test
		}
	}
	if($err_index>0) return $err_index;
	
	if($state!=2){//除"會議室"外,套用此條件
		if($state==4)
			$command = "select * from ext_conf where substring(exten,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from ext_conf where exten='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){
			$err_index=2;
			echo $command." | Check ext_conf= ".$num."<br>";			// for test
		}
	}
	if($err_index>0) return $err_index;
	
	if($state!=3){//除"總機&群組"外,套用此條件
		if($state==4)
			$command = "select * from ext_group where substring(exten,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from ext_group where exten='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){
			$err_index=3;
			echo $command." | Check ext_group= ".$num."<br>";			// for test
		}
	}
	
	//select substring(exten,2,length(exten)-2),length(exten) as len from ext_pstn 
	if($state!=4){//除路由外,套用此條件
		$command = "select * from ext_pstn where substring(exten,2,length(exten)-1)=substring('".trim($exten)."',1,length(exten)-1) and substring(exten,1,1)='_'";
		$query = FUN_SQL_QUERY($command,$database);							// |___ Mike[2015/12/21] -2 to -1 ___________________|
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){
			$err_index=5;
			echo $command." | Check ext_pstn= ".$num."<br>";			// for test
		}
	}
//	echo "return index= ".$err_index."<br>";			// for test
	if($err_index>0) return $err_index;
	else return 0;
}

/*================================================
Firewall 設定: Mike[2018/07/24]
$CtrlType="0" -> 關閉Firewall
$CtrlType="1" -> 開啟Firewall
==================================================*/
function Firewall_Ctrl($CtrlType){
	// ---- { Mike[2019/04/30] Modify
	$data=fopen("/etc/os-release","r") or die("Error,Can't open /etc/os-release");
	while (!feof($data)) {
		$jason=fgets($data);
		if(preg_match("/NAME\s*=\s*(.*)/",$jason,$none)) {
			$OS_Ver=$none[1];
			break;
		}
	}
	fclose($data);
//	$OS_VERSION=php_uname('v');		// Mike[2020/04/15]
//	echo "OS Versino:".$OS_Ver."/".$OS_VERSION."<br>";
	
	if ($CtrlType=="0") {
		$Active_action="disable";
		$Start_action="stop";
		exec("sudo fail2ban-client unban --all");	// Mike[2020/02/19] For disable Fire wall can clear ban
	}
	else {
		$Active_action="enable";
		$Start_action="start";
	}
	if (strstr($OS_Ver,"Fedora")!=FALSE) {		// PC
		exec("/usr/bin/sudo /bin/systemctl ".$Active_action." iptables.service");
		exec("/usr/bin/sudo /bin/systemctl ".$Active_action." fail2ban.service");
		exec("/usr/bin/sudo /sbin/service iptables ".$Start_action);
		exec("/usr/bin/sudo /sbin/service fail2ban ".$Start_action);
	}
	elseif (strstr($OS_Ver,"Ubuntu")!=FALSE) {	// Cloud
		exec("sudo service iptables ".$Start_action);
		exec("sudo service fail2ban ".$Start_action);
	}
	else {										// Raspberry pi
//	   	exec("sudo systemctl ".$Active_action." iptables");
		exec("sudo /usr/sbin/logrotate -vf /etc/logrotate.d/asterisk");		// Mike[2020/03/09] Mike[2022/03/11] add /usr/sbin/
		exec("sudo /usr/sbin/logrotate -vf /etc/logrotate.d/rsyslog");		// Mike[2020/03/09]
		exec("sudo /usr/sbin/logrotate -vf /etc/logrotate.d/fail2ban");		// Mike[2020/03/09]
	   	exec("sudo /usr/sbin/service fail2ban ".$Start_action);						// Mike[2022/03/11]
//		exec("sudo /lib/systemd/systemd-sysv-install ".$Active_action." fail2ban");	//
//		exec("sudo systemctl ".$Start_action." fail2ban");							// /
	}
	
/*	if ($CtrlType=="0"){
	  if ($MACHINE_NAME == "x86_64") {	// Mike[2019/03/12] Add check machine, PC Firewall process
		exec("/usr/bin/sudo /bin/systemctl disable iptables.service");
		exec("/usr/bin/sudo /bin/systemctl disable fail2ban.service");
		exec("/usr/bin/sudo /sbin/service iptables stop");
		exec("/usr/bin/sudo /sbin/service fail2ban stop");
	  }
	  else { // Raspberry Pi Firewall process
//	   	exec("sudo systemctl disable iptables");
		exec("sudo /lib/systemd/systemd-sysv-install disable fail2ban");
//	   	exec("sudo service iptable stops");
		exec("sudo systemctl stop fail2ban");
	  }
	}else{
	  if ($MACHINE_NAME == "x86_64") {	// Mike[2019/03/12] Add check machine, PC Firewall process
		exec("/usr/bin/sudo /bin/systemctl enable iptables.service");
		exec("/usr/bin/sudo /bin/systemctl enable fail2ban.service");
		exec("/usr/bin/sudo /sbin/service iptables start");
		exec("/usr/bin/sudo /sbin/service fail2ban start");
	  }
	  else { // Raspberry Pi Firewall process
//	   	exec("sudo systemctl enable iptables");
		exec("sudo /lib/systemd/systemd-sysv-install enable fail2ban");
//	   	exec("sudo service iptable start");
		exec("sudo systemctl start fail2ban");
	  }
	}
*/	// ---- }
}

/*================================================
Generate Random Passowrd: Mike[2019/01/06]
$random_len: 自動產生亂數長度
==================================================*/
function RandomPWD($random_len){
	for ($i=1;$i<=$random_len;$i=$i+1)
	{
	    $c=rand(1,3);      //亂數$c設定三種亂數資料格式大寫、小寫、數字，隨機產生
	    if($c==1){		   //在$c==1的情況下，設定$a亂數取值為97-122(a~z)之間，並用chr()將數值轉變為對應英文，儲存在$b
			$a=rand(97,122);
			$b=chr($a);
		}
		else if($c==2){		//在$c==2的情況下，設定$a亂數取值為65-90(A~Z)之間，並用chr()將數值轉變為對應英文，儲存在$b
			$a=rand(65,90);
			$b=chr($a);
		}
		else				//在$c==3的情況下，設定$b亂數取值為0-9之間的數字
			$b=rand(0,9);


		$random_num=$random_num.$b;	//使用$random_num連接$b
	}
	return($random_num);
}

/*================================================
Write hint.conf for MWI: Mike[2020/03/10]
$random_len: �۰ʲ��Ͷüƪ���
==================================================*/
function WriteHintConf($database){
	$fp=fopen("/etc/asterisk/hint.ael","w+");
	fputs($fp, "context hint-ext\n");
	fputs($fp, "{\n");

	$tmp_cmd = "select `name`,`call-limit`,`type` from sip_buddies";
	$tmp_qry = FUN_SQL_QUERY($tmp_cmd, $database);
	$tmp_num = FUN_SQL_NUM_ROWS($tmp_qry);
	for($j=0;$j<$tmp_num;$j++){
		$row = FUN_SQL_FETCH_ARRAY($tmp_qry);
		$ext_num=$row['name'];           
		if(($row['call-limit']=="2" && (is_numeric($ext_num))) || 
		  ($row['type']=="peer" && strpos($ext_num, ' ')== false)){
			fputs($fp, '   hint(SIP/'.$ext_num.') '.$ext_num.' => NoOp('.$ext_num.');'."\n");  
		}
	}		
	fputs($fp, "   hint(Custom:line1) 999991 => NoOp(999991);\n");
	fputs($fp, "   hint(Custom:line2) 999992 => NoOp(999992);\n");
	fputs($fp, "   hint(Custom:line3) 999993 => NoOp(999993);\n");
	fputs($fp, "   hint(Custom:line4) 999994 => NoOp(999994);\n");
	fputs($fp, "   hint(Custom:line5) 999995 => NoOp(999995);\n");
	fputs($fp, "   hint(Custom:line6) 999996 => NoOp(999996);\n");
	fputs($fp, "};\n");
	fclose($fp);

	$data_reload="/usr/bin/sudo /usr/sbin/asterisk -rx 'ael reload' ";
	exec($data_reload,$report1);
}

/*================================================
Create Queue & Queue member database: Mike[2022/01/25]
$database: database name
$postName: Queue Name
$postType: Ring Type
$postTimeout: Queue timeout
$postExten: Queue Members
==================================================*/
function CreateQueue($database,$postName,$postType,$postTimeout,$postExten)
{
	// 1.Create Queue
	if ($postType=='0')					// Mike[2022/06/30] change = to ==
		$SetStrategy= "strategy='ringall'";
	else
		$SetStrategy= "strategy='linear'";
	$SetName= "name='".$postName."'";
	$SetTimeout= "timeout=".$postTimeout;
	$SetCommand="SET $SetName,$SetTimeout,$SetStrategy,ringinuse=1";	// Mike[2022/08/10] add ringinuse=1 for let the 2nd call can invite	
	$command="INSERT INTO `queue_table` $SetCommand";	
//	echo "$command<br>";							// Mike for test 
	FUN_SQL_QUERY($command,$database);
	// 2.Add Queue Member
	CreateQueueMember($database,$postName,$postType,$postExten);
}
/*================================================
Create Queue member database: Mike[2022/01/25]
$database: database name
$postName: Queue Name
$postType: Ring Type
$postExten: Queue Members
==================================================*/
function CreateQueueMember($database,$postName,$postType,$postExten)
{
	$Members = explode("&",$postExten);
	$SetQName= "queue_name='$postName'";
	$command="DELETE FROM `queue_member_table` WHERE $SetQName";	// Mike[2022/07/01]	
	FUN_SQL_QUERY($command,$database);								// /
//	echo "CreateQueueMember($postName,$postType,$postExten)\n";		// Mike for test 
	syslog(LOG_INFO, "CreateQueueMember($postName,$postType,$postExten)");		// Mike for test 
	for ($j=0; $j<count($Members); $j++) {
		$SetMName= "membername='".$Members[$j]."'";					// Mike[2022/07/01]	add []
		if (strpos($Members[$j],'/'))
			$SetInterface= "interface='".$Members[$j]."'";
		else
			$SetInterface= "interface='SIP/".$Members[$j]."'";
		if ($postType=='0')                   // Mike[2022/03/28]
			$SetPenalty= "penalty=0";
		else
			$SetPenalty= "penalty=$j";
		$SetCommand="SET $SetMName,$SetQName,$SetInterface,$SetPenalty";	
		$command="INSERT INTO `queue_member_table` $SetCommand";	
//		echo "$j. $command\n";							// Mike for test 
		syslog(LOG_INFO, "** CreateQueueMember ** $j. $command");	// for debug test
		FUN_SQL_QUERY($command,$database);
	}
}

/*
 * Send Web API to HIS server for get shift assignment
 * Iput:postUrl = POST Web API URL
 *		postDataArr = Be send data array
 * Date: Mike[2022/08/23]
 */
function postJsonProcess($postUrl,$postDataArr)
{
	// POST Url Process
	$postJosnData = json_encode($postDataArr);
	syslog(LOG_INFO, "** postJsonProcess ** $postUrl, ".print_r($postDataArr, TRUE));		// for test

	if ( ($ch=curl_init($postUrl)) ) {
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, TRUE);		// set to zero for no timeout
//		syslog(LOG_INFO, "** postJsonProcess ** curl_exec($AccessUrl)");		// for test
		$data = curl_exec($ch);
		if (curl_errno ( $ch ))
			syslog(LOG_ERR, "** postJsonProcess: curl_exec Error ** ".curl_errno($ch)."/".curl_error($ch) );
		else {
			$response = json_decode($data);
			return($response);
		}
	}
	else
		syslog(LOG_ERR, "** postJsonProcess: curl_init($postUrl) Error ** ".curl_error($ch) );
	return(NULL);
}
/*
 * Send Web API to HIS server for get shift assignment
 * Iput:postUrl = POST Web API URL
 *		postDataArr = Be send data array
 * Date: Mike[2022/08/23]
 */
function postFusionJsonProcess($postUrl,$postDataArr,$apiUserName,$apiKey)
{
	if ($apiUserName) {
		$nowUTCString = str_replace("+0000", "GMT", gmdate("r"));
		$signing_string = 'x-date: '.$nowUTCString;
		$signature = base64_encode( hash_hmac("sha1", $signing_string, $apiKey, true) );
		$Authorization = 'hmac username="'.$apiUserName.'", algorithm="hmac-sha1", headers="x-date", signature="'.$signature.'"';
		$urlHeader = array( "X-Api-Key:$apiKey",
							"Content-type: application/json",
							"x-date: $nowUTCString",
							"Authorization: $Authorization"	);
	}
	else {
		$urlHeader = array( "X-Api-Key:$apiKey",
							"Content-type: application/json" );
	}
	// POST Url Process
	$postJosnData = json_encode($postDataArr);
	syslog(LOG_INFO, "** postJsonProcess ** $postUrl, urlHeader=".print_r($urlHeader, TRUE).", postDataArr=".print_r($postDataArr, TRUE));		// for test

	if ( ($ch=curl_init($postUrl)) ) {
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $urlHeader);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postJosnData);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, TRUE);		// set to zero for no timeout
//		syslog(LOG_INFO, "** postJsonProcess ** curl_exec($AccessUrl)");		// for test
		$data = curl_exec($ch);
		if (curl_errno ( $ch ))
			syslog(LOG_ERR, "** postJsonProcess: curl_exec Error ** ".curl_errno($ch)."/".curl_error($ch) );
		else {
			$response = json_decode($data);
			return($response);
		}
	}
	else
		syslog(LOG_ERR, "** postJsonProcess: curl_init($postUrl) Error ** ".curl_error($ch) );
	return(NULL);
}
/*
 * Update NCD Ring assignment
 * Iput: BedNo = NCD Phone Number 
 *		 RingAssign = Assign Ring Number
 *		 StationDeskNum = Common ringing Desk Phone number
 * Date: Mike[2023/08/03]
 */
function UpdateBedRingAssign($BedNo, $RingAssign, $StationDeskNum)
{
    global $database;
	$groupNumber="$BedNo##";
	// 1. Set ringing assignment to Group: 6+$stationBed
	$command="Update `phone` SET normal_rg_assign='$groupNumber' where phoneno='$BedNo'";
//	syslog(LOG_INFO, "** UpdateBedRingAssign ** ".$command);							// for test
	$qry_update=FUN_SQL_QUERY($command, $database);

	// 2. Set RingAssign = StationDeskNum + response phone number
	// Mike[2023/08/01] Modify; Check response phone number is phone number or not
	$command = "SELECT phoneno FROM `phone` where phoneno='$RingAssign'";
	$qry_phone = FUN_SQL_QUERY($command,$database);
	$total_phone = FUN_SQL_NUM_ROWS($qry_phone);
	if ($total_phone == 0) {								// Mike[2023/08/08] total_record -> total_phone
		$command = "SELECT exten FROM `ext_group` where name='$RingAssign'";	// ---- { Mike[2023/08/08]
		$qry_phone = FUN_SQL_QUERY($command,$database);
		$total_phone = FUN_SQL_NUM_ROWS($qry_phone);
		if ($total_phone) {										// If the RingAssign is group name, 
			$row = FUN_SQL_FETCH_ARRAY($qry_phone);
			$RingAssign = "Local/".$row['exten']."@default";	// yes, set ring number = Local/{Group exten number}@default for loop back dial plan process
		}
		else																	// ---- }
			$RingAssign = "Local/$RingAssign@default";		// no, set ring number = Local/{response number}@default for loop back dial plan process
	}

	if ($StationDeskNum)
		$RingAssign = "$StationDeskNum&$RingAssign";

	// 3. Check Group: BedId+## is exist or not for update or insert the ext_group of this Bed
	$command = "SELECT appdata,name FROM `ext_group` where exten='$groupNumber'";			// Mike[2023/08/01] change * to appdata,name
	$qry_record = FUN_SQL_QUERY($command,$database);
	$total_record = FUN_SQL_NUM_ROWS($qry_record);
//	syslog(LOG_INFO, "** UpdateBedRingAssign ** $command -> $total_record");		// for test
	if ($total_record) {
		// ---- { Mike[2023/08/01] Modify for keep origine group setting type, time & name
		$row = FUN_SQL_FETCH_ARRAY($qry_record);
		$groupName = $row['name'];
		$appData= explode(",",$row['appdata']);
		$type	= $appData[2];
		$timeout= $appData[3];
		$SetCommand="SET `appdata`='group,$RingAssign,$type,$timeout,$groupNumber,$groupName'";
		// ---- }
		$command="Update `ext_group` $SetCommand where exten='".$groupNumber."'";
//		syslog(LOG_INFO, "** UpdateBedRingAssign ** CreateQueueMember(database,$groupNumber,$type,$RingAssign)");		// for test
		CreateQueueMember($database,$groupNumber,$type,$RingAssign);
	}
	else {
		$SetExten= "exten='$groupNumber'";
		$SetName= "name='$groupNumber'";					// Mike[2022/06/09] Modify $bedId Ring Assignment to $groupNumber
		$SetAppData= "appdata ='group,$RingAssign,0,180,$groupNumber,$groupNumber'";			// Mike[2022/06/28] add ,$groupNumber
		$SetCommand="SET app='AELSub', context ='groupcalls',$SetExten,$SetAppData,$SetName";	
		$command="INSERT INTO `ext_group` $SetCommand";
//		syslog(LOG_INFO, "** UpdateBedRingAssign ** CreateQueue(database,$groupNumber,'0','180',$RingAssign)");		// for test
		CreateQueue($database,$groupNumber,'0','180',$RingAssign);
	}
	syslog(LOG_INFO, "** UpdateBedRingAssign ** $command");	// for debug test
	$qry_record = FUN_SQL_QUERY($command,$database);
}
/*
 * Fusion Event Process - insert into table:ncd_active_event and generate call
 * Iput: EventId = Fusion事件類型 (0= 求救, 1= 進入通知, 2= 離開通知, 3=Sensor異常通知, 4=跌倒偵測, 5=尿溼提醒, 6=離床提醒, 7=停留逾時警示, 8=綜合指標, 99=Fire Alarm)
 *		 EventNo = C_FUSION_EVENT
 *		 objectId = 發生事件的對象編碼
 *		 taskMessage = Fusion 事件訊息
 *		 taskId = Fusion事件ID (解除事件需用此 Id)
 *		 FusionURL = Fusion Server URL
 *		 FusionPort = 
 *		 callout = 是否需要外撥; 0: 不需要, 1: 需要
 *		 outLine = callout=1 時可指定要外撥哪條線路電話, 外撥號(外撥哪條線路 0~9 )
 * Date: Mike[2023/08/03]
 */
function FusionEventProc($EventId, $EventNo, $objectId, $taskMessage, $taskId, $FusionURL, $FusionPort, $callout, $outLine)
{
    global $database;
	
	$NcdIPAddr = "";
	if ( $EventId == C_FIRE_ALARM_EVENT_ID && $objectId == C_FIRE_ALARM_EVENT_ID ) {
		$command = "SELECT ncd_no,taskId, proc_fg FROM ncd_active_event WHERE ncd_no='Fusion' AND taskId=99 AND proc_fg IS NULL";
		$queryId = FUN_SQL_QUERY($command, $database);
		$query_num= FUN_SQL_NUM_ROWS($queryId);
//		syslog(LOG_INFO, "** fusionPushNotify ** $command->$query_num");								// for test
		if ( $query_num ) 		// Already during call, skip process
			return;
		$taskId = C_FIRE_ALARM_EVENT_ID;
		$query_num = 0;
	}
	else {
		$command = "SELECT P.phoneno, S.ipaddr, S.callerid FROM phone AS P JOIN sip_buddies AS S ON P.phoneno = S.name where P.objectId='$objectId'";
		$query = FUN_SQL_QUERY($command,$database);
		$query_num = FUN_SQL_NUM_ROWS($query);
//		syslog(LOG_INFO, $command." => ".$query_num);            // Mike for test
	}
	// 1. Check objectId has mach NCD or not
	if ($query_num == 1) {	// objectId has mach NCD, Send event request to IP-NCD for generate call
		$row = FUN_SQL_FETCH_ARRAY($query);
		$NcdNo=$row['phoneno'];
		$NcdIPAddr = $row['ipaddr'];
	}
	if ($NcdIPAddr) {	// Yes, Send event to NCD for generate call by NCD
		$url = "http://$NcdIPAddr/content.cgi?_method_=ncd6.htm?device=96?status=$EventNo";
		file_get_contents($url);
		$CalledNo="";
	}
	else {				// No, objectId without mach NCD, direct generate call to console
		$NcdNo='Fusion';
		$command = "SELECT exten FROM ext_group WHERE context='worktime'";
		$query = FUN_SQL_QUERY($command,$database);
		$query_num = FUN_SQL_NUM_ROWS($query);
		if ($query_num) {
			$row = FUN_SQL_FETCH_ARRAY($query);
			$CalledNo=$row['exten'];
		}
	}
//	syslog(LOG_INFO, "NcdNo=$NcdNo, CalledNo=$CalledNo");            // Mike for test
	// 2. insert event into active event database
	$objDateTime = new DateTime('NOW');
	$startTime = $objDateTime->format('Y-m-d H:i:s');
	$command = "INSERT INTO ncd_active_event (active_event,ncd_no,called_no,start_tm,ncd_name,taskId,FusionURL,FusionPort)";
	$command.= "VALUES ($EventNo,'$NcdNo','".$CalledNo."','$startTime','$taskMessage',$taskId,'$FusionURL',$FusionPort)";
	syslog(LOG_INFO, "** fusionPushNotify ** ".$command);								// for test
	$qry_phone = FUN_SQL_QUERY($command, $database);
	// 3. Check Send show Led panel message control 
	CheckSendLedPanel();
	// 4. Cehck Dial out process
	if ($callout) {
		if ($outLine > 0 && $outLine < 10)
			$dialName = "Fusion_0$outLine";
		else
			$dialName = "Fusion_01";
		$command = "SELECT exten FROM ext_group WHERE name='$dialName'";
		$query = FUN_SQL_QUERY($command,$database);
		$query_num = FUN_SQL_NUM_ROWS($query);
		if ($query_num) {
			$row = FUN_SQL_FETCH_ARRAY($query);
			$CalledNo=$row['exten'];
		}
	}
	if ($CalledNo) {
		$tempCallFileName = "/tmp/Public$taskId.call";
		$autoCallFloder = "/var/spool/asterisk/outgoing/";
		$contents = "Channel: Local/$CalledNo@default\r\nWaitTime: 0\r\nCallerID: '$taskMessage'<Fusion>\r\nContext: ael-NursePublicAnswer\r\nExtension: $objectId\r\nSet: EventNo=$EventId";
		file_put_contents($tempCallFileName, $contents);     // Save our content to the file.
		system("mv $tempCallFileName $autoCallFloder" );
	}
}	
?>