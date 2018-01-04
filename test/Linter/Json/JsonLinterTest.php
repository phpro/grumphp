<?php declare(strict_types=1);

namespace GrumPHPTest\Linter\Json;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\Json\JsonLinter;
use GrumPHP\Linter\Json\JsonLintError;
use GrumPHP\Util\Filesystem;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Seld\JsonLint\JsonParser;
use SplFileInfo;

class JsonLinterTest extends TestCase
{
    /**
     * @var JsonLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new JsonLinter(
            new Filesystem(),
            new JsonParser()
        );
    }

    private function getFixture(string $fixture): SplFileInfo
    {
        $file = new SplFileInfo(TEST_BASE_PATH . '/fixtures/linters/json/' . $fixture);
        if (!$file->isReadable()) {
            throw new RuntimeException(sprintf('The fixture %s could not be loaded!', $fixture));
        }

        return $file;
    }
    private function validateFixture(string $fixture, int $errors)
    {
        $result = $this->linter->lint($this->getFixture($fixture));
        $this->assertInstanceOf(LintErrorsCollection::class, $result);
        $this->assertEquals($result->count(), $errors, 'Invalid error-count expected.');
        if ($result->count()) {
            $this->assertInstanceOf(JsonLintError::class, $result[0]);
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

    function provideJsonValidation(): array
    {
        return [
            ['fixture' => 'valid.json', 'errors' => 0],
            ['fixture' => 'duplicate-keys.json', 'errors' => 0],
            ['fixture' => 'invalid.json', 'errors' => 1],
        ];
    }
}
