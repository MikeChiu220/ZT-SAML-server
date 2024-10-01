<?php
function API_command($command){
	$timeout=null;
	$socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout);
	if( $socket ){
	   fputs($socket, "Action: Login\r\n");
	   fputs($socket, "UserName: hybrex_G7\r\n");
	   fputs($socket, "Secret: hybrex_G7xie\r\n\r\n");
	   fputs($socket, "ACTION: COMMAND\r\n");
	   fputs($socket, "command: $command\r\n\r\n");
	   fputs($socket, "Action: Logoff\r\n\r\n");
	   $data=array();
	   while (!feof($socket)) {
		   array_push($data,fgets($socket, 8192));
	   }
	   fclose($socket);
		return $data;
	}   
	return;			// Mike[2018/11/03] test not return when conenct error
}
function API_wakeup($exten, $WakeupTime){
	$timeout=null;
	$socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout);
	if( $socket ){
	   fputs($socket, "Action: Login\r\n");
	   fputs($socket, "UserName: hybrex_G7\r\n");
	   fputs($socket, "Secret: hybrex_G7xie\r\n\r\n");
	   fputs($socket, "ACTION: Originate\r\n");
	   fputs($socket, "Channel: Local/999@ael-wakeup_call-main\r\n");
	   fputs($socket, "Context: hint-ext\r\n");
	   fputs($socket, "Exten: $exten\r\n");
	   fputs($socket, "Priority: 1\r\n");
	   fputs($socket, "Timeout: 30000 \r\n");
	   fputs($socket, "Variable: WTIME=$WakeupTime,CALLER=$exten\r\n");
	   fputs($socket, "Callerid: *62\r\n\r\n");
	   fputs($socket, "Action: Logoff\r\n\r\n");
	   $data=array();
	   while (!feof($socket)) {
		   array_push($data,fgets($socket, 8192));
	   }
	   fclose($socket);
		return $data;
	}   
	return;			// Mike[2018/11/03] test not return when conenct error
}
function API_command1($command){
	$timeout=null;
	$socket = fsockopen("127.0.0.1","5038", $errno, $errstr, $timeout);
	if( $socket ){	// Mike[2018/11/03] Add check socket connect success or not
		fputs($socket, "Action: Login\r\n");
		fputs($socket, "UserName: hybrex_G7\r\n");
		fputs($socket, "Secret: hybrex_G7xie\r\n\r\n");
		fputs($socket, "Action: $command\r\n\r\n");
		fputs($socket, "Action: Logoff\r\n\r\n");
		$data=array();
		while (!feof($socket)) {
			array_push($data,fgets($socket, 8192));
		}
		fclose($socket);
		return $data;
	}
	return;			// Mike[2018/11/03] test not return when conenct error
}

function API_GetRegInfo(){	// Mike[2021/03/18]
	$sip_user=array();
	$reg_user=array();
	unset($sip_user);
	unset($reg_user);
	$sip_user=API_command("sip show peers");
	$index=0;
	for($k=0;$k<sizeof($sip_user);$k++){
		if(preg_match("/^Output: (\w+)\/(\w+).+(OK)/",$sip_user[$k],$reg_none))	{	// / $reg_none[1]=Name, $reg_none[2]=username,$reg_none[3]=OK
			$reg_user[$index]=$reg_none[1];
			$index+=1;
			unset($peer_info);
			$peer_info=API_command("sip show peer $reg_none[1]");
			for($line=0;$line<sizeof($peer_info);$line++) {
//			echo "<br>$peer_info[$line] => ";
				if(preg_match("/Useragent.+:(.*)/",$peer_info[$line],$useragent)) {
//					print_r($useragent);
					if( strstr($useragent[1],"ip-ncd") )
						$command = "UPDATE `phone` SET `phone_type`=1 where phoneno ='".$reg_none[1]."'";
					else
						$command = "UPDATE `phone` SET `phone_type`=0 where phoneno ='".$reg_none[1]."'";
					$qry_phone = FUN_SQL_QUERY($command, $database);
				}
			}
		}
	}
//	print_r($reg_user);
	return $reg_user;
}
?>