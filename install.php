<?php
/**
 * 安装助手
 * 
 * @Author: ohmyga
 * @Date: 2022-06-26 15:34:37
 * @LastEditTime: 2022-06-26 19:41:03
 */
define('__CHAT_ROOT_DIR__', __DIR__);
ini_set("display_errors", "On");
error_reporting(E_ALL);
/* 引入自动加载 */
require_once __CHAT_ROOT_DIR__ . '/vendor/autoload.php';

use Chat\HTTP\HTTP;
use Chat\DB\DB;
use Chat\Error;

$http = (new Http());
$http->init($http);

(new Error())->init();

if (file_exists(__CHAT_ROOT_DIR__ . '/config.php')) {
    header('Location: ' . HTTP::$instance->getSiteUrl());
    exit;
}

function paramPost()
{
    if (file_exists(__CHAT_ROOT_DIR__ . '/config.php')) {
        HTTP::sendJSON(true, 200, '已安装');
    }

    $host = HTTP::$instance->getParams('host', null);
    $port = HTTP::$instance->getParams('port', null);
    $dbname = HTTP::$instance->getParams('db', null);
    $prefix = HTTP::$instance->getParams('prefix', null);
    $username = HTTP::$instance->getParams('username', null);
    $password = HTTP::$instance->getParams('password', null);

    if (empty($host) || empty($port) || empty($dbname) || empty($prefix) || empty($username) || empty($password)) {
        HTTP::sendJSON(false, 400, '参数不完整');
    }

    try {
        $dbs = new DB('Pdo_Mysql', $host, $port, $dbname, $username, $password, $prefix);
        $dbs->addPool();
        $dbs->set($dbs);
    } catch (\Exception $e) {
        HTTP::sendJSON(false, 500, $e->getMessage());
    }

    try {
        $db = DB::get();
    } catch (\Exception $e) {
        HTTP::sendJSON(false, 500, "数据库连接失败");
    }

    if (!file_exists(__CHAT_ROOT_DIR__ . '/init.sql')) {
        $sql = 'CREATE TABLE `Chat_list` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "留言自增 ID",
            `name` varchar(255) NOT NULL COMMENT "昵称",
            `email` varchar(255) NOT NULL COMMENT "邮箱",
            `content` varchar(500) NOT NULL COMMENT "留言内容",
            `created` int(10) unsigned default \'0\' COMMENT "留言时间",
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="留言列表";';
    } else {
        $sql = file_get_contents(__CHAT_ROOT_DIR__ . '/init.sql');
    }

    try {
        $db->query($sql);
    } catch (\Exception $e) {
        HTTP::sendJSON(false, 500, $e->getMessage());
    }

    file_put_contents(__CHAT_ROOT_DIR__ . '/config.php', '<?php
    return [
        "host" => "' . $host . '",
        "port" => "' . $port . '",
        "db" => "' . $dbname . '",
        "prefix" => "' . $prefix . '",
        "username" => "' . $username . '",
        "password" => "' . $password . '",
        "charset" => "utf8mb4"
    ];');

    HTTP::sendJSON(true, 200, '安装成功，正在跳转...');
}

if (HTTP::$instance->isPost()) {
    paramPost();
    exit;
}
?>
<!DOCTYPE HTML>
<html lang="zh">

<head>
    <meta charset="UTF-8" />
    <title>安装 | 留言板 - Mysql 数据库作业</title>
    <link rel="stylesheet" href="<?= HTTP::$instance->getSiteUrl() ?>/static/css/mdui.min.css" />
    <link rel="stylesheet" href="<?= HTTP::$instance->getSiteUrl() ?>/static/css/font.css" />
    <link rel="stylesheet" href="<?= HTTP::$instance->getSiteUrl() ?>/static/css/style.css" />
    <link rel="stylesheet" href="<?= HTTP::$instance->getSiteUrl() ?>/static/css/install.css" />
</head>

<body class="mysql-work-chat-app mdui-theme-primary-blue mdui-theme-accent-light-blue">
    <div id="chat-app">
        <header class="chat-header">
            <div class="chat-loooooooooogo">
                <img src="<?= HTTP::$instance->getSiteUrl() ?>/static/img/kokomi.png" alt="留言板" />
            </div>
            <div class="chat-title">留言板 | 安装程序</div>
        </header>

        <main class="chat-main">
            <div class="mdui-card chat-install-card">
                <form id="chat-install-form">
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库主机</label>
                        <input class="mdui-textfield-input" name="host" type="text" value="127.0.0.1" />
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库端口</label>
                        <input class="mdui-textfield-input" name="port" type="text" value="3306" />
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库名</label>
                        <input class="mdui-textfield-input" name="db" type="text" value="root" />
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">数据库表前缀</label>
                        <input class="mdui-textfield-input" name="prefix" type="text" value="Chat_" />
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">用户名</label>
                        <input class="mdui-textfield-input" name="username" type="text" value="root" />
                    </div>
                    <div class="mdui-textfield">
                        <label class="mdui-textfield-label">密码</label>
                        <input class="mdui-textfield-input" name="password" type="text" value="root" />
                    </div>
                </form>
            </div>

            <div class="mdui-card chat-install-card chat-buttons-card">
                <button class="mdui-btn mdui-color-red mdui-ripple" onclick="clearAll()">清空已输入信息</button>
                <button class="mdui-btn mdui-color-light-blue-700 mdui-ripple" onclick="submit()">完成安装</button>
            </div>
        </main>
    </div>
    <script type="text/javascript">
        const CHAT_CONFIG = {
            site_url: '<?= HTTP::$instance->getSiteUrl() ?>'
        };
    </script>
    <script src="<?= HTTP::$instance->getSiteUrl() ?>/static/js/mdui.min.js"></script>
    <script src="<?= HTTP::$instance->getSiteUrl() ?>/static/js/install.js"></script>
</body>

</html>
