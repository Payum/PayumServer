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
        return new FilesystemStorage($options['storageDir'], $modelClass, $idProperty);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'filesystem';
    }
}
