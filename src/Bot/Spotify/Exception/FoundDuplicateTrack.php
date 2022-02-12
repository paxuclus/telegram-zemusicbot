<?php
declare(strict_types=1);

namespace Lala\Zemusibot\Bot\Spotify\Exception;

class FoundDuplicateTrack extends \Exception
{

    public function __construct(
        string $message,
        int $code,
        ?\Throwable $previous,
        private string $trackId,
        private string $originalMessageId
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getTrackId(): string
    {
        return $this->trackId;
    }

    public function getOriginalMessageId(): string
    {
        return $this->originalMessageId;
    }

}