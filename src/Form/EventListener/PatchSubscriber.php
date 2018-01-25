<?php
/**
 * Copyright (c) 2011 Arnaud Le Blanc, all rights reserved
 */

namespace App\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;

/**
 * Changes Form->bind() behavior so that it treats not set values as if they
 * were sent unchanged.
 *
 * Use when you don't want fields to be set to NULL when they are not displayed
 * on the page (or to implement PUT/PATCH requests).
 */
class PatchSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $clientData = $event->getData();
        $unbindClientData = $this->unbind($event->getForm());

        if (is_array($unbindClientData)) {
            $clientData = array_replace($unbindClientData, $clientData ?: array());
        }

        $event->setData($clientData);
    }

    /**
     * Returns the form's data like $form->bind() expects it
     *
     * @return mixed
     */
    protected function unbind(FormInterface $form)
    {
        if (count($form) > 0) {
            $clientData = array();
            foreach ($form as $name => $childForm) {
                $clientData[$name] = $this->unbind($childForm);
            }

            return $clientData;
        }

        return $form->getViewData();
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        );
    }
}