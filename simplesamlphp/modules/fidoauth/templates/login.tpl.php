<?php
$this->data['header'] = 'FIDO Authentication';
$this->includeAtTemplateBase('includes/header.php');
?>

<h2>FIDO Authentication</h2>
<p>Please authenticate using your FIDO device.</p>

<form id="fido-form">
    <input type="hidden" name="AuthState" value="<?php echo htmlspecialchars($this->data['stateId']); ?>" />
    <button type="button" onclick="startAuthentication()">Authenticate with FIDO</button>
</form>

<script>
async function startAuthentication() {
    const form = document.getElementById('fido-form');
    const authState = form.elements['AuthState'].value;

    try {
        // 这里应该调用您的FIDO客户端逻辑
        // 例如，获取挑战，执行认证等
        const authResult = await performFidoAuthentication();

        // 发送认证结果到服务器
        const response = await fetch('<?php echo \SimpleSAML\Module::getModuleURL('fidoauth/verify.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                AuthState: authState,
                fidoResponse: authResult
            }),
        });

        if (response.ok) {
            // 认证成功，重定向到成功页面或执行其他操作
            window.location.href = '<?php echo \SimpleSAML\Module::getModuleURL('fidoauth/success.php'); ?>';
        } else {
            // 处理错误
            console.error('Authentication failed');
        }
    } catch (error) {
        console.error('Error during authentication:', error);
    }
}

async function performFidoAuthentication() {
    // 实现您的FIDO客户端逻辑
    // 这可能包括调用 navigator.credentials.get() 等
    // 返回认证结果
}
</script>

<?php
$this->includeAtTemplateBase('includes/footer.php');
?>