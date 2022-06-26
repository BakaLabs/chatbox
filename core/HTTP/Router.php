<?php
/**
 * 路由类
 * 
 * @Author: ohmyga
 * @Date: 2022-06-26 05:19:02
 * @LastEditTime: 2022-06-26 19:25:49
 */
namespace Chat\HTTP;
if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\Libs\Libs;
use Chat\HTTP\Router\Parser;
use ReflectionClass, ReflectionMethod;

class Router {
    /**
     * 路由列表
     * 
     * @var array
     * @access private
     */
    private static $_routes = [];

    /**
     * 路由分发
     * 
     * @return void
     * @access public
     */
    public static function dispatch(): void {
        if (HTTP::$instance->getMethod() == "OPTIONS") {
            HTTP::handleOptions();
            return;
        }

        $_has = false;
        $requestUrl = str_replace("//", "/", HTTP::$instance->getServer("REQUEST_URI"));
        if (!empty(HTTP::$instance->getServer("QUERY_STRING"))) {
            $requestUrl = str_replace("?" . HTTP::$instance->getServer("QUERY_STRING"), "/", $requestUrl);
        }

        foreach (self::$_routes as $route) {
            if (preg_match($route['regx'], $requestUrl, $matches)) {
                call_user_func($route["widget"], HTTP::$instance->getMethod(), $matches);
                $_has = true;
            }
        }

        if ($_has === false) {
            HTTP::sendJSON(false, 404, "找不到，怎么想都找不到");
        }
    }

    /**
     * 为后缀为 _Action 的函数启用根据注解注册路由
     * 
     * @param string $class
     * @param string $name
     * @return void
     * @access public
     */
    public static function actionRegisterRouter($class, $name = "path"): void
    {
        $routes = [];
        $ReflectionClass = new ReflectionClass(!empty($class) ? $class : __CLASS__);

        foreach ($ReflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            preg_match('/(.*)_Action$/i', $method->getName(), $matches);
            preg_match('/(.*)_Admin_Action$/i', $method->getName(), $admin_matches);

            if (!empty($matches[1]) && empty($admin_matches[1])) {
                $parseInfo = Libs::parseInfo($method->getDocComment());
                $routes[] = [
                    'action'         => $matches[0],
                    'name'           => 'OAPI_' . $matches[1],
                    'url'            => (!empty($parseInfo[$name])) ?  $parseInfo[$name] : $matches[1],
                    'version'        => (!empty($parseInfo["version"])) ? $parseInfo["version"] : 1,
                    'disableVersion' => (isset($parseInfo["disableVersion"])) ? $parseInfo["disableVersion"] : false,
                    'description'    => $parseInfo['description']
                ];
            }
        }

        foreach ($routes as $key => $route) {
            self::add([
                "name"            => $route["name"],
                "url"             => $route["url"],
                "version"         => $route["version"],
                "disableVersion"  => $route["disableVersion"],
                "widget"          => $class . "::" . $route["action"],
            ]);
        }
    }

    /**
     * 添加路由
     * 
     * @param array $route     单个路由
     * @return array           路由解析结果
     */
    public static function add(array $route): array
    {
        $route["disableVersion"] = (isset($route["disableVersion"])) ? $route["disableVersion"] : false;
        if ($route["url"] != "/" && $route["disableVersion"] != "true") {
            $route["version"] = (!empty($route["version"])) ? $route["version"] : "1";
        }
        $parser = new Parser([$route]);
        self::$_routes[] = $parser->parse()[0];

        return $parser->parse()[0];
    }
}
