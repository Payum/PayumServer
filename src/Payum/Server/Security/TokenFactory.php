<?php
namespace Payum\Server\Security;

use Payum\Core\Registry\StorageRegistryInterface;
use Payum\Core\Security\AbstractGenericTokenFactory;
use Payum\Core\Storage\StorageInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class TokenFactory extends AbstractGenericTokenFactory
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    /**
     * @param RouterInterface $urlGenerator
     * @param StorageInterface $tokenStorage
     * @param StorageRegistryInterface $storageRegistry
     * @param string $capturePath
     * @param string $notifyPath
     */
    public function __construct(UrlGeneratorInterface $urlGenerator, StorageInterface $tokenStorage, StorageRegistryInterface $storageRegistry, $capturePath, $notifyPath)
    {
        $this->urlGenerator = $urlGenerator;

        parent::__construct($tokenStorage, $storageRegistry, $capturePath, $notifyPath);
    }

    /**
     * @param string $path
     * @param array $parameters
     *
     * @return string
     */
    protected function generateUrl($path, array $parameters = array())
    {
        if (0 === strpos($path, 'http')) {
            return $path;
        }

        return $this->urlGenerator->generate($path, $parameters, $absolute = true);
    }
}