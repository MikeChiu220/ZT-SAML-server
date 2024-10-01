	<?php
	//======判斷是否有人在寫資料庫,0有人在寫資料庫不可寫入,1無人在寫可以寫入======//mysql> SELECT GET_LOCK('lock1',10);-> 1,0//mysql> SELECT IS_FREE_LOCK('lock2');-> 1,0//SELECT RELEASE_LOCK('lock2');-> 1,NULL
	$is_write=0;
	for($t=0;$t<300;$t++){
		$command = "SELECT IS_FREE_LOCK('lock') as strlock;";
		$query = FUN_SQL_QUERY($command,$database);
		$row = FUN_SQL_FETCH_ARRAY($query);
		if($row['strlock']==1){
			$is_write=1;
			$command = "SELECT GET_LOCK('lock',360);";
			$query1 = FUN_SQL_QUERY($command,$database);
			break;
		}else{sleep(1);}
	}
	//==============((結束))================
	?>