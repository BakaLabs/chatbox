<?php
/**
 * HTTP 类
 * 
 * @Author: ohmyga
 * @Date: 2022-06-26 05:21:43
 * @LastEditTime: 2022-06-26 17:37:08
 */
namespace Chat\HTTP;
if (!defined('__CHAT_ROOT_DIR__')) exit;

class HTTP
{
    /**
     * 已实例化的 HTTP 模块
     * 
     * @var HTTP
     * @access public
     */
    public static $instance;

    /**
     * 初始化
     * 
     * @return void
     * @access public
     */
    public function init(HTTP $instance): void
    {
        self::$instance = $instance;
        self::setAllowOrigin();
    }

    /**
     * 响应 OPTIONS 请求方法
     * 
     * @return void
     * @access public
     */
    public static function handleOptions(): void
    {
        self::setCode(204);
        self::setHeader("Access-Control-Allow-Headers", "Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, Authorization");
        self::setHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, OPTIONS");
    }

    /**
     * 设置允许通行的域名
     * 
     * @return void
     * @access public
     */
    public static function setAllowOrigin(): void
    {
        /*$config = \Chat\Config::get("allow_origin");

        if (in_array(self::$instance->getHeader("ORIGIN"), $config)) {
            self::setHeader("Access-Control-Allow-Origin", self::$instance->getHeader("ORIGIN"));
        }*/
        self::setHeader("Access-Control-Allow-Origin", self::$instance->getHeader("ORIGIN"));
    }

    /**
     * 获取 Server 变量
     * 
     * @param string $key            变量名
     * @param string|null $default   默认值
     * @return string|null
     * @access public
     */
    public function getServer(string $key, ?string $default = null): ?string
    {
        return $_SERVER[$key] ?? $default;
    }

    /**
     * 获取请求的 Header
     * 
     * @param string $key             Header Key
     * @param string|null $default    值为空时的默认值
     * @return string|null
     * @access public
     */
    public function getHeader($key, $default = null): ?string
    {
        $raw_key = strtoupper(str_replace('-', '_', $key));
        $last_key = 'HTTP_' . $raw_key;
        if (!empty($this->getServer($last_key))) {
            return $this->getServer($last_key, $default);
        } else if (!empty($this->getServer($raw_key))) {
            return $this->getServer($raw_key, $default);
        }

        return $this->getServer($key, $default);
    }

    /**
     * 获取 GET / POST 参数
     * 
     * @param string $key     参数的键
     * @param mixed $default  默认值
     * @return string|null
     */
    public function getParams(string $key, ?string $default = null): ?string
    {
        if ($this->isGet()) {
            if (!empty($_GET[$key]) || isset($_GET[$key])) {
                return $_GET[$key];
            }
        }

        if ($this->isPost() || $this->isDelete() || $this->isPut()) {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!empty($data[$key]) || isset($data[$key])) return $data[$key];
            if (!empty($_GET[$key]) || isset($_GET[$key])) return $_GET[$key];
            if (!empty($_POST[$key])) return $_POST[$key];
            return $default;
        } else if (!empty($_POST[$key])) {
            return $_POST[$key];
        }

        if (!empty($_GET)) {
            return (!empty($_GET[$key]) || isset($_GET[$key])) ? $_GET[$key] : $default;
        }

