<?php

namespace spec\GrumPHP\Locator;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Locator\ChangedFiles;
use GrumPHP\Locator\ListedFiles;
use PhpSpec\ObjectBehavior;
use GrumPHP\Locator\StdInFiles;

class StdInFilesSpec extends ObjectBehavior
{
    public function let(
        ChangedFiles $changedFilesLocator,
        ListedFiles $listedFiles
    ): void {
        $this->beConstructedWith($changedFilesLocator, $listedFiles);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(StdInFiles::class);
    }

    public function it_can_parse_git_diffs(ChangedFiles $changedFilesLocator): void
    {
        $diff = <<<EOD
diff --git a/src/test.php b/src/test.php
index 372bf10b74013301cfb4bf0e8007d208bb813363..d95f50da4a02d3d203bda1f3cb94e29d4f0ef481 100644
--- a/src/test.php
+++ b/src/test.php
@@ -2,3 +2,4 @@


 'something';
+'ok';

EOD;
        $changedFilesLocator->locateFromRawDiffInput($diff)->willReturn($expected = new FilesCollection());

        $this->locate($diff)->shouldBe($expected);
    }

    public function it_can_parse_file_lists(ListedFiles $listedFiles): void
    {
        $fileList = implode(PHP_EOL, ['file1.txt', 'file2.txt']);
        $listedFiles->locate($fileList)->willReturn($expected = new FilesCollection());

        $this->locate($fileList)->shouldBe($expected);
    }
}
