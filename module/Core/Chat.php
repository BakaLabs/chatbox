<?php

/**
 * 不知道是什么的核心文件罢了
 * 
 * @name Chat Core
 * @package Core
 * @author ohmyga
 * @version 1.0.0 
 */

namespace ChatModule\Core;

use Chat\Module\ChatInterface;
use Chat\HTTP\Router;
use Chat\HTTP\HTTP;
use Chat\DB\DB;

class Chat implements ChatInterface
{
    private static $_db;

    public static function run(): void
    {
        Router::actionRegisterRouter(__CLASS__);
        self::$_db = DB::get();
    }

    /**
     * 获取留言列表
     * 
     * @return void
     * @access public
     * @version 1
     * @path /list
     */
    public static function ChatList_Action(): void
    {
        if (!HTTP::lockMethod("GET")) exit;

        $list = self::$_db->fetchAll(
            self::$_db->select()->from('table.list')
        );

        if (count($list) <= 0) {
            HTTP::sendJson(false, 404, "空的");
        }

        $result = [];

        foreach ($list as $item) {
            $result[] = [
                "id"         => $item['id'],
                "nickname"   => $item['name'],
                "content"    => $item['content'],
                "time"       => date("Y-m-d H:i:s", $item['created']),
                "avatar"     => empty($item["email"]) ? self::randAvatar() : "https://gravatar.loli.net/avatar/" . md5($item["email"]) . "?s=100"
            ];
        }

        HTTP::sendJson(true, 200, "成功", $result);
    }

    /**
     * 随机头像
     */
    private static function randAvatar()
    {
        $list = glob(__CHAT_ROOT_DIR__ . "/static/img/avatar/*");

        foreach ($list as $key => $item) {
            $list[$key] = HTTP::$instance->getSiteUrl() . str_replace(__CHAT_ROOT_DIR__, "", $item);
        }

        return $list[mt_rand(0, (count($list) - 1))];
    }

    /**
     * 提交留言
     * 
     * @version 1
     * @path /submit
     */
    public static function ChatSubmit_Action(): void
    {
        if (!HTTP::lockMethod("POST")) exit;

        $nickname = HTTP::$instance->getParams("nickname", null);
        $mail = HTTP::$instance->getParams("mail", null);
        $text = HTTP::$instance->getParams("text", null);

        if (empty($nickname) || empty($text)) {
            HTTP::sendJson(false, 400, "昵称或留言内容不能为空");
        }

        self::$_db->query(
            self::$_db->insert("table.list")->rows([
                'name'       => $nickname,
                'email'      => $mail ?? "",
                'content'    => $text,
                'created'    => time()
            ])
        );

        HTTP::sendJson(true, 200, "留言成功", [
            "id"         => null,
            "nickname"   => $nickname,
            "content"    => $text,
            "time"       => date("Y-m-d H:i:s", time()),
            "avatar"     => empty($mail) ? self::randAvatar() : "https://gravatar.loli.net/avatar/" . md5($mail) . "?s=100"
        ]);
    }
}
