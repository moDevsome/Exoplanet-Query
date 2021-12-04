<?php
// src/Form/Type/ExoplanetsFilterType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ExoplanetsFilterType extends AbstractType
{

    /**
     * Adding CSRF Protection
     * @see https://symfony.com/doc/current/security/csrf.html
     */
    public function configureOptions($resolver): void
    {

        $resolver->setDefaults([
            // enable/disable CSRF protection for this form
            'csrf_protection' => TRUE,
            // the name of the hidden HTML field that stores the token
            'csrf_field_name' => '_filter_form_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'filter_form_token'
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $formData = $builder->getData();
        $builder->setData($formData['userStates']);

        $discoveryYearChoices = [-1 => 'Select a value...'];
        foreach($formData['choices']['discoveryYearChoices'] as $yearOffset => $year) {

            $discoveryYearChoices[$yearOffset] = (string) $year;

        }

        $builder->add('pl_name', TextType::class, ['attr' => ['class' => 'form-control'],
                                                'row_attr' => ['class' => 'form-group col'],
                                                'label' => 'Planet Name',
                                                'required' => FALSE
                                                ])
                ->add('hostname', ExoplanetsHostnameFieldType::class, ['attr' => ['class' => 'form-control'],
                                                'row_attr' => ['id' => 'hostname-field', 'class' => 'form-group col'],
                                                'available_hostname' => $formData['availableHostname'],
                                                'hostname_reset_redirect' => $formData['hostnameResetRedirect'],
                                                'label' => 'Host Name',
                                                'required' => FALSE
                ])
                ->add('discoverymethod', ChoiceType::class, ['attr' => ['class' => 'form-control'],
                                                'row_attr' => ['class' => 'form-group col'],
                                                'choices'  => array_merge(['Select value...' => -1], array_flip($formData['choices']['discoverymethodChoices'])),
                                                'label' => 'Discovery Method'
                ])
                ->add('disc_facility', ChoiceType::class, ['attr' => ['class' => 'form-control'],
                                                'row_attr' => ['class' => 'form-group col'],
                                                'choices'  => array_merge(['Select value...' => -1], array_flip($formData['choices']['discoveryFacilityChoices'])),
                                                'label' => 'Discovery Facility'
                ])
                ->add('disc_year', ChoiceType::class, ['attr' => ['class' => 'form-control'],
                                                'row_attr' => ['class' => 'form-group col'],
                                                'choices'  => array_flip($discoveryYearChoices),
                                                'label' => 'Discovery Year'
                ]);

    }

}