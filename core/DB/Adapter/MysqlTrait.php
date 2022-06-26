<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 15:27:55
 * @LastEditTime: 2022-06-26 15:28:01
 */

namespace Chat\DB\Adapter;

if (!defined('__CHAT_ROOT_DIR__')) exit;

trait MysqlTrait
{
    use QueryTrait;

    /**
     * 清空数据表
     * 
     * @param string $table 数据表名
     * @param mixed $handle 连接对象
     */
    public function truncate(string $table, $handle)
    {
        $this->query('TRUNCATE TABLE ' . $this->quoteColumn($table), $handle);
    }

    /**
     * 合成查询语句
     * 
     * @param array $sql   查询对象词法数组
     * @return string
     */
    public function parseSelect(array $sql): string
    {
        return $this->buildQuery($sql);
    }
}
