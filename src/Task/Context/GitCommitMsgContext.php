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

    /**
     * @param string          $commitMessage
     * @param string          $userName
     * @param string          $userEmail
     */
    public function __construct(FilesCollection $files, string $commitMessage, string $userName, string $userEmail)
    {
        $this->files = $files;
        $this->commitMessage = $commitMessage;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function getCommitMessage(): string
    {
        return $this->commitMessage;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @return string
     */
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
