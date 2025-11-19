<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\DBALException;

class DatabaseInstaller
{
    private $connection;
    private $errors = array();

    public function __construct($config)
    {
        try {
            $connectionParams = [
                'dbname' => $config['database'],
                'user' => $config['username'],
                'password' => $config['password'],
                'host' => $config['host'],
                'port' => $config['port'],
                'driver' => 'pdo_mysql',
                'charset' => 'utf8mb4',
            ];
            $this->connection = DriverManager::getConnection($connectionParams);
        } catch (Exception $e) {
            throw new \Exception("数据库连接失败：" . $e->getMessage());
        }
    }

    public function installSchema()
    {
        try {
            $sql = file_get_contents(__DIR__ . '/../data/schema.sql');
            if ($sql === false) {
                throw new \Exception("无法读取数据库结构文件");
            }

            // 分割SQL语句
            $statements = array_filter(
                array_map('trim', explode(";\n", $sql)),
                'strlen'
            );

            // 开始事务
            $this->connection->beginTransaction();

            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    try {
                        // 记录当前执行的SQL语句
                        error_log("Executing SQL: " . $statement);
                        $this->connection->executeQuery($statement);
                    } catch (DBALException $e) {
                        throw new \Exception("SQL执行错误: " . $e->getMessage() . " 在语句: " . $statement);
                    }
                }
            }

            // 提交事务
            $this->connection->commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            if ($this->connection->isTransactionActive()) {
                $this->connection->rollBack();
            }
            $this->errors[] = "安装数据库结构失败：" . $e->getMessage();
            return false;
        }
    }

    public function createAdminUser($adminData)
    {
        try {
            // 验证输入数据
            if (empty($adminData['username'])) {
                throw new \Exception('用户名不能为空');
            }
            if (empty($adminData['password'])) {
                throw new \Exception('密码不能为空');
            }
            if (empty($adminData['email'])) {
                throw new \Exception('邮箱不能为空');
            }

            // 使用与 UserModel 相同的密码加密方法
            $encryptedPassword = $this->hashPassword($adminData['password']);

            // 准备 SQL 语句
            $sql = "UPDATE `sb_user` SET `username` = ?, `password` = ?, `email` = ? WHERE `userid` = 1";
            $stmt = $this->connection->prepare($sql);

            // 执行语句
            $stmt->execute([
                $adminData['username'],
                $encryptedPassword,
                $adminData['email']
            ]);

            return true;
        } catch (\Exception $e) {
            $this->errors[] = "创建管理员账号失败：" . $e->getMessage();
            return false;
        }
    }

    private function hashPassword($password)
    {
        if (function_exists('password_hash')) {
            return password_hash($password, PASSWORD_DEFAULT);
        } else {
            $salt = substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 16)), 0, 16);
            return sha1($password . $salt) . ':' . $salt;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }
} 