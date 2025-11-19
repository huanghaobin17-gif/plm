<?php
ob_start();
session_start();

if (!isset($_SESSION['agreement_accepted']) || !$_SESSION['agreement_accepted']) {
    @header('Location: ?step=0');
    exit;
}

$requirements = array(
    'php_version' => array('required' => '5.6.40', 'current' => PHP_VERSION),
    'extensions' => array(
        'pdo',
        'pdo_mysql',
        'openssl',
        'mbstring',
        'xml',
        'fileinfo'
    ),
    'writable_dirs' => array(
        '../Application/Common/cache',
        '../Application/Runtime'
    )
);

$passed = true;

// 检查PHP版本
$phpVersionPassed = version_compare(PHP_VERSION, $requirements['php_version']['required'], '>=');
$passed = $passed && $phpVersionPassed;

// 检查扩展
$extensionResults = array();
foreach ($requirements['extensions'] as $extension) {
    $extensionResults[$extension] = extension_loaded($extension);
    $passed = $passed && $extensionResults[$extension];
}

// 检查目录权限
$directoryResults = array();
foreach ($requirements['writable_dirs'] as $directory) {
    $directoryResults[$directory] = is_writable($directory);
    $passed = $passed && $directoryResults[$directory];
}
?>

<div class="check-env">
    <h2>环境检测</h2>
    
    <div class="check-item">
        <h3>PHP版本</h3>
        <p class="<?php echo $phpVersionPassed ? 'success' : 'error'; ?>">
            需要 PHP >= <?php echo $requirements['php_version']['required']; ?>，
            当前版本：<?php echo $requirements['php_version']['current']; ?>
        </p>
    </div>

    <div class="check-item">
        <h3>PHP扩展</h3>
        <?php foreach ($extensionResults as $extension => $loaded): ?>
        <p class="<?php echo $loaded ? 'success' : 'error'; ?>">
            <?php echo $extension; ?>: <?php echo $loaded ? '已安装' : '未安装'; ?>
        </p>
        <?php endforeach; ?>
    </div>

    <div class="check-item">
        <h3>目录权限</h3>
        <?php foreach ($directoryResults as $directory => $writable): ?>
        <p class="<?php echo $writable ? 'success' : 'error'; ?>">
            <?php echo $directory; ?>: <?php echo $writable ? '可写' : '不可写'; ?>
        </p>
        <?php endforeach; ?>
    </div>

    <?php if ($passed): ?>
    <div class="next-step">
        <button type="submit" class="btn" onclick="window.location.href='?step=2'">下一步</button>
    </div>
    <?php else: ?>
    <div class="error-message">
        请解决以上问题后继续安装
    </div>
    <?php endif; ?>
</div> 

<style>
.check-env {
    max-width: 600px;
    margin: 0 auto;
}

.agreement-text {
    border: 1px solid #ccc;
    padding: 10px;
    height: 150px;
    overflow-y: scroll;
    margin-bottom: 10px;
}
</style> 

<?php
ob_end_flush(); 