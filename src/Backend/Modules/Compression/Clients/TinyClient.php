<?php

namespace Backend\Modules\Compression\Clients;

use Common\ModulesSettings;
use Symfony\Component\Finder\SplFileInfo;
use Tinify\AccountException;
use Tinify\Tinify;
use TinyPng\TinyPng;
use function Tinify\validate as TinifyValidate;
use function Tinify\fromFile as TinifyFromFile;

/**
 * Class TinyClient
 * @package Backend\Modules\Compression\Clients
 */
class TinyClient
{
    private $tinyPng;

    /**
     * TinyClient constructor.
     * @param string | null $apiKey
     */
    public function __construct(?string $apiKey)
    {
        Tinify::setKey($apiKey);
        $this->tinyPng = new TinyPng($apiKey);
    }

    /**
     * @param ModulesSettings $settings
     * @return static
     */
    public static function createFromModuleSettings(ModulesSettings $settings): self
    {
        return new self($settings->get('Compression', 'api_key'));
    }

    /**
     * Perform a validation of the API key
     */
    public function isValidApiKey(): bool
    {
        try {
            TinifyValidate();
        } catch (AccountException $e) {
            // Validation of API key failed. Probably not a valid api key or account limit reached.
            return false;
        }

        return true;
    }

    /**
     * The API client automatically keeps track of the number of compressions you have made this month
     * @return int|null Number of images compressed this month
     */
    public function getMonthlyCompressionCount(): ?int
    {
        // Need to make any request prior to the compression count to actually fetch it.
        if (!$this->isValidApiKey()) {
            return null;
        }

        return Tinify::getCompressionCount();
    }

    /**
     * @param SplFileInfo $file
     */
    public function shrinkImage(SplFileInfo $file): void
    {
//        $source = TinifyFromFile($file->getRealPath());
//        $source->toFile($file->getRealPath());

        $this->tinyPng
            ->fromFile($file->getRealPath())
            ->toFile($file->getRealPath());
    }
}
