<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 05:07:04
 * @LastEditTime: 2022-06-26 21:20:45
 */

namespace Chat;

if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\DB\DB;
use Chat\HTTP\HTTP;
use Chat\HTTP\Router;
use Chat\Module\Module;
use Chat\Theme\Theme;
use function file_exists;

class Framework
{
    /**
     * 初始化类
     * 
     * @return void
     * @access public
     */
    public function __construct()
    {
        /**
         * 初始化 HTTP
         */
        $http = new HTTP();
        $http->init($http);

        /**
         * 判断是否安装
         * 如果没有安装，则跳转到安装页面
         */
        if (!file_exists(__CHAT_ROOT_DIR__ . '/config.php')) {
            header('Location: ' . HTTP::$instance->getSiteUrl() .  '/install.php');
            exit;
        }

        /**
         * 初始化数据库
         */
        try {
            $dbc = require_once __CHAT_ROOT_DIR__ . '/config.php';
            $dbs = new DB('Pdo_Mysql', $dbc['host'], $dbc['port'], $dbc['db'], $dbc['username'], $dbc['password'], $dbc['prefix']);
            $dbs->addPool();
            $dbs->set($dbs);
        } catch (\Exception $e) {
            HTTP::sendJSON(false, 500, $e->getMessage());
        }

        // 初始化插件
        try {
            if (!is_dir(__CHAT_ROOT_DIR__ . "/module")) throw new \Exception("模块目录不存在，初始化失败");

            $module = (new Module());
            $module->init();
        } catch (\Exception $e) {
        }

        /**
         * 解析页面 / 主题
         */
        try {
            $theme = new Theme();
            $theme->init($theme);
        } catch (\Exception $e) {
            HTTP::sendJson(false, 500, $e->getMessage());
        }
    }

    /**
     * 启动函数
     * 
     * @return void
     * @access public
     */
    public function start()
    {
        Router::dispatch();
    }
}
