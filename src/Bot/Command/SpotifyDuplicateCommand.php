<?php
declare(strict_types=1);

namespace Lala\Zemusibot\Bot\Command;

use Lala\Zemusibot\Bot\Spotify\DuplicateGuard;
use Lala\Zemusibot\Bot\Spotify\Exception\FoundDuplicateTrack;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class SpotifyDuplicateCommand extends SystemCommand
{

    protected $name = 'genericmessage';
    protected $description = 'Check for duplicate Spotify Links';
    protected $version = '1.2.0';

    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $text = $message->getText();

        if ($text === null) {
            return Request::emptyResponse();
        }

        // https://open.spotify.com/track/0CSvdqfPR3Z3X3jGcaLBA6?si=d1043126ec964de5
        if (preg_match('#https://open.spotify.com/track/(?P<trackId>[a-z0-9]+)\??#i', $text, $matches) !== 1) {
            return Request::emptyResponse();
        }
        $trackId = $matches['trackId'];

        $duplicateGuard = new DuplicateGuard();

        try {
            $duplicateGuard->checkAndRegister($trackId, strval($message->getMessageId()));

            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'Very nice!',
            ]);
        } catch (FoundDuplicateTrack $e) {
            return Request::sendMessage([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'text' => 'You dumbass! Already mentioned in ' . $e->getOriginalMessageId(),
            ]);
        }
    }
}
