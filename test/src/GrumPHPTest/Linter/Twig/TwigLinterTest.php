<?php

namespace GrumPHP\Linter\Twig;

use SplFileInfo;

/**
 * Class TwigLinterTest
 *
 * @package GrumPHP\Linter\Twig
 */
class TwigLinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwigLinter
     */
    protected $linter;

    protected function setUp()
    {
        $this->linter = new TwigLinter();
    }

    /**
     * @param string $fixture
     *
     * @return SplFileInfo
     */
    private function getFixture($fixture)
    {
        $file = new SplFileInfo(TEST_BASE_PATH . '/fixtures/linters/twig/' . $fixture);
        if (!$file->isReadable()) {
            throw new \RuntimeException(sprintf('The fixture %s could not be loaded!', $fixture));
        }

        return $file;
    }

    /**
     * @test
     * @dataProvider provideTwigValidation
     */
    function it_should_validate_twig_for_syntax_errors($fixture, $errors)
    {
        $result = $this->linter->lint($this->getFixture($fixture));
        $message = isset($result[0]) ? (string) $result[0] : null;
        $this->assertInstanceOf('GrumPHP\Collection\LintErrorsCollection', $result);
        $this->assertEquals(0, $result->count(), $message);
    }

    /**
     * @return array
     */
    function provideTwigValidation()
    {
        return array(
            array('fixture' => 'assetic-stylesheet-tag.twig', 'errors' => 0),
            array('fixture' => 'mixed.twig', 'errors' => 0),
            array('fixture' => 'multiple-trans-tags.twig', 'errors' => 0),
            array('fixture' => 'render-tag.twig', 'errors' => 0),
            array('fixture' => 'trans-tag.twig', 'errors' => 0),
            array('fixture' => 'undefined-functions.twig', 'errors' => 0),
            array('fixture' => 'undefined-test.twig', 'errors' => 0),
        );
    }
}
