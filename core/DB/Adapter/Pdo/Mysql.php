<?php

/**
 * Mysql PDO
 * 
 * @Author: ohmyga
 * @Date: 2022-06-26 15:29:05
 * @LastEditTime: 2022-06-26 18:10:27
 */

namespace Chat\DB\Adapter\Pdo;

if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\DB\Adapter\Pdo;

class Mysql extends Pdo
{
    use \Chat\DB\Adapter\MysqlTrait;

    /**
     * 判断适配器是否可用
     * 
     * @return bool
     */
    public static function isAvailable(): bool
    {
        return parent::isAvailable() && in_array('mysql', \PDO::getAvailableDrivers());
    }

    /**
     * 初始化数据库
     * 
     * @param array $config   配置文件数组
     * @return \PDO           PDO 对象
     */
    public function init(array $config): \PDO
    {
        $pdo = new \PDO(
            sprintf(
                'mysql:host=%s;dbname=%s;port=%s;charset=%s',
                $config["host"],
                $config["db"],
                $config["port"],
                $config["charset"]
            ),
            $config["username"],
            $config["password"],
            []
        );

        $pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);


        return $pdo;
    }

    /**
     * 引号转义函数
     * 
     * @param mixed $string  需要转义的字符串
     * @return string
     */
    public function quoteValue($string): string
    {
        return '\'' . str_replace(['\'', '\\'], ['\'\'', '\\\\'], $string) . '\'';
    }

    /**
     * 对象引号过滤
     * 
     * @param string $string  需要转义的字符串
     * @return string
     */
    public function quoteColumn(string $string): string
    {
        return '`' . $string . '`';
    }
}
