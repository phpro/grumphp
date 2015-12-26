<?php

namespace GrumPHP\Linter\Json;

use SplFileInfo;

/**
 * Class JsonLinterTest
 *
 * @package GrumPHP\Linter\Json
 */
class JsonLinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new JsonLinter();
    }

    /**
     * @param string $fixture
     *
     * @return SplFileInfo
     */
    private function getFixture($fixture)
    {
        $file = new SplFileInfo(TEST_BASE_PATH . '/fixtures/linters/json/' . $fixture);
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
            $this->assertInstanceOf('GrumPHP\Linter\Json\JsonLintError', $result[0]);
        }
    }

    /**
     * @test
     * @dataProvider provideJsonValidation
     */
    function it_should_validate_json_for_syntax_errors($fixture, $errors)
    {
        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     */
    function it_should_be_able_to_detect_duplicate_keys()
    {
        $this->linter->setDetectKeyConflicts(true);
        $this->validateFixture('duplicate-keys.json', 1);
    }

    /**
     * @return array
     */
    function provideJsonValidation()
    {
        return array(
            array('fixture' => 'valid.json', 'errors' => 0),
            array('fixture' => 'duplicate-keys.json', 'errors' => 0),
            array('fixture' => 'invalid.json', 'errors' => 1),
        );
    }
}
