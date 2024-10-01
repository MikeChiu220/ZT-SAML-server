<?php

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
//			for($i=0;$i<$phone_num;$i++){//開始列印
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
	//substring(exten,1,length('".$phoneno."'))
	$err_index=0;
	if($state!=1){//除"分機"外,套用此條件
		if($state==4)
			$command = "select * from phone where substring(phoneno,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from phone where phoneno='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){$err_index=1;}
	}
	if($err_index>0) return $err_index;
	
	if($state!=2){//除"會議室"外,套用此條件
		if($state==4)
			$command = "select * from ext_conf where substring(exten,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from ext_conf where exten='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){$err_index=2;}
//		echo $command." | Check ext_conf= ".$num."<br>";			// for test
	}
	if($err_index>0) return $err_index;
	
	if($state!=3){//除"總機&群組"外,套用此條件
		if($state==4)
			$command = "select * from ext_group where substring(exten,1,length('".trim($exten)."'))='".trim($exten)."'";
		else
			$command = "select * from ext_group where exten='".trim($exten)."'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){$err_index=3;}
//		echo $command." | Check ext_group= ".$num."<br>";			// for test
	}
	
	//select substring(exten,2,length(exten)-2),length(exten) as len from ext_pstn 
	if($state!=4){//除路由外,套用此條件
		$command = "select * from ext_pstn where substring(exten,2,length(exten)-2)=substring('".trim($exten)."',1,length(exten)-2) and substring(exten,1,1)='_'";
		$query = FUN_SQL_QUERY($command,$database);
		$num=FUN_SQL_NUM_ROWS($query);
		if($num>0){$err_index=5;}
//		echo $command." | Check ext_pstn= ".$num."<br>";			// for test
	}
//	echo "return index= ".$err_index."<br>";			// for test
	if($err_index>0) return $err_index;
	else return 0;
}
?>