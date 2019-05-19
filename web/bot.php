<?php
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "printing params";
    print_r($_POST);
} else {
    echo "You are not a bot, are you?";
    error_log("This should be logged");
}
?>