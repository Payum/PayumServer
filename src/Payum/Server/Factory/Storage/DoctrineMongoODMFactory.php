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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class DoctrineMongoODMFactory implements FactoryInterface
{
    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

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
            ->add('collection', 'text', array(
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
        $driver->addDriver(
            new XmlDriver(
                new SymfonyFileLocator(array(
                    $this->rootDir.'/vendor/payum/payum/src/Payum/Core/Bridge/Doctrine/Resources/mapping' => 'Payum\Core\Model'
                ), '.mongodb.xml'),
                '.mongodb.xml'
            ),
            'Payum\Core\Model'
        );

// your models
        AnnotationDriver::registerAnnotationClasses();
        $driver->addDriver(
            new AnnotationDriver(new AnnotationReader(), array(
                $this->rootDir.'/src/Payum/Server/Model',
            )),
            'Payum\Server\Model'
        );

        $config = new Configuration();
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir(\sys_get_temp_dir());
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl($driver);
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setDefaultDB($options['collection']);

        $connection = new Connection($options['port'], array(), $config);

        return new DoctrineStorage(DocumentManager::create($connection, $config), $modelClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'doctrine_mongo_odm';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Doctrine MongoODM';
    }
}
