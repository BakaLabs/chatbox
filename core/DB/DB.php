<?php

/**
 * @Author: ohmyga
 * @Date: 2022-06-26 14:44:53
 * @LastEditTime: 2022-06-26 17:54:36
 */

namespace Chat\DB;

if (!defined('__CHAT_ROOT_DIR__')) exit;

use Chat\DB\Consts;

class DB
{
    /**
     * 数据库适配器
     * 
     * @access private
     */
    private $_adapter;

    /**
     * 适配器名称
     * 
     * @access private
     * @var string
     */
    private $_adapterName;

    /**
     * 数据库表前缀
     * 
     * @var string
     * @access private
     */
    private $_prefix;

    /**
     * 数据库连接池
     * 
     * @var array
     * @access private
     */
    private static $_pool = [];

    /**
     * 连接池最大连接数
     * 
     * @var int
     * @access private
     */
    private static $_pool_size = 10;

    /**
     * 数据库配置
     * 
     * @var array
     * @access private
     */
    private $_config = [];

    /**
     * 数据库实例
     * 
     * @var DB
     * @access private
     */
    private static $_instance;

    /**
     * 数据库初始化
     * 
     * @param string $adapter 适配器名称
     * @param string $host    数据库主机
     * @param int $port       数据库端口
     * @param string $db      数据库名称
     * @param string $user    数据库用户名
     * @param string $pass    数据库密码
     * @param string $prefix  数据库表前缀
     * @param string $charset 数据库字符集
     * @param string $engine  数据库引擎
     * @return void
     * @access public
     */
    public function __construct($adapter, $host, $port, $db, $user, $pass, $prefix = 'Chat_', $charset = 'utf8mb4', $engine = 'InnoDB')
    {
        $adapterName = $adapter == 'Mysql' ? 'Mysqli' : $adapter;
        $this->_adapterName = $adapterName;

        $adapterName = '\\Chat\\DB\\Adapter\\' . str_replace('_', '\\', $adapterName);

        if (!method_exists($adapterName, 'isAvailable')) {
            throw new \Exception("没有找到名为 {$this->_adapterName} 的数据库适配器");
        }

        $this->_prefix = $prefix;
        $this->_config = [
            'host'      => $host,
            'port'      => $port,
            'db'        => $db,
            'username'  => $user,
            'password'  => $pass,
            'charset'   => $charset,
            'engine'    => $engine,
            'prefix'    => $prefix,
        ];

        self::$_pool = [];

        try {
            // 实例化适配器
            $this->_adapter = new $adapterName($this->_config);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this->_adapter;
    }

    /**
     * 创建数据库实例并加入连接池
     * 
     * @param bool $locked  是否使用中
     * @return array
     * @access public
     */
    public function addPool(bool $locked = false): array
    {
        if (count(self::$_pool) <= self::$_pool_size) {
            return self::$_pool[] = [
                "id"            =>  (int)(mt_rand(100, mt_rand(300, 900)) . substr(time(), 0, 3) . date("His")),
                "locked"        =>  $locked,
                "create_time"   =>  time(),
                "instance"      =>  $this->_adapter->init($this->_config)
            ];
        } else {
            foreach (self::$_pool as $key => $item) {
                self::$_pool[$key]["locked"] = false;
            }
            return self::$_pool[mt_rand(0, count(self::$_pool) - 1)];
        }
    }

    /**
     * 改变数据库连接池中的使用状态
     * 
     * @param string $id    数据库实例id
     * @param bool $lock    是否使用中
     * @return void
     * @access private
     */
    private function _changelockConnect($id, $lock = false): void
    {
        $_pool = !empty(self::$_pool) ? self::$_pool : [];

        foreach ($_pool as $key => $item) {
            if ($item["id"] == $id) {
                self::$_pool[$key]["locked"] = ($lock === true) ? true : false;
                break;
            }
        }
    }

    /**
     * 获取适配器名称
     * 
     * @return string
     */
    public function getAdapterName(): string
    {
        return $this->_adapterName;
    }

    /**
     * 获取数据库表前缀
     * 
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->_prefix;
    }

    /**
     * 获取数据库表编码
     * 
     * @return string
     */
    public function getCharset(): string
    {
        return $this->_config["charset"];
    }

    /**
     * 获取数据库表引索
     * 
     * @return string
     */
    public function getEngine(): string
    {
        return $this->_config["engine"];
    }

    /**
     * 清空连接池
     * 
     * @return void
     */
    public function clearPool()
    {
        self::$_pool = [];
    }

    /**
     * 选择数据库
     * 
     * @return mixed
     */
    public function selectDb($op = 0, $reid = false)
    {
        if (!empty(self::$_pool)) {
            $_hasUnLock = false;
            $_selectDB = [];
            foreach (self::$_pool as $key => $item) {
                if ($item["locked"] === false) {
                    $_selectDB = $item;
                    $_hasUnLock = true;
                }
            }

            if ($_hasUnLock === false) {
                $add = $this->addPool(true);
                return (!empty($add)) ? (($reid === true) ? $add : $add["instance"]) : [];
            } else {
                $this->_changelockConnect($_selectDB["id"], true);
            }

            return ($reid === true) ? $_selectDB : $_selectDB["instance"];
        }
    }

    /**
     * 获取SQL词法构建器实例化对象
     *
     * @return Query
     */
    public function sql(): Query
    {
        return new Query($this->_adapter, $this->_prefix);
    }

    /**
     * 获取数据库版本
     * 
     * 
     */
    public function getVersion(): string
    {
        return $this->_adapter->getVersion($this->selectDb());
    }

    public static function set(DB $db)
    {
        self::$_instance = $db;
    }

    /**
     * 获取数据库实例
     */
    public static function get()
    {
        if (empty(self::$_instance)) {
            throw new \Exception("没有任何数据库实例");
        }

        return self::$_instance;
    }

    /**
     * 选择查询字段
     * 
     * 
     */
    public function select(...$ags): Query
    {
        $this->selectDb(Consts::READ);

        $args = func_get_args();
        return call_user_func_array([$this->sql(), 'select'], $args ?: ['*']);
    }

    /**
     * 更新记录操作(UPDATE)
     */
    public function update(string $table): Query
    {
        $this->selectDb(Consts::WRITE);

        return $this->sql()->update($table);
    }

    /**
     * 删除记录操作(DELETE)
     */
    public function delete(string $table): Query
    {
        $this->selectDb(Consts::WRITE);

        return $this->sql()->delete($table);
    }

    /**
     * 插入记录操作(INSERT)
     */
    public function insert(string $table): Query
    {
        $this->selectDb(Consts::WRITE);

        return $this->sql()->insert($table);
    }

    /**
     * @param $table
     * @throws DbException
     */
    public function truncate($table)
    {
        $table = preg_replace("/^table\./", $this->prefix, $table);
        $this->adapter->truncate($table, $this->selectDb(Consts::WRITE));
    }

    /**
     * 执行查询语句
     */
    public function query($query, int $op = Consts::READ, string $action = Consts::SELECT)
    {
        $table = null;

        /** 在适配器中执行查询 */
        if ($query instanceof Query) {
            $action = $query->getAttribute('action');
            $table = $query->getAttribute('table');
            $op = (Consts::UPDATE == $action || Consts::DELETE == $action
                || Consts::INSERT == $action) ? Consts::WRITE : Consts::READ;
        } elseif (!is_string($query)) {
            /** 如果query不是对象也不是字符串,那么将其判断为查询资源句柄,直接返回 */
            return $query;
        }

        /** 选择连接池 */
        $handle = $this->selectDb($op, true);

        /** 提交查询 */
        $resource = $this->_adapter->query($query instanceof Query ?
            $query->prepare($query) : $query, $handle["instance"], $op, $action, $table);

        $this->_changelockConnect($handle["id"], false);

        if ($action) {
            //根据查询动作返回相应资源
            switch ($action) {
                case Consts::UPDATE:
                case Consts::DELETE:
                    return $this->_adapter->affectedRows($resource, $handle["instance"]);
                case Consts::INSERT:
                    return $this->_adapter->lastInsertId($resource, $handle["instance"]);
                case Consts::SELECT:
                default:
                    return $resource;
            }
        } else {
            //如果直接执行查询语句则返回资源
            return $resource;
        }
    }

    /**
     * 一次取出所有行
     */
    public function fetchAll($query, ?callable $filter = null): array
    {
        //执行查询
        $resource = $this->query($query);
        $result = $this->_adapter->fetchAll($resource);

        return $filter ? array_map($filter, $result) : $result;
    }

    /**
     * 一次取出一行
     */
    public function fetchRow($query, ?callable $filter = null): ?array
    {
        $resource = $this->query($query);

        return ($rows = $this->_adapter->fetch($resource)) ?
            ($filter ? call_user_func($filter, $rows) : $rows) :
            null;
    }

    /**
     * 一次取出一个对象
     */
    public function fetchObject($query, ?array $filter = null): ?object
    {
        $resource = $this->query($query);

        return ($rows = $this->_adapter->fetchObject($resource)) ?
            ($filter ? call_user_func($filter, $rows) : $rows) :
            null;
    }
}
