<?php

namespace GrumPHP\IO;

use Symfony\Component\Console\Output\ConsoleSectionOutput;

class GitHubActionsIO extends ConsoleIO
{
    public function startGroup(string $title): void
    {
        $this->write(['::group::' . $title]);
        parent::startGroup($title);
    }

    public function endGroup(): void
    {
        parent::endGroup();
        $this->write(['::endgroup::']);
    }
}
