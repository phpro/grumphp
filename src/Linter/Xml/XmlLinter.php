<?php

declare(strict_types=1);

namespace GrumPHP\Linter\Xml;

use DOMDocument;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use SplFileInfo;

class XmlLinter implements LinterInterface
{
    const XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';

    /**
     * @var bool
     */
    private $loadFromNet = false;

    /**
     * @var bool
     */
    private $xInclude = false;

    /**
     * @var bool
     */
    private $dtdValidation = false;

    /**
     * @var bool
     */
    private $schemeValidation = false;

    public function lint(SplFileInfo $file): LintErrorsCollection
    {
        $errors = new LintErrorsCollection();
        $useInternalErrors = $this->useInternalXmlLoggin(true);
        $this->flushXmlErrors();

        $document = $this->loadDocument($file);
        if (!$document) {
            $this->collectXmlErrors($errors);
            $this->useInternalXmlLoggin($useInternalErrors);

            return $errors;
        }

        if ($this->xInclude && $document->xinclude() === -1) {
            $this->collectXmlErrors($errors);
        }

        if ($this->dtdValidation && !$this->validateDTD($document)) {
            $this->collectXmlErrors($errors);
        }

        if ($this->schemeValidation && !$this->validateInternalSchemes($file, $document)) {
            $this->collectXmlErrors($errors);
        }

        $this->useInternalXmlLoggin($useInternalErrors);

        return $errors;
    }

    public function isInstalled(): bool
    {
        $extensions = get_loaded_extensions();

        return \in_array('libxml', $extensions, true) && \in_array('dom', $extensions, true);
    }

    public function setLoadFromNet(bool $loadFromNet): void
    {
        $this->loadFromNet = $loadFromNet;
    }

    public function setXInclude(bool $xInclude): void
    {
        $this->xInclude = $xInclude;
    }

    public function setDtdValidation(bool $dtdValidation): void
    {
        $this->dtdValidation = $dtdValidation;
    }

    public function setSchemeValidation(bool $schemeValidation): void
    {
        $this->schemeValidation = $schemeValidation;
    }

    private function useInternalXmlLoggin(bool $useInternalErrors = false): bool
    {
        return libxml_use_internal_errors($useInternalErrors);
    }

    private function loadDocument(SplFileInfo $file): ?DOMDocument
    {
        $this->registerXmlStreamContext();

        $document = new DOMDocument();
        $document->resolveExternals = $this->loadFromNet;
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $loaded = $document->load($file->getPathname());

        return $loaded ? $document : null;
    }

    /**
     * This is added to fix a bug with remote DTDs that are blocking automated php request on some domains:.
     *
     * @see http://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
     * @see https://bugs.php.net/bug.php?id=48080
     */
    private function registerXmlStreamContext(): void
    {
        libxml_set_streams_context(stream_context_create([
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:43.0) Gecko/20100101 Firefox/43.0',
            ],
        ]));
    }

    private function collectXmlErrors(LintErrorsCollection $errors): void
    {
        foreach (libxml_get_errors() as $error) {
            $errors->add(XmlLintError::fromLibXmlError($error));
        }
        $this->flushXmlErrors();
    }

    /**
     * Make sure the libxml errors are flushed and won't be occurring again.
     */
    private function flushXmlErrors(): void
    {
        libxml_clear_errors();
    }

    private function validateDTD(DOMDocument $document): bool
    {
        /** @psalm-suppress TypeDoesNotContainNull */
        if (null === $document->doctype) {
            return true;
        }

        // Do not validate external DTDs if the loadFromNet option is disabled:
        $systemId = $document->doctype->systemId;
        if (filter_var($systemId, FILTER_VALIDATE_URL) && !$this->loadFromNet) {
            return true;
        }

        return $document->validate();
    }

    private function validateInternalSchemes(SplFileInfo $file, DOMDocument $document): bool
    {
        $schemas = [];
        $attributes = $document->documentElement->attributes;

        if ($schemaLocation = $attributes->getNamedItemNS(self::XSI_NAMESPACE, 'schemaLocation')) {
            $parts = preg_split('/\s{1,}/', trim($schemaLocation->textContent));
            foreach ($parts as $key => $value) {
                if ($key & 1) {
                    $schemas[] = $value;
                }
            }
        }

        if ($schemaLocNoNamespace = $attributes->getNamedItemNS(self::XSI_NAMESPACE, 'noNamespaceSchemaLocation')) {
            $schemas = array_merge($schemas, preg_split('/\s{1,}/', trim($schemaLocNoNamespace->textContent)));
        }

        $isValid = true;
        foreach ($schemas as $scheme) {
            if ($scheme = $this->locateScheme($file, $scheme)) {
                $isValid = $isValid && $document->schemaValidate($scheme);
            }
        }

        return $isValid;
    }

    /**
     * @return null|string
     */
    private function locateScheme(SplFileInfo $xmlFile, string $scheme)
    {
        if (filter_var($scheme, FILTER_VALIDATE_URL)) {
            return $this->loadFromNet ? $scheme : null;
        }

        $xmlFilePath = $xmlFile->getPath();
        $schemePath = empty($xmlFilePath) ? $scheme : rtrim($xmlFilePath, '/').DIRECTORY_SEPARATOR.$scheme;

        $schemeFile = new SplFileInfo($schemePath);

        return $schemeFile->isReadable() ? $schemeFile->getPathname() : null;
    }
}
