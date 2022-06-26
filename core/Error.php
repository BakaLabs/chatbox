<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 17:41:24
 * @LastEditTime: 2022-06-26 17:43:41
 */

namespace Chat;
if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\HTTP\HTTP;

class Error
{
    public static function init()
    {
        set_error_handler("Chat\Error::handler", E_ALL | E_STRICT);
        set_exception_handler("Chat\Error::eachxception_handler");
    }

    /**
     * 错误解析器
     * 
     * @param int $error_level            错误等级
     * @param string $error_message       错误消息
     * @param string $error_file          错误所在文件
     * @param int $error_line             错误所在行号
     * @param void $error_context         错误上下文内容
     */
    public static function handler($error_level, $error_message, $error_file, $error_line, $error_context)
    {
        switch ($error_level) {
            case 2:
                $level = 'WARNING';
                break;
            case 8:
                $level = 'INFO';
                break;
            case 256:
                $level = 'ERROR';
                break;
            case 512:
                $level = 'WARNING';
                break;
            case 1024:
                $level = 'NOTICE';
                break;
            case 4096:
                $level = 'ERROR';
                break;
            case 8191:
                $level = 'ERROR';
                break;

            default:
                $level = 'UNKNOWN';
                break;
        }

        HTTP::sendJSON(false, 500, 'PHP ' . $level . ': ' . $error_message . ' in ' . $error_file . ' on line ' . $error_line . '.');
    }

    /**
     * 致命错误处理器
     * 
     * @param $exception
     * @param bool $console     是否在控制台输出
     **/
    public static function eachxception_handler($exception, $console = true)
    {
        HTTP::sendJSON(false, 500, 'PHP Exception: ' . $exception->getMessage() . ' in ' . $exception->getFile() . ' on line ' . $exception->getLine() . '.');
    }
}
