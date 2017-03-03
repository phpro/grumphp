<?php

namespace GrumPHP\Task\Context;

use GrumPHP\Collection\FilesCollection;

class GitCommitMsgContext implements ContextInterface
{
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
    public function __construct($commitMessage, $userName, $userEmail)
    {
        $this->commitMessage = $commitMessage;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

    /**
     * @return string
     */
    public function getCommitMessage()
    {
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

    public function getFiles()
    {
        return new FilesCollection;
    }
}
