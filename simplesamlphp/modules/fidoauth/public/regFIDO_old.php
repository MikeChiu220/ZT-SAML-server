<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register FIDO Credential</title>
</head>
<body>
    <h1>Register FIDO Credential</h1>
    <form id="registerForm">
        <label for="user_id">User ID:</label>
        <input type="text" id="user_id" name="user_id" required><br><br>
        <label for="display_name">Display Name:</label>
        <input type="text" id="display_name" name="display_name"><br><br>
        
        <button type="button" onclick="registerFIDO()">Register FIDO Credential</button>
    </form>

    <script src="register_fido.js"></script>
</body>
</html>
