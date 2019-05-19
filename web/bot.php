<?php
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo $_POST;
} else {
    echo "You are not a bot, are you?";
}
?>