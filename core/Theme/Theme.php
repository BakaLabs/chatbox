<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 19:09:19
 * @LastEditTime: 2022-06-26 19:53:43
 */
namespace Chat\Theme;
if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\HTTP\Router;
use Chat\HTTP\HTTP;

class Theme {
    /**
     * 初始化
     * 
     * @return void
     * @access public
     */
    public function init($instance) {
        $this->addPage("index", "/", "index.php");
    }

    /**
     * 注册页面
     * 
     * @param string $name  页面名称
     * @param string $path  页面URL
     * @param string $file  页面文件
     */
    private function addPage($name, $path, $file) {
        Router::add([
            "name"           => $name,
            "url"            => $path,
            "disableVersion" => true,
            "version"        => "0",
            "widget"         => function() use ($file) {
                HTTP::setHeader('Content-Type', 'text/html; charset=UTF-8');
                require_once __CHAT_ROOT_DIR__ . "/theme/{$file}";
            },
        ]);
    }

    /**
     * 加载文件
     * 
     * @param string $file  文件名称
     * @return void
     */
    public function loadFile($file) {
        require_once __CHAT_ROOT_DIR__ . "/theme/{$file}";
    }

    /**
     * 站点根目录连接
     * 
     * @return string
     */
    public function siteUrl() {
        return HTTP::$instance->getSiteUrl();
    }
}
