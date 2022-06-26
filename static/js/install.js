/**
 * 留言板 の Javascript
 * 好难------
 */

var $$ = mdui.$;

function submit() {
    var $data = $$("#chat-install-form").serialize();

    $$('.chat-buttons-card button').attr('disabled', true);

    $$.ajax({
        url: CHAT_CONFIG.site_url + "/install.php",
        method: "POST",
        data: $data,
        success: function (raw, textStatus, xhr) {
            const data = JSON.parse(raw);
            mdui.snackbar({
                message: data.message,
                timeout: 3000,
                position: "top",
            });
            window.location.reload();
        },
        error: function (raw, textStatus) {
            const data = JSON.parse(raw.response);
            mdui.snackbar({
                message: data.message,
                timeout: 3000,
                position: "top",
            });
            $$('.chat-buttons-card button').removeAttr('disabled');
        }
    })
}

function clearAll() {
    $$("#chat-install-form").find("input").val("");
}
