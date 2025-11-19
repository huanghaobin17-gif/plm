<?php
// Start output buffering and session at the very beginning
ob_start();
session_start();

$error = null;
$redirect = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConfig = array(
        'host' => isset($_POST['db_host']) ? $_POST['db_host'] : '127.0.0.1',
        'port' => isset($_POST['db_port']) ? $_POST['db_port'] : '3306',
        'database' => isset($_POST['db_name']) ? $_POST['db_name'] : '',
        'username' => isset($_POST['db_user']) ? $_POST['db_user'] : '',
        'password' => isset($_POST['db_password']) ? $_POST['db_password'] : ''
    );

    try {
        $connection = new mysqli(
            $dbConfig['host'], 
            $dbConfig['username'], 
            $dbConfig['password'], 
            '', 
            (int)$dbConfig['port']
        );
        
        if ($connection->connect_error) {
            throw new Exception('数据库连接失败：' . $connection->connect_error);
        }
        
        // 创建数据库
        $sql = "CREATE DATABASE IF NOT EXISTS `" . 
            $connection->real_escape_string($dbConfig['database']) . 
            "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            
        if (!$connection->query($sql)) {
            throw new Exception('创建数据库失败：' . $connection->error);
        }
        
        // 保存配置
        $_SESSION['db_config'] = $dbConfig;
        
        // 保存配置到.env文件
        $envContent = "db_host={$dbConfig['host']}\n" .
            "db_port={$dbConfig['port']}\n" .
            "db_name={$dbConfig['database']}\n" .
            "db_user={$dbConfig['username']}\n" .
            "db_pwd={$dbConfig['password']}\n";
            
        if (file_put_contents('../.env', $envContent, FILE_APPEND) === false) {
            throw new Exception('无法写入配置文件');
        }

        $redirect = true; // 设置重定向标志

        if ($redirect) {
            // header('Location: ?step=3');
            ob_end_flush();
            // exit;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        error_log($error);
    }
}
?>

<?php if ($redirect): ?>
<script>
    window.location.href = '?step=3';
</script>
<?php endif; ?>

<div class="db-config">
    <h2>数据库配置</h2>

    <?php if (isset($error)): ?>
    <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-item">
            <label>数据库主机：</label>
            <input type="text" name="db_host" value="127.0.0.1" required>
        </div>

        <div class="form-item">
            <label>端口：</label>
            <input type="text" name="db_port" value="3306" required>
        </div>

        <div class="form-item">
            <label>数据库名：</label>
            <input type="text" name="db_name" required>
        </div>

        <div class="form-item">
            <label>用户名：</label>
            <input type="text" name="db_user" required>
        </div>

        <div class="form-item">
            <label>密码：</label>
            <input type="password" name="db_password" required>
        </div>

        <div class="form-buttons">
            <a href="?step=1" class="btn btn-back">上一步</a>
            <button type="submit" class="btn">下一步</button>
        </div>
    </form>
</div> 