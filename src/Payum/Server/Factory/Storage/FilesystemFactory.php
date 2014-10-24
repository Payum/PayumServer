<?php
namespace Payum\Server\Factory\Storage;

use Payum\Core\PaymentInterface;
use Payum\Core\Storage\FilesystemStorage;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

class FilesystemFactory implements FactoryInterface
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
            ->add('storageDir', 'text', array(
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
        return new FilesystemStorage(
            str_replace('%app.root_dir%', $this->rootDir, $options['storageDir']),
            $modelClass,
            $idProperty
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'filesystem';
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {
        return 'Filesystem (For devs only)';
    }
}
