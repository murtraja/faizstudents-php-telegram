<?php
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "printing params";
    error_log(print_r($_POST, true));
    $update = json_decode( file_get_contents( 'php://input' ) );
    error_log($update);
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}
?>