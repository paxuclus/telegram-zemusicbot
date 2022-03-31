<?php
declare(strict_types=1);

namespace Lala\Zemusibot\Bot\Command;

use GuzzleHttp\Client;
use Lala\Zemusibot\Bot\Spotify\DuplicateGuard;
use Lala\Zemusibot\Bot\Spotify\Exception\FoundDuplicateTrack;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

final class SpotifyDuplicateCommand extends SystemCommand
{

    private const TRACK_PATTERN = '#https://open\.spotify\.com/track/(?P<trackId>[a-z0-9]+)\??#i';

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
        if (preg_match(self::TRACK_PATTERN, $text, $matches) !== 1) {
            return Request::emptyResponse();
        }
        $trackId = $matches['trackId'];

        $duplicateGuard = new DuplicateGuard();

        try {
            $duplicateGuard->checkAndRegister(
                (string)$message->getChat()->getId(),
                $trackId,
                strval($message->getMessageId())
            );

            try {
                $data = self::fetchTrackData($trackId);
                return Request::sendPhoto([
                    'chat_id' => $message->getChat()->getId(),
                    'photo' => $data['image'],
                    'caption' => $data['title'] . PHP_EOL . $data['uri'],
                    'reply_to_message_id' => $message->getMessageId(),
                    'disable_notification' => true,
                ]);
            } catch (\Exception $e) {
                return Request::sendMessage([
                    'chat_id' => $message->getChat()->getId(),
                    'text' => 'Could not fetch song preview. FFS! 😡',
                    'reply_to_message_id' => $message->getMessageId(),
                    'disable_notification' => true,
                ]);
            }
        } catch (FoundDuplicateTrack $e) {
            Request::deleteMessage([
                'chat_id' => $message->getChat()->getId(),
                'message_id' => $message->getMessageId(),
            ]);

            return Request::sendMessage([
                'chat_id' => $message->getChat()->getId(),
                'text' => 'Got upvote by @' . $message->getFrom()->getUsername(),
                'reply_to_message_id' => $e->getOriginalMessageId(),
                'disable_notification' => true,
            ]);
        }
    }

    private static function fetchTrackData(string $trackId): array
    {
        $uri = sprintf('https://open.spotify.com/track/%s', $trackId);

        $client = new Client();

        $response = $client->get($uri);
        $html = $response->getBody()->getContents();
        if (str_starts_with($html, '<!DOCTYPE html>')) {
            $html = substr($html, strlen('<!DOCTYPE html>'));
        }

        $dom = new \DOMDocument();
        $dom->loadHTML($html, \LIBXML_NOWARNING | \LIBXML_NOERROR);
        $xpath = new \DOMXPath($dom);

        $title = $xpath->query('/html/head/title')->getIterator()->current();
        assert($title instanceof \DOMElement);

        $image = $xpath->query('/html/head/meta[@property="og:image"]')->getIterator()->current();
        assert($image instanceof \DOMElement);

        return [
            'title' => (string)$title->textContent,
            'image' => (string)$image->getAttribute('content'),
            'uri' => sprintf('%s?si=zemusibot', $uri),
        ];
    }

}
