<?php
// --- Get Device Register Domain name Mike[2019/03/07] ---
$tmp_cmd = "select * from `admin_domain` where `id` = 999999";
$tmp_qry = FUN_SQL_QUERY($tmp_cmd, $database);
$tmp_num = FUN_SQL_NUM_ROWS($tmp_qry);
$regDomain="";
$ssdpCertificate="";
$webCertificate="";
if ($tmp_num <> 0)
{
	$row = FUN_SQL_FETCH_ARRAY($tmp_qry);	              
	$regDomain=$row['domain'];
	if ( substr($row['ip'],0,1)=="1" )
		$ssdpCertificate="checked";
	if ( substr($row['ip'],1,1)=="1" )
		$webCertificate="checked";
}

	//== 讀取外網IP Netmask 資訊 ===
	$WIPAddr=preg_split("/[\.\/]+/",$_SERVER['SERVER_ADDR']);  		// Mike[2023/10/27] 外網IP
	$MACHINE_NAME=php_uname('m');	// Mike[2018/11/16]
	if ($MACHINE_NAME == "x86_64")
	{
		//== Get LAN interface name ==
		$command = '/sbin/ifconfig -s';
		exec($command,$output,$return_var);
		$WAN= strtok($output[1], ' ');
		$if_cfg_="/etc/sysconfig/network-scripts/ifcfg-";//ip設定檔
		if( file_exists($if_cfg_.$WAN) ) {				// Mike[2023/08/21]
			$eth1_ary= parse_ini_file($if_cfg_.$WAN);
			$WIPAddr=explode(".",$eth1_ary["IPADDR"]);  //外網IP
		}
	}
	else
	{
		// ---- { Mike[2023/04]24] Modify
		$WIPAddr=preg_split("/[\.\/]+/",$_SERVER['SERVER_ADDR']);  		//外網IP
/*		$if_cfg="/etc/dhcpcd.conf";//ip設定檔
		$eth1_ary= parse_ini_file($if_cfg);
		$WIPAddr=preg_split("/[\.\/]+/",$eth1_ary["static ip_address"]);  //外網IP
*/		// ---- }
	}
	
if ("$regDomain"=="") {			// Mike[2020/02/12] Move & modify
	$connectLocation=$WIPAddr[0].'.'.$WIPAddr[1].'.'.$WIPAddr[2].'.'.$WIPAddr[3];
	$Intranet_IP="";
}
else {
	$connectLocation=$regDomain;
	$Intranet_IP=$WIPAddr[0].'.'.$WIPAddr[1].'.'.$WIPAddr[2].'.'.$WIPAddr[3];
}
?>
