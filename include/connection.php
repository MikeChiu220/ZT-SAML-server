<?php
// ---- Feature Version Define ---- Mike[2023/03/06] Move from session.php
const   C_FEATURE_NCS			= 1;
const   C_FEATURE_HOTEL			= 2;
const   C_FEATURE_IPPBX			= 3;
const   C_FEATURE_FUSION_GATEWAY= 4;
const   C_FEATURE_FUSION_NCS	= 5;
const   C_FEATURE_ENVIRCTRL		= 6;

function FUN_SQL_QUERY($command,$database){
	$result=mysqli_query($database,$command);
	if ($result <> FALSE) {
//		echo "mysqli_query: $command Sucess! <br>";	
//		syslog(LOG_INFO, "mysqli_query: $command Sucess!");	// for test
		return($result);
	}
	else {
		echo "mysqli_query Fail!:<br>'$command'<br> **** ".mysqli_error($database)." **** <br>";
		syslog(LOG_ERR, "mysqli_query Fail!: '$command'".mysqli_error($database));	// for test
		return(FALSE);
	}
}

function FUN_SQL_FETCH_ARRAY($query_result){
	$result=mysqli_fetch_array($query_result,MYSQLI_ASSOC);
//	if ($result==NULL)
//		echo "mysqli_fetch_array return NULL!<br>";	
	return($result);
}

function FUN_SQL_NUM_ROWS($query_result){
	return(mysqli_num_rows($query_result));
}

$data=fopen("/etc/asterisk/res_config_mysql.conf","r") or die("無法讀取res_config_mysql.conf設定檔");
while (!feof($data))
{
	$jason=fgets($data);
	if(preg_match("/dbhost\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBIP=$none[1];
	if(preg_match("/dbname\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBname=$none[1];
	if(preg_match("/dbuser\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBuser=$none[1];
	if(preg_match("/dbpass\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBpass=$none[1];
}
//echo "Connect IP=".$SQL_DBIP.",User=".$SQL_DBuser.",Psw=".$SQL_DBpass.",DB=".$SQL_DBname."<br>";
$database = mysqli_connect(trim($SQL_DBIP),trim($SQL_DBuser),trim($SQL_DBpass),trim($SQL_DBname));
if (mysqli_connect_errno()) {
    die("Connect failed: ".mysqli_connect_error());
    exit();
}
//$database = mysql_connect(trim($SQL_DBIP),trim($SQL_DBuser),trim("222") or die('Could not link to database.');
//mysql_select_db($SQL_DBname, $database) or die('Could not select database.');
//echo $SQL_DBIP."=".$SQL_DBname."=".$SQL_DBuser."=".$SQL_DBpass;

?>