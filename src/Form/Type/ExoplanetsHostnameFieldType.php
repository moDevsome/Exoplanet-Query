<?php
// src/Form/Type/ExoplanetsFilterType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExoplanetsHostnameFieldType extends AbstractType
{
    public function configureOptions($resolver): void {
        // this defines the available options and their default values when
        // they are not configured explicitly when using the form type
        $resolver->setDefaults([
            'available_hostname' => [],
            'hostname_reset_redirect' => ''
        ]);

    }

    public function buildView($view, $form, $options): void {

        // pass the form type option directly to the template
        $view->vars['available_hostname'] = $options['available_hostname'];
        $view->vars['value'] = $form->getData() ?? '';
        $view->vars['hostname_reset_redirect'] = $options['hostname_reset_redirect'];

    }
}