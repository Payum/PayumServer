<?php
declare(strict_types=1);

namespace App\Storage;

use Makasim\Yadm\Storage;
use Payum\Bundle\PayumBundle\DependencyInjection\Factory\Storage\AbstractStorageFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class YadmStorageFactory
 * @package App\Storage
 */
class YadmStorageFactory extends AbstractStorageFactory
{
    /**
     * {@inheritdoc}
     */
    public function getName() : string
    {
        return 'yadm';
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder) : void
    {
        parent::addConfiguration($builder);

        // @formatter:off
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('id_property')->defaultValue(YadmStorage::DEFAULT_ID_PROPERTY)->end()
            ->end();
        // @formatter:on
    }

    /**
     * @param ContainerBuilder $container
     * @param string $modelClass
     * @param array $config
     *
     * @return Definition
     */
    protected function createStorage(ContainerBuilder $container, $modelClass, array $config) : Definition
    {
        $yadmStorageId = sprintf('payum.storage._%s', strtolower(str_replace(['\\\\', '\\'], '_', $modelClass)));

        $container->register($yadmStorageId, Storage::class)
            ->setFactory([new Reference('yadm'), 'getStorage'])
            ->setPrivate(false)
            ->addArgument($modelClass);

        $definition = new Definition(YadmStorage::class);
        $definition->setPublic(true);
        $definition->addArgument(new Reference($yadmStorageId));
        $definition->addArgument($config['id_property']);
        $definition->addArgument($modelClass);

        return $definition;
    }
}
