<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 15:25:26
 * @LastEditTime: 2022-06-26 15:25:30
 */

namespace Chat\DB\Adapter;

if (!defined('__CHAT_ROOT_DIR__')) exit;

trait QueryTrait
{
    private function buildQuery(array $sql): string
    {
        if (!empty($sql['join'])) {
            foreach ($sql['join'] as $val) {
                [$table, $condition, $op] = $val;
                $sql['table'] = "{$sql['table']} {$op} JOIN {$table} ON {$condition}";
            }
        }

        $sql['limit'] = (0 == strlen((string)$sql['limit'])) ? null : ' LIMIT ' . $sql['limit'];
        $sql['offset'] = (0 == strlen((string)$sql['offset'])) ? null : ' OFFSET ' . $sql['offset'];

        return 'SELECT ' . $sql['fields'] . ' FROM ' . $sql['table'] .
            $sql['where'] . $sql['group'] . $sql['having'] . $sql['order'] . $sql['limit'] . $sql['offset'];
    }
}
