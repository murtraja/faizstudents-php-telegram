<?php

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = json_decode( file_get_contents( 'php://input' ) );
    error_log("printing params");
    error_log(print_r($message, true));
    sendTextMessage($message, 'hello there');
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}

function sendTextMessage($message, $text) {
    $chat_id = $message->chat->id;
    $params = array(
        "chat_id" => $chat_id,
        "text" => $text,
    );
    sendMessage($message, $params);
}

function sendMessage($message, $params) {
    $API_TOKEN = getenv('API_TOKEN');
    $API_URL = 'https://api.telegram.org/bot'.$API_TOKEN.'/';
    $sendMessageUrl = $API_URL.'sendMessage/?'.http_build_query($params);
    error_log('sendMessageUrl:'.$sendMessageUrl);
    $response = file_get_contents($sendMessageUrl);
    error_log("print response");
    error_log(print_r($response, true));
}
?>