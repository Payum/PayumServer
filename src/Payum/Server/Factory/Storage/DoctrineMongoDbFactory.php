<?php
namespace Payum\Server\Factory\Storage;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ODM\MongoDB\Types\Type;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\ODM\MongoDB\Mapping\Driver\XmlDriver;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Payum\Core\Bridge\Doctrine\Storage\DoctrineStorage;
use Payum\Core\GatewayInterface;
use Payum\Server\Application;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DoctrineMongoDbFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function configureOptionsFormBuilder(FormBuilderInterface $builder)
    {
        $builder
            ->add('host', 'text', array(
                'data' => 'localhost:27017',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
            ->add('databaseName', 'text', array(
                'data' => 'payum_server',
                'required' => true,
                'constraints' => array(new NotBlank),
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function createStorage($modelClass, $idProperty, array $options)
    {
        if (false == Type::hasType('object')) {
            Type::addType('object', 'Payum\Core\Bridge\Doctrine\Types\ObjectType');
        }

        $driver = new MappingDriverChain;

        // payum's basic models
        $coreRootDir = dirname((new \ReflectionClass(GatewayInterface::class))->getFileName());

        $driver->addDriver(
            new XmlDriver(
                new SymfonyFileLocator(array(
                    $coreRootDir.'/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'
                ), '.mongodb.xml'),
                '.mongodb.xml'
            ),
            'Payum\Core\Model'
        );

        // your models
        $sererRootDir = dirname((new \ReflectionClass(Application::class))->getFileName());

        AnnotationDriver::registerAnnotationClasses();
        $driver->addDriver(
            new AnnotationDriver(new AnnotationReader(), array($sererRootDir.'/Model')),
            'Payum\Server\Model'
        );

        $config = new Configuration();
        $config->setProxyDir(\sys_get_temp_dir().'/PayumServer');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir(\sys_get_temp_dir().'/PayumServer');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl($driver);
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setDefaultDB($options['databaseName']);

        $connection = new Connection($options['host'], array(), $config);

        return new DoctrineStorage(DocumentManager::create($connection, $config), $modelClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine_mongodb';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Doctrine MongoDB';
    }
}
