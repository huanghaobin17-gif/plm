<?php
ob_start(); // Start output buffering at the very beginning

// 确保在此行之前没有空白或输出
date_default_timezone_set('Asia/Shanghai'); // 设置时区为上海

// 移除 strict_types 声明，因为低版本PHP不支持
// declare(strict_types=1);

session_start();

// 检查是否已安装
if (file_exists('../config/installed.lock')) {
    die('系统已经安装，如需重新安装请删除 config/installed.lock 文件');
}

// 修改 ?? 运算符为兼容写法
$step = isset($_GET['step']) ? $_GET['step'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>安装向导</title>
    <link rel="stylesheet" href="./assets/install.css">
</head>
<body>
    <div class="install-box">
        <div class="header">
            <h1>系统安装向导</h1>
        </div>
        
        <div class="step">
            <ul>
                <li>许可协议</li>
                <li>环境检测</li>
                <li>数据库配置</li>
                <li>管理员配置</li>
                <li>安装完成</li>
            </ul>
        </div>

        <div class="content">
            <?php include "./steps/step{$step}.php"; ?>
        </div>
    </div>

    <script>
        // JavaScript to set the active class
        document.addEventListener('DOMContentLoaded', function() {
            const step = <?php echo json_encode($step); ?>;
            const steps = document.querySelectorAll('.step ul li');
            steps.forEach((li, index) => {
                if (index <= step) {
                    li.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 