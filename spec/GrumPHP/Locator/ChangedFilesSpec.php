        $result = $this->locateFromGitRepository();

    function it_will_list_all_diffed_files_from_raw_diff_input()
    {
        $rawDiff = 'diff --git a/file.txt b/file.txt
new file mode 100644
index 0000000000000000000000000000000000000000..9766475a4185a151dc9d56d614ffb9aaea3bfd42
--- /dev/null
+++ b/file.txt
@@ -0,0 +1 @@
+content
';

        $result = $this->locateFromRawDiffInput($rawDiff);
        $result->shouldBeAnInstanceOf('GrumPHP\Collection\FilesCollection');
        $result[0]->getPathname()->shouldBe('file.txt');
        $result->getIterator()->count()->shouldBe(1);
    }