/**
 * 留言板 の Javascript
 */
var $$ = mdui.$;

function submit() {
    var form = $$("#chat-box-form");
    var $data = form.serialize();

    $$('.chat-buttons button').attr('disabled', true);

    $$.ajax({
        method: "POST",
        url: CHAT_CONFIG.site_url + "/v1/submit",
        data: $data,
        success: function (raw, textStatus, xhr) {
            const data = JSON.parse(raw);
            if ($$("#chat-list").find('.error-not-found').length > 0) {
                $$("#chat-list").find('.error-not-found').remove();
            }
            $$('.chat-buttons button').removeAttr('disabled');
            $$("#chat-list").prepend('<div class="chat-item-card mdui-card animation-fadein">\
            <div class="chat-item-avatar">\
              <img src = "' + data.data.avatar + '">\
            </div>\
            <div class="chat-item-content">\
              <div class="chat-item-author">' + data.data.nickname + '</div>\
                <div class="chat-item-time">' + data.data.time + '</div>\
                <div class="chat-item-text">' + data.data.content + '</div>\
              </div>\
          </div>');
            mdui.snackbar({
                message: data.message,
                timeout: 3000,
                position: "top",
            });
        },
        error: function (raw, textStatus) {
            $$('.chat-buttons button').removeAttr('disabled');
            const data = JSON.parse(raw.response);
            mdui.snackbar({
                message: data.message,
                timeout: 3000,
                position: "top",
            });
        }
    });
}

function getList() {
    var listBox = $$("#chat-list");

    $$.ajax({
        method: "GET",
        url: CHAT_CONFIG.site_url + "/v1/list",
        success: function (raw, textStatus, xhr) {
            const data = JSON.parse(raw);
            data.data.forEach(function (item) {
                if (listBox.find('.error-not-found').length > 0) {
                    $$("#chat-list").find('.error-not-found').remove();
                }

                setTimeout(function () {
                    listBox.prepend('<div class="chat-item-card mdui-card">\
                  <div class="chat-item-avatar">\
                    <img src = "' + item.avatar + '">\
                  </div>\
                  <div class="chat-item-content">\
                    <div class="chat-item-author">' + item.nickname + '</div>\
                      <div class="chat-item-time">' + item.time + '</div>\
                      <div class="chat-item-text">' + item.content + '</div>\
                    </div>\
                </div>');
                }, 10);
            })

            setTimeout(function () {
                listBox[0].style.opacity = '1';
            }, 100);
        },
        error: function (raw, textStatus) {
            listBox.append('<div class="mdui-card chat-item-card error-not-found"><img src="' + CHAT_CONFIG.site_url + '/static/img/404.png"><div>一条留言都没有</div></div>');
            setTimeout(function () {
                listBox[0].style.opacity = '1';
            }, 100);
        }
    });
}

window.addEventListener("load", function () {
    getList();
})
