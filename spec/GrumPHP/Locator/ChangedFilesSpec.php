use Gitonomy\Git\Diff\File;
use GrumPHP\Collection\FilesCollection;
        $this->shouldHaveType(ChangedFiles::class);
        $file = $prophet->prophesize(File::class);
        $diff->getFiles()->willReturn([$changedFile, $movedFile, $deletedFile]);
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->shouldBeAnInstanceOf(FilesCollection::class);