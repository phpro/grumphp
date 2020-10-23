<?php

declare(strict_types=1);

namespace GrumPHPTest\Unit\Task\Git;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Git\GitRepository;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitCommitMsgContext;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use GrumPHP\Task\Git\CommitMessage;
use GrumPHP\Task\TaskInterface;
use GrumPHP\Test\Task\AbstractTaskTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CommitMessageTest extends AbstractTaskTestCase
{
    /**
     * @var ObjectProphecy & GitRepository
     */
    protected $repository;

    protected function provideTask(): TaskInterface
    {
        $this->repository = $this->prophesize(GitRepository::class);
        $this->repository->run('config', ['--get', 'core.commentChar'])->willReturn('#');
        $this->repository->tryToRunWithFallback(Argument::cetera())->will(function (array $arguments) {
            return $arguments[0]();
        });

        return new CommitMessage(
            $this->repository->reveal()
        );
    }

    public function provideConfigurableOptions(): iterable
    {
        yield 'defaults' => [
            [],
            [
                'allow_empty_message' => false,
                'enforce_capitalized_subject' => true,
                'enforce_no_subject_punctuations' => false,
                'enforce_no_subject_trailing_period' => true,
                'enforce_single_lined_subject' => true,
                'max_body_width' => 72,
                'max_subject_width' => 60,
                'case_insensitive' => true,
                'multiline' => true,
                'type_scope_conventions' => [],
                'matchers' => [],
                'additional_modifiers' => '',
            ]
        ];
    }

    public function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            false,
            $this->mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            false,
            $this->mockContext(GitPreCommitContext::class)
        ];

        yield 'commit-msg-context' => [
            true,
            $this->mockContext(GitCommitMsgContext::class)
        ];

        yield 'other' => [
            false,
            $this->mockContext()
        ];
    }

    public function provideFailsOnStuff(): iterable
    {
        yield 'dont-allow_empty_message' => [
            [
                'allow_empty_message' => false,
            ],
            $this->mockCommitMsgContext(''),
            function () {
            },
            'Commit message should not be empty.'
        ];
        yield 'dont-allow_trimmed_empty_message' => [
            [
                'allow_empty_message' => false,
            ],
            $this->mockCommitMsgContext('     '),
            function () {
            },
            'Commit message should not be empty.'
        ];
        yield 'enforce_capitalized_subject' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('no capital subject')),
            function () {
            },
            'Subject should start with a capital letter.'
        ];
        yield 'enforce_capitalized_subject_punctuation' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('"no" capital subject')),
            function () {
            },
            'Subject should start with a capital letter.'
        ];
        yield 'enforce_capitalized_subject_utf8' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('"ärsgäng" capital subject')),
            function () {
            },
            'Subject should start with a capital letter.'
        ];
        yield 'enforce_no_subject_punctuations' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Some . punctiation')),
            function () {
            },
            'Please omit all punctuations from commit message subject.'
        ];
        yield 'enforce_no_subject_punctuations_comma' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Some , punctiation')),
            function () {
            },
            'Please omit all punctuations from commit message subject.'
        ];
        yield 'enforce_no_subject_punctuations_exclamation' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Some ! punctiation')),
            function () {
            },
            'Please omit all punctuations from commit message subject.'
        ];
        yield 'enforce_no_subject_punctuations_question' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Some ? punctiation')),
            function () {
            },
            'Please omit all punctuations from commit message subject.'
        ];
        yield 'enforce_no_subject_trailing_period' => [
            [
                'enforce_no_subject_trailing_period' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Subject ending with.')),
            function () {
            },
            'Please omit trailing period from commit message subject.'
        ];
        yield 'enforce_single_lined_subject-with_body' => [
            [
                'enforce_single_lined_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage(
                $this->buildMultiLineString('Subject line', 'subject line 2'),
                'comment line1',
                'comment line2'
            )),
            function () {
            },
            'Subject should be one line and followed by a blank line.'
        ];
        yield 'enforce_text_with_regular' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $this->mockCommitMsgContext($this->buildMessage(
                'Subject 1234567891011',
                'Body 1234567891011',
                'Body ok',
                'Body 1110987654321'
            )),
            function () {
            },
            $this->buildMultiLineString(
                'Please keep the subject <= 10 characters.',
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            )
        ];
        yield 'enforce_text_with_long_comments' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $this->mockCommitMsgContext($this->wrapComments(
                $this->buildMessage(
                    'Subject',
                    'Body 1234567891011',
                    'Body ok',
                    'Body 1110987654321'
                )
            )),
            function () {
            },
            $this->buildMultiLineString(
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            )
        ];
        yield 'enforce_text_with_ignore_below_comment' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $this->mockCommitMsgContext($this->addIgnoreBelowComment(
                $this->buildMessage(
                    'Subject',
                    'Body 1234567891011',
                    'Body ok',
                    'Body 1110987654321'
                )
            )),
            function () {
            },
            $this->buildMultiLineString(
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            )
        ];
        yield 'type_scope_conventions_not_set_in_message' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('doesnt match type scope convention')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
        yield 'type_scope_conventions_invalid_type' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('bug: doesnt match type scope convention')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
        yield 'type_scope_conventions_invalid_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('fix(api): doesnt match type scope convention')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
        yield 'it_fails_on_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST'],
            ],
            $this->mockCommitMsgContext('invalid'),
            function () {
            },
            $this->buildMultiLineString(
                'Rule not matched: "0" test',
                'Rule not matched: "1" *es*',
                'Rule not matched: "2" te[s][t]',
                'Rule not matched: "3" /^te(.*)/',
                'Rule not matched: "4" /(.*)st$/',
                'Rule not matched: "5" /t(e|a)st/',
                'Rule not matched: "6" TEST',
                'Original commit message: ',
                'invalid'
            )
        ];
        yield 'it_fails_on_names_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => [
                    'full' => 'test',
                    'partial' => '*es*'
                ],
            ],
            $this->mockCommitMsgContext('invalid'),
            function () {
            },
            $this->buildMultiLineString(
                'Rule not matched: "full" test',
                'Rule not matched: "partial" *es*',
                'Original commit message: ',
                'invalid'
            )
        ];
        yield 'it_applies_matchers_with_case_sensitive' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/TEST/'],
                'case_insensitive' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('test')),
            function () {
            },
            $this->buildMultiLineString(
                'Rule not matched: "0" /TEST/',
                'Original commit message: ',
                'test'
            )
        ];
        yield 'it_applies_matchers_with_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('you', 'me', 'there')),
            function () {
            },
            'Rule not matched: "0" /^hello/'
        ];
        yield 'it_applies_matchers_without_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello((.|[\n])*)bye$/'],
                'multiline' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('hello there bye', 'bye good hello')),
            function () {
            },
            'Rule not matched: "0" /^hello((.|[\n])*)bye$/'
        ];
        yield 'dont-enforce_capitalized_subject_fixup_without_subject' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('', 'only body')),
            function () {
            },
            'Subject should start with a capital letter.'
        ];
    }

    public function providePassesOnStuff(): iterable
    {
        yield 'allow_empty_message' => [
            [
                'allow_empty_message' => true,
            ],
            $this->mockCommitMsgContext(''),
            function () {
            }
        ];
        yield 'allow_trimmed_empty_message' => [
            [
                'allow_empty_message' => true,
            ],
            $this->mockCommitMsgContext('     '),
            function () {
            }
        ];
        yield 'allow_starts_with_comment' => [
            [
                'allow_empty_message' => false,
                'enforce_capitalized_subject' => false,
                'enforce_no_subject_trailing_period' => false,
                'enforce_single_lined_subject' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('# Some content', 'The body!')),
            function () {
            }
        ];
        yield 'allow_starts_with_custom_comment_char' => [
            [
                'allow_empty_message' => false,
                'enforce_capitalized_subject' => true,
                'enforce_no_subject_trailing_period' => false,
                'enforce_single_lined_subject' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('; some content', 'The body!')),
            function () {
                $this->repository->run('config', ['--get', 'core.commentChar'])->willReturn('; ');
            }
        ];
        yield 'dont-enforce_capitalized_subject' => [
            [
                'enforce_capitalized_subject' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('no capital subject')),
            function () {
            }
        ];
        yield 'dont-enforce_capitalized_subject_fixup' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->fixup('no capital subject')),
            function () {
            }
        ];
        yield 'dont-enforce_capitalized_subject_squash' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->squash('no capital subject')),
            function () {
            }
        ];
        yield 'enforce_capitalized_subject_special_utf8_char' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Årsgång')),
            function () {
            }
        ];
        yield 'enforce_capitalized_subject_punctuation' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('"Initial" commit')),
            function () {
            }
        ];
        yield 'dont-enforce_no_subject_punctuations' => [
            [
                'enforce_no_subject_punctuations' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Some . punctiation')),
            function () {
            }
        ];
        yield 'dont-enforce_no_subject_trailing_period' => [
            [
                'enforce_no_subject_trailing_period' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('Subject ending with.')),
            function () {
            }
        ];
        yield 'enforce_single_lined_subject-with_body' => [
            [
                'enforce_single_lined_subject' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage(
                'Subject line',
                'comment line1',
                'comment line2'
            )),
            function () {
            },
        ];
        yield 'dont-enforce_single_lined_subject-multiline' => [
            [
                'enforce_single_lined_subject' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage(
                'Subject line',
                'comment line1',
                'comment line2'
            )),
            function () {
            }
        ];
        yield 'enforce_text_with_special_prefix_fixup' => [
            [
                'max_subject_width' => 10,
            ],
            $this->mockCommitMsgContext($this->fixup('123456789')),
            function () {
            }
        ];
        yield 'enforce_text_with_special_prefix_squash' => [
            [
                'max_subject_width' => 10,
            ],
            $this->mockCommitMsgContext($this->squash('123456789')),
            function () {
            }
        ];
        yield 'enforce_text_with_ignore_below_comment' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $this->mockCommitMsgContext($this->addIgnoreBelowComment($this->buildMessage('Subject'))),
            function () {
            },
        ];
        yield 'empty-type_scope_conventions' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [],
            ],
            $this->mockCommitMsgContext($this->buildMessage('doesnt match type scope convention')),
            function () {
            },
        ];
        yield 'type_scope_conventions_match_type' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('fix: match type scope convention')),
            function () {
            },
        ];
        yield 'type_scope_conventions_match_type_without_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('fix: match type scope convention')),
            function () {
            },
        ];
        yield 'type_scope_conventions_match_type_with_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('fix(app): match type scope convention')),
            function () {
            },
        ];
        yield 'fixup_type_scope_conventions_match_type_without_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->fixup('fix: match type scope convention')),
            function () {
            },
        ];
        yield 'fixup_type_scope_conventions_match_type_with_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->fixup('fix(app): match type scope convention')),
            function () {
            },
        ];
        yield 'squash_type_scope_conventions_match_type_without_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->squash('fix: match type scope convention')),
            function () {
            },
        ];
        yield 'squash_type_scope_conventions_match_type_with_scope' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                    'scopes' => [
                        'app'
                    ]
                ],
            ],
            $this->mockCommitMsgContext($this->squash('fix(app): match type scope convention')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('Merge branch \'x\' into')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_branch_gitflow' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage("Merge branch 'release/x.y.z'")),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_tag_gitflow' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage("Merge tag 'x.y.z' into")),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_remote' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('Merge remote-tracking branch \'x\' into')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_PR' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->buildMessage('Merge pull request #123 into')),
            function () {
            },
        ];
        yield 'fixup_skip_type_scope_conventions_on_merge' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            $this->mockCommitMsgContext($this->fixup('Merge branch \'x\' into')),
            function () {
            },
        ];
        yield 'it_applies_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST'],
            ],
            $this->mockCommitMsgContext('test'),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_additional_modifiers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/.*ümlaut/'],
                'additional_modifiers' => 'u',
            ],
            $this->mockCommitMsgContext($this->buildMessage('message containing ümlaut')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_case_sensitive' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/TEST/'],
                'case_insensitive' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('TEST')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_every_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('hello you', 'hello me', 'hello there')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_single_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            $this->mockCommitMsgContext($this->buildMessage('you', 'hello me', 'there')),
            function () {
            },
        ];
        yield 'it_applies_matchers_without_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello((.|[\n])*)bye$/'],
                'multiline' => false,
            ],
            $this->mockCommitMsgContext($this->buildMessage('hello there', 'good bye')),
            function () {
            },
        ];
    }

    public function provideSkipsOnStuff(): iterable
    {
        return [];
    }

    private function mockCommitMsgContext(string $message): ContextInterface
    {
        /** @var GitCommitMsgContext|ObjectProphecy $context */
        $context = $this->prophesize(GitCommitMsgContext::class);
        $context->getFiles()->willReturn(new FilesCollection([]));
        $context->getCommitMessage()->willReturn($message);

        return $context->reveal();
    }

    private function buildMessage(string $subject, string ... $lines): string
    {
        return $this->buildMultiLineString(...array_merge([$subject, ''], $lines));
    }

    private function buildMultiLineString(string ... $lines): string
    {
        return implode(PHP_EOL, $lines);
    }

    private function wrapComments(string $message): string
    {
        return $this->buildMultiLineString(
            '# Something very long. Something very long. Something very long. Something very long. Something very long. Something very long.',
            $message,
            '# Something very long. Something very long. Something very long. Something very long. Something very long. Something very long.'
        );
    }

    private function addIgnoreBelowComment(string $message): string
    {
        return $this->buildMultiLineString(
            $message,
            '',
            '# Please enter the commit message for your changes. Lines starting',
            '# with \'#\' will be ignored, and an empty message aborts the commit.',
            '#',
            '# On branch fix-ignore-git-verbose',
            '# Changes to be committed:',
            '#	modified:   src/Task/Git/CommitMessage.php',
            '#',
            '# ------------------------ >8 ------------------------',
            '# Do not modify or remove the line above.',
            '# Everything below it will be ignored.',
            'diff --git a/src/Task/Git/CommitMessage.php b/src/Task/Git/CommitMessage.php',
            'Something very long. Something very long. Something very long. Something very long. Something very long. Something very long.'
        );
    }

    private function fixup(string ... $messages): string
    {
        $subject = array_shift($messages);

        return $this->buildMessage(
            'fixup! '.$subject,
            '# This was created by running git commit --fixup=...',
            ...$messages
        );
    }


    private function squash(string ... $messages): string
    {
        $subject = array_shift($messages);

        return $this->buildMessage(
            'squash! '.$subject,
            '# This was created by running git commit --squash=...',
            ...$messages
        );
    }
}
