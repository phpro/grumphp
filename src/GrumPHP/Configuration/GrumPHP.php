<?php

namespace GrumPHP\Configuration;

use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Zend\Stdlib\AbstractOptions;

/**
 * Class GrumPHP
 *
 * @package GrumPHP\Configuration
 */
class GrumPHP extends AbstractOptions
{

    const CONFIG_NAMESPACE = 'grumphp';

    /**
     * @var string
     */
    protected $baseDir = '/.';

    /**
     * @var string
     */
    protected $gitDir = '/.';

    /**
     * @var Phpcs
     */
    protected $phpcs;

    /**
     * @return string
     */
    public function getBaseDir()
    {
        return $this->baseDir;
    }

    /**
     * @param string $baseDir
     */
    public function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
    }

    /**
     * @return string
     */
    public function getGitDir()
    {
        return $this->gitDir;
    }

    /**
     * @param string $gitDir
     */
    public function setGitDir($gitDir)
    {
        $this->gitDir = $gitDir;
    }

    /**
     * @return Phpcs
     */
    public function getPhpcs()
    {
        return $this->phpcs;
    }

    /**
     * @param array $phpcs
     */
    public function setPhpcs($phpcs)
    {
        $phpcs = ($phpcs instanceof Phpcs) ?: new Phpcs($phpcs);
        $this->phpcs = $phpcs;
    }

    /**
     * @return bool
     */
    public function hasPhpcs()
    {
        return ($this->phpcs instanceof Phpcs);
    }

    /**
     * @param $baseDir
     *
     * @return GrumPHP
     */
    public static function loadFromComposerFile($baseDir)
    {
        $filesystem = new Filesystem();
        $composerFile = $baseDir . '/composer.json';
        if (!$filesystem->exists($composerFile)) {
            throw new RuntimeException(sprintf('The composer.json file could not be found at %s.', $baseDir));
        }

        $composerData = json_decode(file_get_contents($composerFile), true);
        if (!isset($composerData['extra'][self::CONFIG_NAMESPACE])) {
            throw new RuntimeException(sprintf('No configuration could be found. There is no %s key in the composer.json file', self::CONFIG_NAMESPACE));
        }

        return new self($composerData['extra'][self::CONFIG_NAMESPACE]);
    }

}
