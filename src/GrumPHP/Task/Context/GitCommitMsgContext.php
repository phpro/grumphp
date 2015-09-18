<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

/**
 * Class GitCommitMsgContext
 *
 * @package GrumPHP\Task\Context
 */
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
    private $commitMessageFile;

    /**
     * @var string
     */
    private $userName;

    /**
     * @var string
     */
    private $userEmail;

    /**
     * @param FilesCollection $files
     * @param string          $commitMessageFile
     * @param string          $userName
     * @param string          $userEmail
     */
    public function __construct(FilesCollection $files, $commitMessageFile, $userName, $userEmail)
    {
        $this->files = $files;
        $this->commitMessageFile = $commitMessageFile;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function getCommitMessage()
    {
        if (is_null($this->commitMessage)) {
            $this->commitMessage = file_get_contents($this->commitMessageFile);
        }

        return $this->commitMessage;
    }

    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->userEmail;
    }

    /**
     * @return FilesCollection
     */
    public function getFiles()
    {
        return $this->files;
    }
}
