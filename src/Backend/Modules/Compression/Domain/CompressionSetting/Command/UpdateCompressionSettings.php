<?php

namespace Backend\Modules\Compression\Domain\CompressionSetting\Command;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UpdateCompressionSettings
 * @package Backend\Modules\Compression\Domain\CompressionSetting\Command
 */
final class UpdateCompressionSettings
{
    /**
     * @var string
     * @Assert\NotBlank(message="err.FieldIsRequired")
     */
    public $folders;

    /**
     * @return array
     */
    public function getFoldersArray(): array
    {
        return explode(',', $this->folders);
    }
}
