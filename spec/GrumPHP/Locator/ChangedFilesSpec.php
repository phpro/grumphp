use GrumPHP\Util\Filesystem;
 * Class ChangedFilesSpec
    function let(Repository $repository, Filesystem $filesystem)
        $this->beConstructedWith($repository, $filesystem);
    function it_will_list_all_diffed_files(Repository $repository, Filesystem $filesystem, Diff $diff, WorkingCopy $workingCopy)
        $filesystem->exists('file1.txt')->willReturn(true);
        $filesystem->exists('file2.txt')->willReturn(true);
        $filesystem->exists('file3.txt')->willReturn(false);

    function it_will_not_list_non_existing_files(Repository $repository, Filesystem $filesystem, Diff $diff, WorkingCopy $workingCopy)
    {
        $changedFile = $this->mockFile('file1.txt');
        $filesystem->exists('file1.txt')->willReturn(false);

        $repository->getWorkingCopy()->willReturn($workingCopy);
        $workingCopy->getDiffStaged()->willReturn($diff);
        $diff->getFiles()->willReturn([$changedFile]);

        $result = $this->locateFromGitRepository();
        $result->shouldBeAnInstanceOf(FilesCollection::class);
        $result->getIterator()->count()->shouldBe(0);
    }

    function it_will_list_all_diffed_files_from_raw_diff_input(Filesystem $filesystem)
        $filesystem->exists('file.txt')->willReturn(true);
