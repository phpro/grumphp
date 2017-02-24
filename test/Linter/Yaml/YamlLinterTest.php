<?php

namespace GrumPHPTest\Linter\Yaml;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\Yaml\YamlLinter;
use GrumPHP\Linter\Yaml\YamlLintError;
use GrumPHP\Util\Filesystem;
use PHPUnit_Framework_TestCase;
use RuntimeException;
use SplFileInfo;

class YamlLinterTest extends PHPUnit_Framework_TestCase
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
    public function it_should_validate_yaml_for_syntax_errors($fixture, $errors)
    {
        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     */
    public function it_should_be_able_to_handle_object_support()
    {
        $this->linter->setObjectSupport(true);
        $fixture = YamlLinter::supportsFlags() ? 'object-support.yml' : 'object-support-old.yml';
        $this->validateFixture($fixture, 0);
    }

    /**
     * @test
     */
    public function it_should_handle_exceptions_on_invalid_type()
    {
        $this->linter->setObjectSupport(false);
        $this->linter->setExceptionOnInvalidType(true);
        $fixture = YamlLinter::supportsFlags() ? 'object-support.yml' : 'object-support-old.yml';
        $this->validateFixture($fixture, 1);
    }

    /**
     * @return array
     */
    public function provideYamlValidation()
    {
        return [
            ['fixture' => 'valid.yml', 'errors' => 0],
            ['fixture' => 'invalid.yml', 'errors' => 1],
        ];
    }
}
