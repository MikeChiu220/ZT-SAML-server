<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register FIDO Credential</title>
	<link href="./ztadc_files/chunk-vendors.86e0353f.css" rel="stylesheet">
	<link href="./ztadc_files/app.628c8686.css" rel="stylesheet">
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
                        <form id="registerForm">
                        <input type="text" id="user_id" name="user_id" placeholder="請輸入使用者帳號" autocomplete="off" required>
                        <input type="text" id="display_name" name="display_name" placeholder="請輸入使用者顯示名稱" autocomplete="off">
                        <button type="button" class="btn btn-block btn-success btn-lg" onclick="registerFIDO()" data-v-c83f01de="">
								FIDO 卡片註冊 
							</button>
                        </form>
					</div>
				</div>
			</div>
		</div>
	</div>
<!-- 
    <h1>Register FIDO Credential</h1>
    <form id="registerForm">
        <label for="user_id">User ID:</label>
        <input type="text" id="user_id" name="user_id" required><br><br>
        <label for="display_name">Display Name:</label>
        <input type="text" id="display_name" name="display_name"><br><br>
        
        <button type="button" onclick="registerFIDO()">Register FIDO Credential</button>
    </form>
-->
    <script src="register_fido.js"></script>
</body>
</html>
