<?php
/**
 * @Author: ohmyga
 * @Date: 2022-06-26 05:11:41
 * @LastEditTime: 2022-06-26 20:45:45
 */
if (!defined('__CHAT_ROOT_DIR__')) exit;
$this->loadFile('inc/header.php');
?>
<main class="chat-main">
    <div class="mdui-card chat-send-card">
        <form id="chat-box-form">
            <div class="chat-info-input">
                <div class="mdui-textfield">
                    <input class="mdui-textfield-input" name="nickname" type="text" placeholder="昵称">
                </div>
                <div class="mdui-textfield">
                    <input class="mdui-textfield-input" name="mail" type="mail" placeholder="邮箱（非必填）">
                </div>
            </div>
            <div class="chat-textarea mdui-textfield">
                <textarea class="mdui-textfield-input" name="text" rows="5" placeholder="说点什么吧..."></textarea>
            </div>
        </form>

        <div class="chat-buttons">
            <button class="mdui-btn mdui-ripple mdui-color-light-blue-700" onclick="submit()">发送留言</button>
        </div>
    </div>

    <div class="chat-list" id="chat-list">

    </div>
</main>
<?php $this->loadFile('inc/footer.php'); ?>