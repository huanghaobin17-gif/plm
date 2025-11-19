<?php
ob_start();
session_start();

// Check session before any output
if (!isset($_SESSION['db_config']) || !isset($_SESSION['admin_config'])) {
    header('Location: ?step=1');
    ob_end_flush();
    exit;
}

try {
    // 生成配置文件
    $dbConfig = $_SESSION['db_config'];
    $configContent = "<?php\nreturn " . var_export(array(
        'database' => array(
            'host' => $dbConfig['host'],
            'port' => $dbConfig['port'],
            'database' => $dbConfig['database'],
            'username' => $dbConfig['username'],
            'password' => $dbConfig['password'],
        ),
    ), true) . ";\n";

    // 确保配置目录存在
    if (!is_dir('../config')) {
        mkdir('../config', 0755, true);
    }

    // 写入配置文件
    if (!file_put_contents('../config/database.php', $configContent)) {
        throw new Exception('无法写入配置文件');
    }

    // 创建安装锁定文件
    if (!file_put_contents('../config/installed.lock', date('Y-m-d H:i:s'))) {
        throw new Exception('无法创建安装锁定文件');
    }

    $success = true;
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>
<?php if ($redirect): ?>
<script>
    window.location.href = '?step=3';
</script>
<?php endif; ?>

<div class="install-complete">
    <h2>安装完成</h2>

    <?php if (isset($success)): ?>
        <div class="success-message">
            <div class="success-icon">✓</div>
            <p class="congrats">🎉 恭喜！系统安装成功！</p>
            
            <div class="admin-info">
                <h3>管理员信息</h3>
                <div class="info-box">
                    <div class="info-item">
                        <span class="label">用户名：</span>
                        <span class="value"><?php echo htmlspecialchars($_SESSION['admin_config']['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="label">邮箱：</span>
                        <span class="value"><?php echo htmlspecialchars($_SESSION['admin_config']['email']); ?></span>
                    </div>
                </div>
            </div>

            <div class="next-steps">
                <h3>⚠️ 重要提示</h3>
                <div class="steps-box">
                    <p>1. 请立即删除 install 目录</p>
                    <p>2. 请保存好管理员账号信息</p>
                    <p>3. 建议立即修改默认密码</p>
                </div>
            </div>
        </div>
        <div class="form-buttons">
            <a href="../admin" class="btn btn-primary">进入管理后台</a>
        </div>
    <?php else: ?>
        <div class="error-message">
            <div class="error-icon">✕</div>
            <h3>安装失败</h3>
            <p class="error-detail"><?php echo htmlspecialchars($error); ?></p>
        </div>
        <div class="form-buttons">
            <a href="?step=3" class="btn btn-back">返回上一步</a>
        </div>
    <?php endif; ?>
</div>

<?php
// Move session cleanup to the top of the success block
if (isset($success) && $success) {
    // Clear installation session data
    session_destroy();
}
?> 