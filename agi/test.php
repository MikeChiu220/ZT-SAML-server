<?php

require_once 'vendor/autoload.php'; // 如果使用 Composer 安裝

use RobRichards\XMLSecLibs\XMLSecurityKey;
// 或者手動加載
// require_once 'libs/xmlseclibs/xmlseclibs.php';

// 創建 XMLSecurityKey 對象來測試
//$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type'=>'private']);
$objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, array('type'=>'private'));

echo "XMLSecLibs 安裝成功!";
?>