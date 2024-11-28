<?php

$path= dirname(__FILE__).'/../../../lib/_autoload.php';
require_once(dirname(__FILE__).'/../../../lib/_autoload.php');

$state = [];
$authStateId = \SimpleSAML\Auth\State::saveState($state, 'fidoauth:AuthState');
$state['fidoauth:AuthID'] = $authStateId;
/*
// 定義 API URL
$pqcBoxURL = "https://".$_SERVER['REMOTE_ADDR'].":8080/getToken";

// 呼叫 API: getToken 並獲取 JSON 資訊
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $pqcBoxURL); # URL to post to
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); # return into a variable
// 禁用 SSL 证书验证
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$jsonData = curl_exec( $ch ); # run!
curl_close($ch);
if ($jsonData === false) {
    echo "HTTP request $pqcBoxURL failed. Error was: " . curl_error($ch)."\n";
    syslog(LOG_ERR, "HTTP request $apiUT failed. Error was: " . curl_error($ch));
    return;
}
$data = json_decode($jsonData, true);
echo "HTTP request $pqcBoxURL -> ". print_r($data, true);
*/
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register FIDO Credential</title>
	<link href="./ztadc_files/chunk-vendors.86e0353f.css" rel="stylesheet">
	<link href="./ztadc_files/app.628c8686.css" rel="stylesheet">
	<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/jwt-decode@3.1.2/build/jwt-decode.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/js-jose/0.1.0/jose.min.js"></script>
<!--
	<script src="axios.min.js"></script>
	<script src="jwt-decode.min.js"></script>
	<script src="jose.min.js"></script>
-->
    <script src="fidoLogin.js"></script>
	<style type="text/css">x-vue-echarts{display:block;width:100%;height:100%;min-width:0}</style>
	<style>
		.po-password-strength-bar{
			border-radius:2px;
			transition:all .2s linear;
			height:5px;margin-top:8px}
		.po-password-strength-bar.risky{
			background-color:#f95e68;width:10%}
		.po-password-strength-bar
		.guessable{background-color:#fb964d;width:32.5%}
		.po-password-strength-bar.weak{background-color:#fdd244;width:55%}
		.po-password-strength-bar.safe{background-color:#b0dc53;width:77.5%}
		.po-password-strength-bar.secure{background-color:#35cc62;width:100%}
	</style>
</head>
<body>
<div id="app" data-v-app="" class="login-background">
		<div data-v-f19a27c6="">
			<div>
				<div class="card1">
					<div class="card-header text-center">
						<div class="row justify-content-center align-items-center">
							<div class="col-3">
								<img src="./ztadc_files/xtrust.png" style="width: 200px; height: 110px; border: 0px;">
							</div>
							<div class="col-8">
								<div class="h1">中華電信<br>零信任網路系統</div>
							</div>
						</div>
					</div>
					<div class="card-body">
                        <form id="loginForm">
							<input type="hidden" name="AuthState" value="<?php echo htmlspecialchars($authStateId); ?>" />
							<input type="text" id="user_id" name="user_id" placeholder="請輸入使用者帳號" autocomplete="off" required>
							<button type="button" class="btn btn-block btn-success btn-lg" onclick="LoginByGet()" data-v-c83f01de="">
								FIDO 身份鑑別 
							</button>
                        </form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
