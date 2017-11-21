<?php

namespace GrumPHPTest\Linter\Yaml;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Linter\Yaml\YamlLintError;
use GrumPHP\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;

class YamlLinterTest extends TestCase
{
    /**
     * @var YamlLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new YamlLinter(
            new Filesystem()
        );
    }

    /**
     * @param string $fixture
     *
     * @return SplFileInfo
     */
    private function getFixture($fixture)
    {
        $file = new SplFileInfo(TEST_BASE_PATH . '/fixtures/linters/yaml/' . $fixture);
        if (!$file->isReadable()) {
            throw new RuntimeException(sprintf('The fixture %s could not be loaded!', $fixture));
        }

        return $file;
    }
    /**
     * @param string $fixture
     * @param int $errors
     */
    private function validateFixture($fixture, $errors)
    {
        $result = $this->linter->lint($this->getFixture($fixture));
        $this->assertInstanceOf(LintErrorsCollection::class, $result);
        $this->assertEquals($result->count(), $errors, 'Invalid error-count expected.');
        if ($result->count()) {
            $this->assertInstanceOf(YamlLintError::class, $result[0]);
        }
    }

    /**
     * @test
     * @dataProvider provideYamlValidation
     */
    function it_should_validate_yaml_for_syntax_errors($fixture, $errors)
    {
        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     */
    function it_should_be_able_to_handle_object_support()
    {
        $this->linter->setObjectSupport(true);
        $fixture = YamlLinter::supportsFlags() ? 'object-support.yml' : 'object-support-old.yml';
        $this->validateFixture($fixture, 0);
    }

    /**
     * @test
     */
    function it_should_handle_exceptions_on_invalid_type()
    {
        $this->linter->setObjectSupport(false);
        $this->linter->setExceptionOnInvalidType(true);
        $fixture = YamlLinter::supportsFlags() ? 'object-support.yml' : 'object-support-old.yml';
        $this->validateFixture($fixture, 1);
    }

    /**
     * @test
     */
    function it_should_handle_exceptions_on_constants()
    {
        if (!YamlLinter::supportsFlags()) {
            $this->markTestSkipped('Parsing constants is not supported by the current version of symfony/yaml');
        }

        $this->linter->setExceptionOnInvalidType(true);
        $fixture = 'constant-support.yml';
        $this->validateFixture($fixture, 1);
    }

    /**
     * @test
     */
    function it_should_validate_constants()
    {
        if (!YamlLinter::supportsFlags()) {
            $this->markTestSkipped('Parsing constants is not supported by the current version of symfony/yaml');
        }

        $this->linter->setExceptionOnInvalidType(true);
        $this->linter->setParseConstants(true);
        $fixture = 'constant-support.yml';
        $this->validateFixture($fixture, 0);
    }

    /**
     * @test
     */
    function it_should_handle_exceptions_on_custom_tags()
    {
        if (!YamlLinter::supportsFlags()) {
            $this->markTestSkipped('Parsing custom tags is not supported by the current version of symfony/yaml');
        }

        $this->linter->setExceptionOnInvalidType(true);
        $fixture = 'tags-support.yml';
        $this->validateFixture($fixture, 1);
    }

    /**
     * @test
     */
    function it_should_validate_custom_tags()
    {
        if (!YamlLinter::supportsFlags()) {
            $this->markTestSkipped('Parsing custom tags is not supported by the current version of symfony/yaml');
        }

        $this->linter->setExceptionOnInvalidType(true);
        $this->linter->setParseCustomTags(true);
        $fixture = 'tags-support.yml';
        $this->validateFixture($fixture, 0);
    }

    /**
     * @return array
     */
    function provideYamlValidation()
    {
        return [
            ['fixture' => 'valid.yml', 'errors' => 0],
            ['fixture' => 'invalid.yml', 'errors' => 1],
        ];
    }
}
