<?php
// Remove strict_types declaration for older PHP versions
// declare(strict_types=1);

ob_start();
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../includes/DatabaseInstaller.php';

// Check session before any output
if (!isset($_SESSION['db_config'])) {
    header('Location: ?step=2');
    ob_end_flush();
    exit;
}

$errors = [];
$progress = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adminConfig = array(
        'username' => isset($_POST['admin_user']) ? $_POST['admin_user'] : '',
        'password' => isset($_POST['admin_password']) ? $_POST['admin_password'] : '',
        'email' => isset($_POST['admin_email']) ? $_POST['admin_email'] : ''
    );

    // Validate input
    if (strlen($adminConfig['username']) < 4) {
        $errors[] = '用户名长度不能小于4个字符';
    }
    if (strlen($adminConfig['password']) < 6) {
        $errors[] = '密码长度不能小于6个字符';
    }
    if (!filter_var($adminConfig['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = '请输入有效的邮箱地址';
    }

    if (empty($errors)) {
        try {
            $installer = new DatabaseInstaller($_SESSION['db_config']);
            
            // Update progress
            $progress = 20;
            updateProgress($progress);

            // Install database schema
            if (!$installer->installSchema()) {
                $errors = array_merge($errors, $installer->getErrors());
            } else {
                // Update progress
                $progress = 60;
                updateProgress($progress);

                // Create admin user
                if (!$installer->createAdminUser($adminConfig)) {
                    $errors = array_merge($errors, $installer->getErrors());
                } else {
                    // Update progress
                    $progress = 100;
                    updateProgress($progress);

                    // Save configuration
                    $_SESSION['admin_config'] = $adminConfig;

                    $redirect = true; // 设置重定向标志

                    if ($redirect) {
                        header('Location: ?step=4');
                        ob_end_flush();
                        exit;
                    }
                }
            }
        } catch (Exception $e) {
            $errors[] = '安装过程中发生错误: ' . $e->getMessage();
        }
    }
}

function updateProgress($progress) {
    echo "<script>document.getElementById('progress-bar').style.width = '{$progress}%';</script>";
    ob_flush();
    flush();
}
?>

<div class="admin-config">
    <h2>管理员配置</h2>

    <?php if (!empty($errors)): ?>
    <div class="error-message">
        <?php foreach ($errors as $error): ?>
            <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <form method="post">
        <div class="form-item">
            <label>管理员用户名：</label>
            <input type="text" name="admin_user" required 
                   value="<?php echo htmlspecialchars(isset($_POST['admin_user']) ? $_POST['admin_user'] : '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-item">
            <label>管理员密码：</label>
            <input type="password" name="admin_password" required>
        </div>

        <div class="form-item">
            <label>管理员邮箱：</label>
            <input type="email" name="admin_email" required
                   value="<?php echo htmlspecialchars(isset($_POST['admin_email']) ? $_POST['admin_email'] : '', ENT_QUOTES, 'UTF-8'); ?>">
        </div>

        <div class="form-buttons">
            <a href="?step=2" class="btn btn-back">上一步</a>
            <button type="submit" class="btn">下一步</button>
        </div>
    </form>
</div> 

<style>
.progress {
    width: 100%;
    background-color: #f3f3f3;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 20px;
}

.progress-bar {
    height: 20px;
    background-color: #4caf50;
    width: 0;
    transition: width 0.4s;
}
</style> 