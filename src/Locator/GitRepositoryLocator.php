<?php

declare(strict_types=1);

namespace GrumPHP\Locator;

use Gitonomy\Git\Repository;
use GrumPHP\Util\Paths;

class GitRepositoryLocator
{
    /**
     * @var Paths
     */
    private $paths;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths;
    }

    public function locate(array $options): Repository
    {
        return new Repository(
            $this->paths->getGitRepositoryDir(),
            array_merge(
                [
                    'working_dir' => $this->paths->getGitWorkingDir(),
                ],
                $options
            )
        );
    }
}
