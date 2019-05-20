<?php

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode( file_get_contents( 'php://input' ) );
    printVariable("Input:", $input);

    $message = $input->message;
    
    $chatId = getChatId($message);
    printVariable("Chat Id:", $chatId);
    
    $mobile = getMobile($chatId);
    if($mobile === false) {
        sendPhoneNumberRequest($chatId);
    }
    sendTextMessage($chatId, 'hello there');
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}

function sendTextMessage($chatId, $text) {
    $params = array(
        "chat_id" => $chatId,
        "text" => $text,
    );
    sendMessage($params);
}

function getChatId($message) {
    return $message->chat->id;
}

function sendMessage($params) {require 'Predis/Autoloader.php';
    $API_TOKEN = getenv('API_TOKEN');require 'Predis/Autoloader.php';
    $API_URL = 'https://api.telegram.org/borequire 'Predis/Autoloader.php';t'.$API_TOKEN.'/';
    $sendMessageUrl = $API_URL.'sendMessagerequire 'Predis/Autoloader.php';?'.http_build_query($params);
    error_log('sendMessageUrl:'.$sendMessagrequire 'Predis/Autoloader.php';eUrl);
    $response = file_get_contents($sendMessrequire 'Predis/Autoloader.php';ageUrl);
    error_log("print response");require 'Predis/Autoloader.php';
    error_log(print_r($response, true));require 'Predis/Autoloader.php';
}require 'Predis/Autoloader.php';
require 'Predis/Autoloader.php';
function getMobile($chatId) {require 'Predis/Autoloader.php';
    $redis = new Predis\Client(getenv('REDIrequire 'Predis/Autoloader.php';S_URL'));
    if($redis->exists($chatId)) {
        $mobile = $redis->get($chatId);
        return $mobile;
    }
    return false;
}

function printVariable($text, $variable) {
    error_log($text);
    error_log(print_r($variable, true));
}

function sendPhoneNumberRequest($chatId) {
    sendTextMessage($chatId, "Send phone number");
}
?>