<?php
namespace Payum\Server\Controller;

class IndexController
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    public function indexAction()
    {
        return MarkdownExtended(file_get_contents($this->rootDir.'/README.md'));
    }
} 