<?php
declare(strict_types=1);

namespace Lala\Zemusibot\Bot;

use Lala\Zemusibot\Bot\Command\SpotifyDuplicateCommand;
use Lala\Zemusibot\Bot\Exception\CouldNotRegisterWebHook;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

final class Bot
{

    private Telegram $telegram;

    public function __construct(
        private string $apiKey,
        private string $username
    ) {
        $this->telegram = new Telegram($this->apiKey, $this->username);

        $this->telegram->setCommandsPaths([]);

        $this->telegram->addCommandClass(
            SpotifyDuplicateCommand::class
        );
    }

    /**
     * @throws TelegramException
     * @throws CouldNotRegisterWebHook
     */
    public function setWebHookUrl(string $url): string
    {
        $response = $this->telegram->setWebhook($url, [
            'allowed_updates' => [
                Update::TYPE_MESSAGE,
                Update::TYPE_EDITED_MESSAGE,
            ]
        ]);

        if (!$response->isOk()) {
            throw new CouldNotRegisterWebHook(
                sprintf(
                    '%d: %s',
                    $response->getErrorCode(),
                    $response->getDescription()
                ),
                1644621918
            );
        }

        return $response->getDescription();
    }

    /**
     * @throws TelegramException
     */
    public function handleWebhook(): void
    {
        $this->telegram->handle();
    }

    public static function createFromEnvironment(): self
    {
        $apiKey = getenv('TELEGRAM_BOT_TOKEN');
        $username = getenv('TELEGRAM_BOT_USERNAME');

        return new self($apiKey, $username);
    }

}