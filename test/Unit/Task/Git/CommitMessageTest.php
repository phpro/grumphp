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
use Prophecy\Prophet;

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

    private static function buildFailureMessage(string $lines, GitCommitMsgContext $commitMsg)
    {
        return $lines . PHP_EOL . 'Original commit message:' . PHP_EOL . $commitMsg->getCommitMessage();
    }

    public static function provideConfigurableOptions(): iterable
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
                'skip_on_merge_commit' => true,
                'matchers' => [],
                'additional_modifiers' => '',
            ]
        ];
    }

    public static function provideRunContexts(): iterable
    {
        yield 'run-context' => [
            false,
            self::mockContext(RunContext::class)
        ];

        yield 'pre-commit-context' => [
            false,
            self::mockContext(GitPreCommitContext::class)
        ];

        yield 'commit-msg-context' => [
            true,
            self::mockContext(GitCommitMsgContext::class)
        ];

        yield 'other' => [
            false,
            self::mockContext()
        ];
    }

    public static function provideFailsOnStuff(): iterable
    {
        yield 'dont-allow_empty_message' => [
            [
                'allow_empty_message' => false,
            ],
            self::mockCommitMsgContext(''),
            function () {
            },
            'Commit message should not be empty.'
        ];
        yield 'dont-allow_trimmed_empty_message' => [
            [
                'allow_empty_message' => false,
            ],
            self::mockCommitMsgContext('     '),
            function () {
            },
            'Commit message should not be empty.'
        ];
        yield 'enforce_capitalized_subject' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('no capital subject')),
            function () {
            },
            self::buildFailureMessage('Subject should start with a capital letter.', $commitMsg)
        ];
        yield 'enforce_capitalized_subject_punctuation' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('"no" capital subject')),
            function () {
            },
            self::buildFailureMessage('Subject should start with a capital letter.', $commitMsg)
        ];
        yield 'enforce_capitalized_subject_utf8' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('"ärsgäng" capital subject')),
            function () {
            },
            self::buildFailureMessage('Subject should start with a capital letter.', $commitMsg)
        ];
        yield 'enforce_no_subject_punctuations' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('Some . punctiation')),
            function () {
            },
            self::buildFailureMessage('Please omit all punctuations from commit message subject.', $commitMsg)
        ];
        yield 'enforce_no_subject_punctuations_comma' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('Some , punctiation')),
            function () {
            },
            self::buildFailureMessage('Please omit all punctuations from commit message subject.', $commitMsg)
        ];
        yield 'enforce_no_subject_punctuations_exclamation' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('Some ! punctiation')),
            function () {
            },
            self::buildFailureMessage('Please omit all punctuations from commit message subject.', $commitMsg)
        ];
        yield 'enforce_no_subject_punctuations_question' => [
            [
                'enforce_no_subject_punctuations' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('Some ? punctiation')),
            function () {
            },
            self::buildFailureMessage('Please omit all punctuations from commit message subject.', $commitMsg)
        ];
        yield 'enforce_no_subject_trailing_period' => [
            [
                'enforce_no_subject_trailing_period' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('Subject ending with.')),
            function () {
            },
            self::buildFailureMessage('Please omit trailing period from commit message subject.', $commitMsg)
        ];
        yield 'enforce_single_lined_subject-with_body' => [
            [
                'enforce_single_lined_subject' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage(
                self::buildMultiLineString('Subject line', 'subject line 2'),
                'comment line1',
                'comment line2'
            )),
            function () {
            },
            self::buildFailureMessage('Subject should be one line and followed by a blank line.', $commitMsg)
        ];
        yield 'enforce_text_with_regular' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage(
                'Subject 1234567891011',
                'Body 1234567891011',
                'Body ok',
                'Body 1110987654321'
            )),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Please keep the subject <= 10 characters.',
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            ), $commitMsg)
        ];
        yield 'enforce_text_with_regular_gives_back_message' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage(
                'Subject 1234567891011',
                'Body 1234567891011',
                'Body ok',
                'Body 1110987654321'
            )),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Please keep the subject <= 10 characters.',
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.',
            ), $commitMsg)
        ];
        yield 'enforce_text_with_long_comments' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $commitMsg = self::mockCommitMsgContext(self::wrapComments(
                self::buildMessage(
                    'Subject',
                    'Body 1234567891011',
                    'Body ok',
                    'Body 1110987654321'
                )
            )),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            ), $commitMsg)
        ];
        yield 'enforce_text_with_ignore_below_comment' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            $commitMsg = self::mockCommitMsgContext(self::addIgnoreBelowComment(
                self::buildMessage(
                    'Subject',
                    'Body 1234567891011',
                    'Body ok',
                    'Body 1110987654321'
                )
            )),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Line 3 of commit message has > 10 characters.',
                'Line 5 of commit message has > 10 characters.'
            ), $commitMsg)
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
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('doesnt match type scope convention')),
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
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('bug: doesnt match type scope convention')),
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
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('fix(api): doesnt match type scope convention')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
        yield 'it_fails_on_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST'],
            ],
            $commitMsg = self::mockCommitMsgContext('invalid'),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Rule not matched: "0" test',
                'Rule not matched: "1" *es*',
                'Rule not matched: "2" te[s][t]',
                'Rule not matched: "3" /^te(.*)/',
                'Rule not matched: "4" /(.*)st$/',
                'Rule not matched: "5" /t(e|a)st/',
                'Rule not matched: "6" TEST',
            ), $commitMsg)
        ];
        yield 'it_fails_on_names_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => [
                    'full' => 'test',
                    'partial' => '*es*'
                ],
            ],
            $commitMsg = self::mockCommitMsgContext('invalid'),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Rule not matched: "full" test',
                'Rule not matched: "partial" *es*',
            ), $commitMsg)
        ];
        yield 'it_applies_matchers_with_case_sensitive' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/TEST/'],
                'case_insensitive' => false,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('test')),
            function () {
            },
            self::buildFailureMessage(self::buildMultiLineString(
                'Rule not matched: "0" /TEST/',
            ), $commitMsg)
        ];
        yield 'it_applies_matchers_with_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('you', 'me', 'there')),
            function () {
            },
            self::buildFailureMessage('Rule not matched: "0" /^hello/', $commitMsg)
        ];
        yield 'it_applies_matchers_without_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello((.|[\n])*)bye$/'],
                'multiline' => false,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('hello there bye', 'bye good hello')),
            function () {
            },
            self::buildFailureMessage('Rule not matched: "0" /^hello((.|[\n])*)bye$/', $commitMsg)
        ];
        yield 'dont-enforce_capitalized_subject_fixup_without_subject' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('', 'only body')),
            function () {
            },
            self::buildFailureMessage('Subject should start with a capital letter.', $commitMsg)
        ];
        yield 'it_fails_on_subject_pattern_default' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'scopes' => null,
                ],
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('feat(feature): Это не сработает')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
        yield 'it_fails_on_subject_pattern' => [
            [
                'type_scope_conventions' => [
                    'scopes' => null,
                    'subject_pattern' => '([0-9]+)'
                ],
            ],
            $commitMsg = self::mockCommitMsgContext(self::buildMessage('It fails')),
            function () {
            },
            'Rule not matched: "Invalid Type/Scope Format"'
        ];
    }

    public static function providePassesOnStuff(): iterable
    {
        yield 'allow_empty_message' => [
            [
                'allow_empty_message' => true,
            ],
            self::mockCommitMsgContext(''),
            function () {
            }
        ];
        yield 'allow_trimmed_empty_message' => [
            [
                'allow_empty_message' => true,
            ],
            self::mockCommitMsgContext('     '),
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
            self::mockCommitMsgContext(self::buildMessage('# Some content', 'The body!')),
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
            self::mockCommitMsgContext(self::buildMessage('; some content', 'The body!')),
            function () {
                $this->repository->run('config', ['--get', 'core.commentChar'])->willReturn('; ');
            }
        ];
        yield 'dont-enforce_capitalized_subject' => [
            [
                'enforce_capitalized_subject' => false,
            ],
            self::mockCommitMsgContext(self::buildMessage('no capital subject')),
            function () {
            }
        ];
        yield 'dont-enforce_capitalized_subject_fixup' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            self::mockCommitMsgContext(self::fixup('no capital subject')),
            function () {
            }
        ];
        yield 'dont-enforce_capitalized_subject_squash' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            self::mockCommitMsgContext(self::squash('no capital subject')),
            function () {
            }
        ];
        yield 'dont-enforce_capitalized_subject_amend' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            self::mockCommitMsgContext(self::amend('no capital subject')),
            function () {
            }
        ];
        yield 'enforce_capitalized_subject_special_utf8_char' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            self::mockCommitMsgContext(self::buildMessage('Årsgång')),
            function () {
            }
        ];
        yield 'enforce_capitalized_subject_punctuation' => [
            [
                'enforce_capitalized_subject' => true,
            ],
            self::mockCommitMsgContext(self::buildMessage('"Initial" commit')),
            function () {
            }
        ];
        yield 'dont-enforce_no_subject_punctuations' => [
            [
                'enforce_no_subject_punctuations' => false,
            ],
            self::mockCommitMsgContext(self::buildMessage('Some . punctiation')),
            function () {
            }
        ];
        yield 'dont-enforce_no_subject_trailing_period' => [
            [
                'enforce_no_subject_trailing_period' => false,
            ],
            self::mockCommitMsgContext(self::buildMessage('Subject ending with.')),
            function () {
            }
        ];
        yield 'enforce_single_lined_subject-with_body' => [
            [
                'enforce_single_lined_subject' => true,
            ],
            self::mockCommitMsgContext(self::buildMessage(
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
            self::mockCommitMsgContext(self::buildMessage(
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
            self::mockCommitMsgContext(self::fixup('123456789')),
            function () {
            }
        ];
        yield 'enforce_text_with_special_prefix_squash' => [
            [
                'max_subject_width' => 10,
            ],
            self::mockCommitMsgContext(self::squash('123456789')),
            function () {
            }
        ];
        yield 'enforce_text_with_special_prefix_amend' => [
            [
                'max_subject_width' => 10,
            ],
            self::mockCommitMsgContext(self::amend('123456789')),
            function () {
            }
        ];
        yield 'enforce_text_with_ignore_below_comment' => [
            [
                'enforce_single_lined_subject' => false,
                'max_subject_width' => 10,
                'max_body_width' => 10,
            ],
            self::mockCommitMsgContext(self::addIgnoreBelowComment(self::buildMessage('Subject'))),
            function () {
            },
        ];
        yield 'empty-type_scope_conventions' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [],
            ],
            self::mockCommitMsgContext(self::buildMessage('doesnt match type scope convention')),
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
            self::mockCommitMsgContext(self::buildMessage('fix: match type scope convention')),
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
            self::mockCommitMsgContext(self::buildMessage('fix: match type scope convention')),
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
            self::mockCommitMsgContext(self::buildMessage('fix(app): match type scope convention')),
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
            self::mockCommitMsgContext(self::fixup('fix: match type scope convention')),
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
            self::mockCommitMsgContext(self::fixup('fix(app): match type scope convention')),
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
            self::mockCommitMsgContext(self::squash('fix: match type scope convention')),
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
            self::mockCommitMsgContext(self::squash('fix(app): match type scope convention')),
            function () {
            },
        ];
        yield 'amend_type_scope_conventions_match_type_without_scope' => [
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
            self::mockCommitMsgContext(self::amend('fix: match type scope convention')),
            function () {
            },
        ];
        yield 'amend_type_scope_conventions_match_type_with_scope' => [
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
            self::mockCommitMsgContext(self::amend('fix(app): match type scope convention')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage('Merge branch \'x\' into')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_branch_gitflow' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage("Merge branch 'release/x.y.z'")),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_tag_gitflow' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage("Merge tag 'x.y.z' into")),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_remote' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage('Merge remote-tracking branch \'x\' into')),
            function () {
            },
        ];
        yield 'skip_type_scope_conventions_on_merge_PR' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage('Merge pull request #123 into')),
            function () {
            },
        ];
        yield 'fixup_skip_type_scope_conventions_on_merge' => [
            [
                'enforce_capitalized_subject' => false,
                'skip_on_merge_commit' => false,
                'type_scope_conventions' => [
                    'types' => [
                        'fix'
                    ],
                ],
            ],
            self::mockCommitMsgContext(self::fixup('Merge branch \'x\' into')),
            function () {
            },
        ];
        yield 'it_applies_matchers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['test', '*es*', 'te[s][t]', '/^te(.*)/', '/(.*)st$/', '/t(e|a)st/', 'TEST'],
            ],
            self::mockCommitMsgContext('test'),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_additional_modifiers' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/.*ümlaut/'],
                'additional_modifiers' => 'u',
            ],
            self::mockCommitMsgContext(self::buildMessage('message containing ümlaut')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_case_sensitive' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/TEST/'],
                'case_insensitive' => false,
            ],
            self::mockCommitMsgContext(self::buildMessage('TEST')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_every_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            self::mockCommitMsgContext(self::buildMessage('hello you', 'hello me', 'hello there')),
            function () {
            },
        ];
        yield 'it_applies_matchers_with_single_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello/'],
                'multiline' => true,
            ],
            self::mockCommitMsgContext(self::buildMessage('you', 'hello me', 'there')),
            function () {
            },
        ];
        yield 'it_applies_matchers_without_multiline' => [
            [
                'enforce_capitalized_subject' => false,
                'matchers' => ['/^hello((.|[\n])*)bye$/'],
                'multiline' => false,
            ],
            self::mockCommitMsgContext(self::buildMessage('hello there', 'good bye')),
            function () {
            },
        ];
        yield 'it_applies_on_subject_pattern_default' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'scopes' => null,
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage('feat(feature): It works')),
            function () {
            },
        ];
        yield 'it_applies_on_subject_pattern' => [
            [
                'enforce_capitalized_subject' => false,
                'type_scope_conventions' => [
                    'scopes' => null,
                    'subject_pattern' => '([a-zA-Zа-яА-Я0-9-_ #@\'\/\\"]+)'
                ],
            ],
            self::mockCommitMsgContext(self::buildMessage('feat(feature): Это сработает')),
            function () {
            },
        ];
        yield 'dont_skip_on_merge_commit' => [
            [
                'skip_on_merge_commit' => false,
            ],
            self::mockCommitMsgContext(self::fixup('Merge branch \'x\' into')),
            function () {
            },
        ];
    }

    public static function provideSkipsOnStuff(): iterable
    {
        yield 'skip_on_merge_commit' => [
            [
                'skip_on_merge_commit' => true,
            ],
            self::mockCommitMsgContext(self::fixup('Merge branch \'x\' into')),
            function () {
            },
        ];
    }

    private static function mockCommitMsgContext(string $message): ContextInterface
    {
        /** @var GitCommitMsgContext|ObjectProphecy $context */
        $context = (new Prophet())->prophesize(GitCommitMsgContext::class);
        $context->getFiles()->willReturn(new FilesCollection([]));
        $context->getCommitMessage()->willReturn($message);

        return $context->reveal();
    }

    private static function buildMessage(string $subject, string ... $lines): string
    {
        return self::buildMultiLineString(...array_merge([$subject, ''], $lines));
    }

    private static function buildMultiLineString(string ... $lines): string
    {
        return implode(PHP_EOL, $lines);
    }

    private static function wrapComments(string $message): string
    {
        return self::buildMultiLineString(
            '# Something very long. Something very long. Something very long. Something very long. Something very long. Something very long.',
            $message,
            '# Something very long. Something very long. Something very long. Something very long. Something very long. Something very long.'
        );
    }

    private static function addIgnoreBelowComment(string $message): string
    {
        return self::buildMultiLineString(
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

    private static function fixup(string ... $messages): string
    {
        $subject = array_shift($messages);

        return self::buildMessage(
            'fixup! '.$subject,
            '# This was created by running git commit --fixup=...',
            ...$messages
        );
    }


    private static function squash(string ... $messages): string
    {
        $subject = array_shift($messages);

        return self::buildMessage(
            'squash! '.$subject,
            '# This was created by running git commit --squash=...',
            ...$messages
        );
    }

    private static function amend(string ... $messages): string
    {
        $subject = array_shift($messages);

        return self::buildMessage(
            'amend! '.$subject,
            '# This was created by running git commit --fixup=amend:...',
            ...$messages
        );
    }
}