        return $default;
    }

    /**
     * 获取 Cookie
     * 
     * @param string $key              Cookie 的键
     * @param string|null $default     默认值
     * @return string|null
     * @access public
     */
    public function getCookie(string $key, ?string $default = null): ?string
    {
        if (!empty($_COOKIE)) {
            $cookie = $_COOKIE;
            return (!empty($cookie[$key])) ? $cookie[$key] : $default;
        } else {
            return $default;
        }
    }

    /**
     * 设置 Cookie
     * 
     * @access public
     */
    public static function setCookie($key, $value, $expires = 0, $path = "/", $domain = null, $secure = false): void
    {
        $expires = ($expires === 0) ? time() + 3600 : $expires;
        $domain = (!empty($domain)) ? $domain : self::$instance->getHeader("host", "");
        setcookie($key, $value, $expires, $path, $domain, $secure);
    }

    /**
     * 获取请求方法
     * 
     * @return string
     * @access public
     */
    public function getMethod(): string
    {
        return strtoupper(self::getServer('REQUEST_METHOD', "GET"));
    }

    /**
     * 设置 HTTP 状态码
     * 
     * @return void
     * @access public
     */
    public static function setCode(int $code = 200): void
    {
        header('HTTP/1.1 ' . $code . " " . Code::get($code));
    }

    /**
     * 锁定请求方式
     * 
     * @param string|array $method
     * @return bool
     * @access public
     */
    public static function lockMethod($method): bool
    {
        $lockMsg = (function () {
            self::sendJSON(false, 405, "Method not allowed");
        });
        if (is_array($method)) {
            $_methods = [];
            foreach ($method as $val) {
                $_methods[] = strtoupper($val);
            }

            if (!in_array(strtoupper(self::$instance->getMethod()), $_methods)) {
                $lockMsg();
                return false;
            }
        } else {
            if (strtoupper(self::$instance->getMethod()) != strtoupper($method)) {
                $lockMsg();
                return false;
            }
        }

        return true;
    }

    /**
     * 返回 JSON
     * 
     * @param bool $status      当前状态
     * @param int $code         HTTP Code
     * @param string $message   返回的消息
     * @param array $data       返回的数据
     * @param array $more       更多字段
     * @return void
     */
    public static function sendJSON(bool $status, $code, $message, array $data = [], array $more = []): void
    {
        $data = [
            "status"    => $status === true ? true : false,
            "code"      => (int)$code,
            "message"   => $message,
            "data"      => $data,
        ];

        if (is_array($more)) {
            $data = array_merge($data, $more);
        }

        self::setCode($code);
        self::setHeader("Content-type", "application/json; charset=utf-8");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * 返回 Raw JSON
     * 
     * @param int $code       HTTP code
     * @param array $data     返回的数据数组
     * @return void
     */
    public static function sendRawJSON($code, array $data = []): void
    {
        self::setCode($code);
        self::setHeader("Content-type", "application/json; charset=utf-8");
        echo json_encode($data, JSON_UNESCAPED_UNICODE);

        exit;
    }

    /**
     * 返回任何内容
     * 
     * @param int $code      HTTP Code
     * @param mixed $data    返回的内容
     * @param array $header  设置 Header
     * @return void
     */
    public static function send($code, $data, array $header = []): void
    {
        self::setCode($code);

        if (count($header) > 0) {
            foreach ($header as $key => $value) {
                self::setHeader($key, $value);
            }
        }

        echo $data;
        exit;
    }

    /**
     * 返回重定向
     * 
     * @param int $code       HTTP Code [301/302]
     * @param string $url     重定向的链接
     * @return void
     */
    public static function sendRedirect($code, $url): void
    {
        self::setCode(($code == 301) ? 301 : 302);
        self::setHeader("Location", $url);
    }

    /**
     * is GET
     * 
     * @return bool
     * @access public
     */
    public function isGet(): bool
    {
        return strtolower('GET') == strtolower($this->getServer('REQUEST_METHOD') ?? "");
    }

    /**
     * is POST
     * 
     * @return bool
     * @access public
     */
    public function isPost(): bool
    {
        return strtolower('POST') == strtolower($this->getServer('REQUEST_METHOD') ?? "");
    }

    /**
     * is PUT
     * 
     * @return bool
     * @access public
     */
    public function isPut(): bool
    {
        return strtolower('PUT') == strtolower($this->getServer('REQUEST_METHOD') ?? "");
    }

    /**
     * is DELETE
     * 
     * @return bool
     * @access public
     */
    public function isDelete(): bool
    {
        return strtolower('DELETE') == strtolower($this->getServer('REQUEST_METHOD') ?? "");
    }

    /**
     * set header
     * 
     * @param string $key
     * @param string $value
     * @return void
     * @access public
     */
    public static function setHeader($key, $value): void
    {
        header($key . ": " . $value);
    }

    /**
     * set headers
     * 
     * @param array $headers
     * @return void
     * @access public
     */
    public static function setHeaders(array $headers): void
    {
        foreach ($headers as $key => $value) {
            self::setHeader($key, $value);
        }
    }

    /**
     * 获取当前域名
     * 
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->getHttpType() . $this->getHeader("host", "");
    }

    /**
     * 获取当前请求协议
     * 
     * @return string
     */
    public function getHttpType()
    {
        $isHTTPS = false;
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            $isHTTPS = true;
        } else if ($this->getHeader("x-forwarded-proto") !== null && strtolower($this->getHeader("x-forwarded-proto")) == 'https') {
            $isHTTPS = true;
        } else if (!empty($this->getHeader('FRONT_END_HTTPS') && strtolower($this->getHeader('FRONT_END_HTTPS')) !== 'off')) {
            $isHTTPS = true;
        }
        return $isHTTPS ? 'https://' : 'http://';
    }
}
