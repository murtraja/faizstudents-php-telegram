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
    handleMessage($message, $mobile);
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}

function handleMessage($message, $mobile) {
    $chatId = getChatId($message);
    $userInput = $message->text;
    $userInputArgs = explode(" ", $userInput);
    $countInputArgs = sizeof($userInputArgs);
    if($countInputArgs === 3 || $countInputArgs === 4) {
        $word = $userInputArgs[0];
        if(!isValidNumber($word)) {
            sendInvalidNumberMessage($chatId, $word);
            return;
        }
        $thali = $word;
        $word = $userInputArgs[1];
        if(!isValidNumber($word)) {
            sendInvalidNumberMessage($chatId, $word);
            return;
        }
        $amount = $word;

        $word = strtolower($userInputArgs[2]);
        if(!isValidReceiptType($word)) {
            sendInvalidReceiptTypeMessage($chatId, $word);
            return;
        }
        $receipt_type = $word;

        $postParams = array(
            "receipt_thali" =>  $thali,
            "receipt_amount" => $amount,
            "mobile" => $mobile,
            "payment" => ucfirst($receipt_type), # 'Cash' or 'Bank'
        );
        if($receipt_type == 'bank') {
            if ($countInputArgs !== 4) {
                sendTextMessage($chatId, "Please provide transaction id also. <thali> <amount> bank <tran id>");
                return;
            }
            $postParams["transaction_id"] = $userInputArgs[3];
        }
        $response = getServerReply($postParams);
        sendTextMessage($chatId, $response);
    } elseif (sizeof($userInputArgs) === 1) {
        $word = $userInputArgs[0];
        if(!isValidNumber($word)) {
            sendInvalidNumberMessage($chatId, $word);
            return;
        }
        $thali = $word;
        $name = "Mustafa bhai Shaikh Hatim bhai Manawarwala";
        $mobile_no = "9049378652";
        $active = "No";
        $transporter = "Nasir bhai";
        $address = "B1-304, Kingston Serene, Autadwadi, Handewadi, Near Pinacho Restaurant, Undri";
        $start_date = getHijriYear();
        $stop_date = "Invalid Date";
        $prev_year_pending = "24700";
        $cur_year_takhmeen = "30000";
        $paid = "0";
        $pending = "54700";
        $content = <<<CONTENT
<b>Thali No.:</b> $thali

<b>Name:</b> $name

<b>Mob No.:</b> $mobile_no

<b>Active:</b> $active

<b>Transporter:</b> $transporter

<b>Address:</b> $address

<b>Start date:</b> $start_date

<b>Stop date:</b> $stop_date

<b>Prev year pending:</b> ₹$prev_year_pending

<b>Cur year takhmeen:</b> ₹$cur_year_takhmeen

<b>Paid:</b> ₹$paid

<b>Total pending:</b> ₹$pending

CONTENT;
        sendTextMessage($chatId, $content, false, true);
    } else {
        sendTextMessage($chatId, "Invalid number of arguments received. Should be either 3 or 4.");
    }
}

function getServerReply($data) {
    $url = 'https://faizstudents.com/users/_payhoob.php';

    // use key 'http' even if you send the request to https
    $headers = array(
        "Content-type: application/x-www-form-urlencoded",
        "User-Agent: Googlebot/2.1 (+http://www.googlebot.com/bot.html)"
    );
    $options = array(
        'http' => array(
            'header'  => $headers,
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { 
        /* Handle error */ 
        error_log("There was an error while posting to server");
        return "ERROR";
    }
    error_log("server response: ".$result);
    return $result;
}

function sendInvalidNumberMessage($chatId, $word) {
    $message = $word." is not a valid number. Please enter a valid number.";
    sendTextMessage($chatId, $message);
}

function isValidNumber($input) {
    if ($input[0] == '-') {
        return false;
    }
    return ctype_digit($input);
}

function sendInvalidReceiptTypeMessage($chatId, $word) {
    $message = $word." is not a valid receipt type. Enter either cash or bank.";
    sendTextMessage($chatId, $message);
}

function isValidReceiptType($word) {
    $word_lower = strtolower($word);
    if ($word_lower === 'cash' || $word_lower === 'bank') {
        return true;
    }
    return false;
}

function sendTextMessage($chatId, $text, $markdown=false, $html=false) {
    $params = array(
        "chat_id" => $chatId,
        "text" => $text,
    );
    if ($markdown) {
        $params['parse_mode'] = 'MarkdownV2';
    }
    if ($html) {
        $params['parse_mode'] = 'HTML';
    }
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

function getHijriYear() {
    $now = \GeniusTS\HijriDate\Date::now();
    return $now->format('Y');
}
?>