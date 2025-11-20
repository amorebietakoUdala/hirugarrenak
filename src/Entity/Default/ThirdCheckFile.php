<?php

namespace App\Entity\Default;

use App\Repository\Default\ThirdCheckFileRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThirdCheckFileRepository::class)]
class ThirdCheckFile
{
    final public const STATUS_UNPROCESSED = 0;
    final public const STATUS_PROCESSING = 1;
    final public const STATUS_PROCESSED = 2;
    final public const STATUS_INVALID = 3;
    final public const STATUS_FAILED = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $fileName = null;

    #[ORM\Column]
    private ?\DateTime $receptionDate = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $processedDate = null;

    #[ORM\Column(nullable: true)]
    private ?int $status = null;

    public function __construct()
    {
        $this->receptionDate = new \DateTime();   
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getReceptionDate(): ?\DateTime
    {
        return $this->receptionDate;
    }

    public function setReceptionDate(\DateTime $receptionDate): static
    {
        $this->receptionDate = $receptionDate;

        return $this;
    }

    public function getProcessedDate(): ?\DateTime
    {
        return $this->processedDate;
    }

    public function setProcessedDate(?\DateTime $processedDate): static
    {
        $this->processedDate = $processedDate;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public static function createThirdCheckFile(array $data)
    {
        $thirdCheckFile = new self();
        $thirdCheckFile->setFileName($data['thirdFileName']);
        $thirdCheckFile->setReceptionDate(new \DateTime());
        $thirdCheckFile->setStatus(self::STATUS_UNPROCESSED);

        return $thirdCheckFile;
    }

}
