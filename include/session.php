<?php
session_name('G7_PBX');
session_start();
//==網頁登入畫面路徑
if(file_exists("index.php")){$indexpath="index.php";}
else{$indexpath="../";}
//==相對tmp檔路徑
if(file_exists("index.php")){$path="";}
else{$path="../";}
//==判斷是否正確
if (is_file($path."tmpdir/".$_SESSION['userkey2'])) {
	//echo  "Exist";
	include("connection.php");//Mysql連結
	include("APIcmd.php");//Asterisk API
	$command = "select * from admin_info where username='".$_SESSION['username']."' and passwd='".$_SESSION['passwd']."'";
	$query = FUN_SQL_QUERY($command, $database);
	$row = FUN_SQL_FETCH_ARRAY($query);
	$Web_Language=$row['language'];
//	echo "Web_Language: $Web_Language! <br>";	

}else{
	//echo "No Exist";
	?><script>parent.location.href="<?php echo $indexpath?>"</script><?php 
}

$MACHINE_NAME=php_uname('m');	// Mike[2019/03/12] Move
$HOST_NAME=php_uname('n');						// Mike[2021/11/08]
$CLOUD_FLAG = strstr($HOST_NAME,"ipscloud");	// /
?>
