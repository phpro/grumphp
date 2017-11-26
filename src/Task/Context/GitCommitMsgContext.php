<?php declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class GitCommitMsgContext implements ContextInterface
{
    /**
     * @var FilesCollection
     */
    private $files;

    /**
     * @var string
     */
    private $commitMessage = null;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $userEmail;

    public function __construct(FilesCollection $files, string $commitMessage, string $userName, string $userEmail)
    {
        $this->files = $files;
        $this->commitMessage = $commitMessage;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    public function getCommitMessage(): string
    {
        return $this->commitMessage;
    }

    public function getUserName(): string
    {
        return $this->userName;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    /**
     * @return FilesCollection
     */
    public function getFiles(): FilesCollection
    {
        return $this->files;
    }
}
