--
-- 表的结构 `Chat_list`
--
CREATE TABLE `Chat_list` (
    `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "留言自增 ID",
    `name` varchar(255) NOT NULL COMMENT "昵称",
    `email` varchar(255) NOT NULL COMMENT "邮箱",
    `content` varchar(500) NOT NULL COMMENT "留言内容",
    `created` int(10) unsigned default '0' COMMENT "留言时间",
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT="留言列表";
