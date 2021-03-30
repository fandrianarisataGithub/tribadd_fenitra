(function () {
    'use strict';

    var _receiver = document.getElementById('ws-content-receiver');
    var ws = new WebSocket('ws://'+wsUrl);

    var defaultChannel = 'general';
    var botName = 'ChatBot';
    const elem = document.querySelector('.msg_card_body')

    var addMessageToChannel = function(message) {
        var user = JSON.parse(message).user
        var contenu = JSON.parse(message).message
        var photo = JSON.parse(message).photo
        if(user === 'ChatBot') {

        } else {
            let src = photo
            const other = `
                            <div class="d-flex justify-content-start message">
                                <div class="img_cont_msg position-relative">
                                    <img src="${src}" class="rounded-circle user_img_msg">
                                    <span class="online_icon"></span>
                                </div>
                                <div class="msg_cotainer">
                                    <span class="msg_user">${user}</span>
                                    ${contenu}
                                    <span class="msg_time"></span>
                                </div>
                            </div>`
            const me = `
                        <div class="d-flex justify-content-end message">
                            <div class="msg_cotainer_send">
                                <span class="msg_user">${user}</span>
                                ${contenu}
                                <span class="msg_time_send"></span>
                            </div>
                            <div class="img_cont_msg">
                                <img src="${src}" class="rounded-circle user_img_msg">
                            </div>
                        </div>`
            _receiver.innerHTML += (user === userName) ? me : other;
        }
        const height = elem.scrollHeight - elem.offsetHeight
        if (elem.scrollTop > height - 200) {
            $(elem).animate({ scrollTop: elem.scrollHeight }, 100);
        }
    };

    var botMessageToGeneral = function (message) {
        return addMessageToChannel(JSON.stringify({
            action: 'message',
            channel: defaultChannel,
            user: botName,
            message: message
        }));
    };

    ws.onopen = function () {
        ws.send(JSON.stringify({
            action: 'subscribe',
            channel: defaultChannel,
            user: userName
        }));
    };

    ws.onmessage = function (event) {
        addMessageToChannel(event.data);
    };

    ws.onclose = function () {
        botMessageToGeneral('Connection closed');
    };

    ws.onerror = function () {
        botMessageToGeneral('An error occured!');
    };
    var _textInput = document.getElementById('ws-content-to-send');
    var _textSender = document.getElementById('ws-send-content');
    var enterKeyCode = 13;

    var sendTextInputContent = function () {
        // Get text input content
        var content = _textInput.value;

        // Send it to WS
        ws.send(JSON.stringify({
            action: 'message',
            user: userName,
            message: content,
            photo: document.querySelector('.sender-img').getAttribute('src'),
            channel: 'general',
        }));

        $("#ws-send-content").attr("disabled", "disabled");
        // let xhr = new XMLHttpRequest();
        var contenu = document.getElementById('ws-content-to-send').value;
        if(contenu !== ""){
            $.ajax({
                url : "/user/add/message",
                type : "POST",
                data: {
                    contenu : contenu
                },
                cache: false,
                success: function(data, status){
                    if(status === 'success'){
                        $("#ws-send-content").removeAttr("disabled");
                        document.getElementById('ws-content-to-send').value = '';
                        $(document.querySelector('.msg_card_body')).animate({ scrollTop: document.querySelector('.msg_card_body').scrollHeight }, 100);
                    }
                }
            })
        }

    };

    _textSender.onclick = sendTextInputContent;
    _textInput.onkeyup = function(e) {
        // Check for Enter key
        if (e.keyCode === enterKeyCode && _textInput.value !== '') {
            e.preventDefault()
            sendTextInputContent();
        }
    };
})();