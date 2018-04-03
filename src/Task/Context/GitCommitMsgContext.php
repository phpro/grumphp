<?php

declare(strict_types=1);

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class GitCommitMsgContext implements ContextInterface
{
    private $files;
    private $commitMessage;
    private $userName;
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

    public function getFiles(): FilesCollection
    {
        return $this->files;
    }
}
