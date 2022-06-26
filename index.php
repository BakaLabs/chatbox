<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 04:58:51
 * @LastEditTime: 2022-06-26 15:33:05
 */

define('__CHAT_ROOT_DIR__', __DIR__);

/* 引入自动加载 */
require_once __CHAT_ROOT_DIR__ . '/vendor/autoload.php';

// 框架初始化
(new \Chat\Framework())->start();
