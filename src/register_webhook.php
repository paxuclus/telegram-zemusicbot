<?php
declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

use Lala\Zemusibot\Bot\Bot;

$webhookUrl = getenv('TELEGRAM_BOT_WEBHOOK_URL');

$bot = Bot::createFromEnvironment();
$description = $bot->setWebHookUrl($webhookUrl);
echo $description . PHP_EOL;
