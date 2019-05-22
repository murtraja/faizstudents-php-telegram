<?php
require_once '../vendor/autoload.php';
use Predis\Client;

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode( file_get_contents( 'php://input' ) );
    printVariable("Input:", $input);

    $message = $input->message;
    
    $chatId = getChatId($message);
    printVariable("Chat Id:", $chatId);
    
    $mobile = getMobile($chatId);
    if($mobile === false) {
        handleAuthentication($message);
        exit();
    }
    handleMessage($message);
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}

function handleMessage($message) {
    $chatId = getChatId($message);
    sendTextMessage($chatId, "Authenticated");
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

function sendMessage($params) {
    $API_TOKEN = getenv('API_TOKEN');
    $API_URL = 'https://api.telegram.org/bot'.$API_TOKEN.'/';
    $sendMessageUrl = $API_URL.'sendMessage?'.http_build_query($params);
    error_log('sendMessageUrl:'.$sendMessageUrl);
    $response = file_get_contents($sendMessageUrl);
    error_log("print response");
    error_log(print_r($response, true));
}

function getMobile($chatId) {
    $chatId = (string) $chatId;
    $redis = new Predis\Client(getenv('REDIS_URL'));
    if($redis->exists($chatId)) {
        $mobile = $redis->get($chatId);
        return $mobile;
    }
    return false;
}

function storeMobile($chatId, $mobile) {
    $chatId = (string) $chatId;
    $mobile = (string) $mobile;
    $redis = new Predis\Client(getenv('REDIS_URL'));
    $redis->set($chatId, $mobile);
}

function printVariable($text, $variable) {
    error_log($text);
    error_log(print_r($variable, true));
}

function sendPhoneNumberRequest($chatId) {
    $keyboard = array(array(array(
        "text" => "Send phone number",
        "request_contact" => true
    )));

    $replyMarkup = array(
        "one_time_keyboard" => true,
        "keyboard" => $keyboard
    );

    $params = array(
        "chat_id" => $chatId,
        "text" => "Please click on send phone number",
        "reply_markup" => json_encode($replyMarkup)
    );
    sendMessage($params);
}

function receivePhoneNumber($chatId, $contact) {
    $mobile = $contact->phone_number;
    storeMobile($chatId, $mobile);
    sendTextMessage($chatId, "Thank you for your number ".$mobile);
}

function handleAuthentication($message) {
    $chatId = getChatId($message);
    $contact = $message->contact;
    if($contact) {
        receivePhoneNumber($chatId, $contact);
        return;
    }
    sendPhoneNumberRequest($chatId);
}
?>