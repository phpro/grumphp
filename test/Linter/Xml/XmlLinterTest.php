<?php

namespace GrumPHPTest\Linter\Xml;

use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\Xml\XmlLinter;
use GrumPHP\Linter\Xml\XmlLintError;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SplFileInfo;

class XmlLinterTest extends TestCase
{
    /**
     * @var XmlLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new XmlLinter();
    }

    /**
     * @param string $fixture
     *
     * @return SplFileInfo
     */
    private function getFixture($fixture)
    {
        $file = new SplFileInfo(TEST_BASE_PATH . '/fixtures/linters/xml/' . $fixture);
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
            $this->assertInstanceOf(XmlLintError::class, $result[0]);
        }
    }

    /**
     * @test
     * @dataProvider provideXmlValidation
     */
    function it_should_validate_xml_for_syntax_errors($fixture, $errors)
    {
        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     * @dataProvider provideDtdValidation
     */
    function it_should_validate_xml_with_dtd($fixture, $errors, $loadFromNet)
    {
        $this->linter->setDtdValidation(true);
        $this->linter->setLoadFromNet($loadFromNet);

        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     * @dataProvider provideSchemeValidation
     */
    function it_should_validate_xml_with_xsd($fixture, $errors, $loadFromNet)
    {
        $this->linter->setSchemeValidation(true);
        $this->linter->setLoadFromNet($loadFromNet);

        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     * @dataProvider provideDtdAndSchemeValidation
     */
    function it_should_validate_both_xsd_and_dtd($fixture, $errors)
    {
        $this->linter->setDtdValidation(true);
        $this->linter->setSchemeValidation(true);

        $this->validateFixture($fixture, $errors);
    }

    /**
     * @test
     * @dataProvider provideXincludeValidation
     */
    function it_can_handle_xincludes($fixture, $errors)
    {
        $this->linter->setXInclude(true);

        $this->validateFixture($fixture, $errors);
    }

    /**
     * @return array
     */
    function provideXmlValidation()
    {
        return [
            ['fixture' => 'xml-valid.xml', 'errors' => 0],
            ['fixture' => 'xml-invalid.xml', 'errors' => 1],
        ];
    }

    /**
     * @return array
     */
    function provideDtdValidation()
    {
        return [
            ['fixture' => 'xml-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'dtd-internal-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'dtd-internal-invalid.xml', 'errors' => 1, 'loadFromNet' => false],
            ['fixture' => 'dtd-external-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'dtd-external-invalid.xml', 'errors' => 1, 'loadFromNet' => false],
            ['fixture' => 'dtd-url-valid.xml', 'errors' => 0, 'loadFromNet' => true],
            ['fixture' => 'dtd-url-invalid.xml', 'errors' => 1, 'loadFromNet' => true],
            ['fixture' => 'dtd-url-invalid.xml', 'errors' => 0, 'loadFromNet' => false],
        ];
    }

    /**
     * @return array
     */
    function provideSchemeValidation()
    {
        return [
            ['fixture' => 'xml-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'xsd-namespace-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'xsd-namespace-invalid.xml', 'errors' => 1, 'loadFromNet' => false],
            ['fixture' => 'xsd-nonamespace-valid.xml', 'errors' => 0, 'loadFromNet' => false],
            ['fixture' => 'xsd-nonamespace-invalid.xml', 'errors' => 1, 'loadFromNet' => false],
            ['fixture' => 'xsd-url-valid.xml', 'errors' => 0, 'loadFromNet' => true],
            ['fixture' => 'xsd-url-invalid.xml', 'errors' => 1, 'loadFromNet' => true],
            ['fixture' => 'xsd-url-invalid.xml', 'errors' => 0, 'loadFromNet' => false],
        ];
    }

    /**
     * @return array
     */
    function provideDtdAndSchemeValidation()
    {
        return [
            ['fixture' => 'dtd-xsd-valid.xml', 'errors' => 0],
            ['fixture' => 'dtd-xsd-invalid.xml', 'errors' => 2],
        ];
    }

    /**
     * @return array
     */
    function provideXincludeValidation()
    {
        return [
            ['fixture' => 'xml-valid.xml', 'errors' => 0],
            ['fixture' => 'xinclude-valid.xml', 'errors' => 0],
            ['fixture' => 'xinclude-invalid.xml', 'errors' => 2],
        ];
    }
}
