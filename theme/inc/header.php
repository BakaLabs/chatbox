<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 19:32:54
 * @LastEditTime: 2022-06-26 22:14:09
 */
if (!defined('__CHAT_ROOT_DIR__')) exit;
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>留言板 | MySQL 数据库作业</title>
    <link rel="stylesheet" href="<?= $this->siteUrl() ?>/static/css/mdui.min.css" />
    <link rel="stylesheet" href="<?= $this->siteUrl() ?>/static/css/font.css" />
    <link rel="stylesheet" href="<?= $this->siteUrl() ?>/static/css/style.css" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0,maximum-scale=1.0, user-scalable=no">
</head>

<body class="mysql-work-chat-app mdui-theme-primary-blue mdui-theme-accent-light-blue">
    <div id="chat-app">

        <header class="chat-header">
            <!-- 留言板的图标，来张表情包 -->
            <div class="chat-loooooooooogo">
                <img src="<?= $this->siteUrl() ?>/static/img/kokomi.png" alt="留言板" />
            </div>
            <!-- 留言版的标题 -->
            <div class="chat-title">留言板</div>
        </header>