<?php

namespace Backend\Modules\Compression\Domain\CompressionHistory;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="CompressionHistoryRepository")
 * @ORM\HasLifecycleCallbacks
 */
class CompressionHistory
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $filename;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $path;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $originalSize;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $compressedSize;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $checksum;

    /**
     * @var DateTime
     * @ORM\Column(type="datetime")
     */
    private $compressedOn;

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->compressedOn = new DateTime();
    }
}
