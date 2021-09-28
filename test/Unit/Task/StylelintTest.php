<?php

declare(strict_types=1);

namespace GrumPHP\Test\Unit\Task;

use GrumPHP\Runner\FixableTaskResult;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Stylelint;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractExternalTaskTestCase;

class StylelintTest extends AbstractExternalTaskTestCase
{
    protected function provideTask(): TaskInterface
    {
        return new Stylelint(
            $this->processBuilder->reveal(),
            $this->formatter->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                // Task config options
                'bin' => null,
                'triggered_by' => ['css', 'scss', 'sass', 'less', 'sss'],
                'whitelist_patterns' => [],

                // Stylelint native config options
                'config' => null,
                'config_basedir' => null,
                'ignore_path' => null,
                'ignore_pattern' => null,
                'syntax' => null,
                'custom_syntax' => null,
                'ignore_disables' => null,
                'disable_default_ignores' => null,
                'cache' => null,
                'cache_location' => null,
                'formatter' => null,
                'custom_formatter' => null,
                'quiet' => null,
                'color' => null,
                'report_needless_disables' => null,
                'report_invalid_scope_disables' => null,
                'report_descriptionless_disables' => null,
                'max_warnings' => null,
                'output_file' => null,
            ]
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            true,
            $this->mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            true,
            $this->mockContext(GitPreCommitContext::class)
        ];

        yield 'other' => [
            false,
            $this->mockContext()
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        yield 'exitCode1' => [
            [],
            $this->mockContext(RunContext::class, ['hello.css']),
            function () {
                $this->mockProcessBuilder('stylelint', $process = $this->mockProcess(1));

                $this->formatter->format($process)->willReturn($message = 'message');
            },
            'message',
            FixableTaskResult::class
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'exitCode0' => [
            [],
            $this->mockContext(RunContext::class, ['hello.css']),
            function () {
                $this->mockProcessBuilder('stylelint', $this->mockProcess(0));
            }
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        yield 'no-files' => [
            [],
            $this->mockContext(RunContext::class),
            function () {}
        ];
        yield 'no-files-after-triggered-by' => [
            [],
            $this->mockContext(RunContext::class, ['notajsfile.txt']),
            function () {}
        ];
        yield 'no-files-after-whitelist-patterns' => [
            [
                'whitelist_patterns' => ['/^resources\/css\/(.*)/'],
            ],
            $this->mockContext(RunContext::class, ['resources/dont/find/this/file.css']),
            function () {}
        ];
    }

    public function provideExternalTaskRuns(): iterable
    {
        yield 'precommit' => [
            [],
            $this->mockContext(GitPreCommitContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'bin' => [
            [
                'bin' => 'node_modules/.bin/stylelint',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                'node_modules/.bin/stylelint',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'config' => [
            [
                'config' => '.stylelintrc.json',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--config=.stylelintrc.json',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'config_basedir' => [
            [
                'config_basedir' => 'path/to/base/dir',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--config-basedir=path/to/base/dir',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'ignore_path' => [
            [
                'ignore_path' => 'path/to/.ignorefile',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--ignore-path=path/to/.ignorefile',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'ignore_pattern' => [
            [
                'ignore_pattern' => 'pattern',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--ignore-pattern=pattern',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'syntax' => [
            [
                'syntax' => 'css',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--syntax=css',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'custom_syntax' => [
            [
                'custom_syntax' => 'mysyntax',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--custom-syntax=mysyntax',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'ignore_disables' => [
            [
                'ignore_disables' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--ignore-disables',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'disable_default_ignores' => [
            [
                'disable_default_ignores' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--disable-default-ignores',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'cache' => [
            [
                'cache' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--cache',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'cache_location' => [
            [
                'cache_location' => 'path/to/cache',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--cache-location=path/to/cache',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'formatter' => [
            [
                'formatter' => 'string',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--formatter=string',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'custom_formatter' => [
            [
                'custom_formatter' => 'myformatter',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--custom-formatter=myformatter',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'quiet' => [
            [
                'quiet' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--quiet',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'color' => [
            [
                'color' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--color',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'no_color' => [
            [
                'color' => false,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--no-color',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'report_needless_disables' => [
            [
                'report_needless_disables' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--report-needless-disables',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'report_invalid_scope_disables' => [
            [
                'report_invalid_scope_disables' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--report-invalid-scope-disables',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'report_descriptionless_disables' => [
            [
                'report_descriptionless_disables' => true,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--report-descriptionless-disables',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'max_warnings' => [
            [
                'max_warnings' => 10,
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--max-warnings=10',
                'hello.css',
                'hello2.css',
            ]
        ];
        yield 'output_file' => [
            [
                'output_file' => 'path/to/outputfile',
            ],
            $this->mockContext(RunContext::class, ['hello.css', 'hello2.css']),
            'stylelint',
            [
                '--output-file=path/to/outputfile',
                'hello.css',
                'hello2.css',
            ]
        ];
    }
}
