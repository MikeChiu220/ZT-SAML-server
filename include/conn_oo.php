<?php
function FUN_SQL_QUERY($command,$input){
	$sth = $conn->prepare($command);
	$sth->execute($input);
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

function FUN_SQL_CLOSE(){
	$conn = NULL;
}

$data=fopen("/etc/asterisk/res_config_mysql.conf","r") or die("無法讀取res_config_mysql.conf設定檔");
while (!feof($data))
{
	$jason=fgets($data);
	if(preg_match("/dbhost\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBhost=$none[1];
	if(preg_match("/dbname\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBname=$none[1];
	if(preg_match("/dbuser\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBuser=$none[1];
	if(preg_match("/dbpass\s*=\s*(\w+)/",$jason,$none))
		$SQL_DBpass=$none[1];
}
//echo "Connect IP=".$SQL_DBIP.",User=".$SQL_DBuser.",Psw=".$SQL_DBpass.",DB=".$SQL_DBname."<br>";
$SQL_DBdsn = "mysql:host=".$SQL_DBhost.";dbname=".$SQL_DBname;
try
{
    $conn = new PDO($SQL_DBdsn,$SQL_DBuser,$SQL_DBpass);
    $conn->exec("SET CHARACTER SET utf8");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected Successfully";
}
catch(PDOException $e)
{
    echo "Connection failed: ".$e->getMessage();
}
//$database = mysql_connect(trim($SQL_DBIP),trim($SQL_DBuser),trim("222") or die('Could not link to database.');
//mysql_select_db($SQL_DBname, $database) or die('Could not select database.');
//echo $SQL_DBIP."=".$SQL_DBname."=".$SQL_DBuser."=".$SQL_DBpass;

?>