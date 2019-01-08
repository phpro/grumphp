<?php

declare(strict_types=1);

namespace GrumPHP\Event;

final class StageEvents
{
    const STAGE_RUN = 'grumphp.stage.run';
    const STAGE_COMPLETE = 'grumphp.stage.complete';
    const STAGE_FAILED = 'grumphp.stage.failed';
}
