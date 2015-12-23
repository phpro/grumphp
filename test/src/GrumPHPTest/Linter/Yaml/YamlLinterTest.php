<?php

namespace GrumPHP\Linter\Yaml;

use SplFileInfo;

/**
 * Class YamlLinterTest
 *
 * @package GrumPHP\Linter\Yaml
 */
class YamlLinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var YamlLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new YamlLinter();
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
            throw new \RuntimeException(sprintf('The fixture %s could not be loaded!', $fixture));
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
        $this->assertInstanceOf('GrumPHP\Collection\LintErrorsCollection', $result);
        $this->assertEquals($result->count(), $errors, 'Invalid error-count expected.');
        if ($result->count()) {
            $this->assertInstanceOf('GrumPHP\Linter\Yaml\YamlLintError', $result[0]);
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
        $this->validateFixture('object-support.yml', 0);
    }

    /**
     * @test
     */
    function it_should_handle_exceptions_on_invalid_type()
    {
        $this->linter->setObjectSupport(false);
        $this->linter->setExceptionOnInvalidType(true);
        $this->validateFixture('object-support.yml', 1);

    }

    /**
     * @return array
     */
    function provideYamlValidation()
    {
        return array(
            array('fixture' => 'valid.yml', 'errors' => 0),
            array('fixture' => 'invalid.yml', 'errors' => 1),
        );
    }
}
