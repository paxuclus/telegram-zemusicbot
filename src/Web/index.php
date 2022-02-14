<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../vendor/autoload.php');

use Lala\Zemusibot\Bot\Bot;
use Longman\TelegramBot\Exception\TelegramException;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    exit(0);
}

$bot = Bot::createFromEnvironment();

try {
    $bot->handleWebhook();
} catch (TelegramException $e) {
}