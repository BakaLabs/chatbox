<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 20:56:18
 * @LastEditTime: 2022-06-26 20:56:23
 */

namespace Chat\Module;

interface ChatInterface {
    /**
     * 每次运行的函数
     * 
     * @static
     * @return void
     * @access public
     */
    public static function run(): void;
}