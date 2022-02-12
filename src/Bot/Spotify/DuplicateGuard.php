<?php
declare(strict_types=1);

namespace Lala\Zemusibot\Bot\Spotify;

use Lala\Zemusibot\Bot\Spotify\Exception\FoundDuplicateTrack;

final class DuplicateGuard
{

    private const FILE_PATH = __DIR__ . '/../../../data/spotify_track_list';

    /**
     * @throws FoundDuplicateTrack
     */
    public function checkAndRegister(string $trackId, string $messageId): void
    {
        self::checkTrack($trackId);
        self::addTrack($trackId, $messageId);
    }

    /**
     * @throws FoundDuplicateTrack
     */
    private static function checkTrack(string $trackId): void
    {
        $contents = file_get_contents(self::FILE_PATH);
        if ($contents === false) {
            return;
        }

        $lines = explode(PHP_EOL, $contents);

        foreach ($lines as $line) {
            $parts = explode("\t", $line);

            if ($parts[0] === $trackId) {
                throw new FoundDuplicateTrack(
                    'Found duplicate track ' . $trackId,
                    1644626275,
                    null,
                    $trackId,
                    $parts[1]
                );
            }
        }
    }

    private static function addTrack(string $trackId, string $messageId): void
    {
        file_put_contents(
            self::FILE_PATH,
            "{$trackId}\t{$messageId}" . PHP_EOL,
            FILE_APPEND
        );
    }

}