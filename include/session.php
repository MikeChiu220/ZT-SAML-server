<?php
session_name('G7_PBX');
session_start();
//==�����n�J�e�����|
if(file_exists("index.php")){$indexpath="index.php";}
else{$indexpath="../";}
//==�۹�tmp�ɸ��|
if(file_exists("index.php")){$path="";}
else{$path="../";}
//==�P�_�O�_���T
if (is_file($path."tmpdir/".$_SESSION['userkey2'])) {
	//echo  "Exist";
	include("connection.php");//Mysql�s��
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
