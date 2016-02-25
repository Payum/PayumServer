<?php
namespace Payum\Server\Api\View;

use Symfony\Component\Form\FormInterface;

class FormToJsonConverter
{
    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function convertMeta(FormInterface $form)
    {
        $formView = $form->createView();

        $fields = array();
        foreach ($formView->children as $name => $child) {
            $fields[$name] = array(
                'default' => $child->vars['data'],
                'label' => $child->vars['label'],
                'required' => $child->vars['required'],
            );

            if (in_array('checkbox', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'checkbox';
            } else if (in_array('password', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'password';
            } elseif (in_array('choice', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'choice';
                $fields[$name]['choices'] = array_values($child->vars['choices']);
            } elseif (in_array('text', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'text';
            } elseif (in_array('number', $child->vars['block_prefixes'])) {
                $fields[$name]['type'] = 'text';
            } else {
                $fields[$name]['type'] = 'form';
                $fields[$name]['children'] = $this->convertMeta($form->get($name));
            }
        }

        return $fields;
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function convertInvalid(FormInterface $form)
    {
        return array(
            'errors' => (string) $form->getErrors(true, false),
        );
    }
}