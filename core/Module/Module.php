<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 20:48:47
 * @LastEditTime: 2022-06-26 20:52:16
 */
namespace Chat\Module;
if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\Libs\Libs;

class Module {
    /**
     * 所有模块
     * 
     * @var array
     * @access private
     */
    private static $_module = [];

    /**
     * 未处理的模块列表
     * 
     * @var array
     * @access private
     */
    private static $_raw_module = [];

    /**
     * 已实例化的模块
     * 
     * @var array
     * @access private
     */
    private static $_instance_plugins = [];

    /**
     * 初始化
     * 
     * @return void
     * @access public
     */
    public function __construct()
    {
        self::$_raw_module = $this->_getTailList();

        if (count(self::$_raw_module) > 0) {
            foreach (self::$_raw_module as $module) {
                if (file_exists($module . "/Chat.php")) {
                    $info = Libs::parseInfo(file_get_contents($module . "/Chat.php"), true);
                    self::$_module[$info['package']] = [
                        "name"          => $info['name'],
                        "package"       => $info['package'],
                        "author"        => $info['author'],
                        "version"       => $info['version'],
                        "file"          => $module . "/Chat.php",
                        "description"   => $info['description'],
                    ];
                }
            }
        }
    }

    /**
     * 模块初始化
     * 
     * @return void
     */
    public function init(): void
    {
        foreach (self::$_module as $module) {
            $pl = $this->_loadTail($module);
            if ($pl["status"] === false) throw new \Exception($pl["message"]);
            self::$_instance_plugins[$module["package"]] = $pl["instance"];
        }

        foreach (self::$_instance_plugins as $pl) $pl::run();
    }

    /**
     * 读取指定目录下所有模块
     * 
     * @return array
     * @access private
     */
    private function _getTailList(): array
    {
        return glob(__CHAT_ROOT_DIR__ . "/module/*");
    }

    /**
     * 加载模块
     * 
     * @return array
     * @access private
     */
    private function _loadTail(array $module): array
    {
        if (!file_exists($module["file"])) return ["status" => false, "message" => "模块源文件不存在，无法正确加载"];

        require_once $module["file"];

        $pl = "\\ChatModule\\" . $module["package"] . "\\Chat";
        return ["status" => true, "instance" => (new $pl())];
    }
}
