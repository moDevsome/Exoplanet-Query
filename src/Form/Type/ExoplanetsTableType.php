<?php
// src/Form/Type/ExoplanetsFilterType.php
namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\FormBuilderInterface;

class ExoplanetsTableType extends AbstractType
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
            'csrf_field_name' => '_table_form_token',
            // an arbitrary string used to generate the value of the token
            // using a different string for each form improves its security
            'csrf_token_id'   => 'table_form_token'
        ]);

    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $formData = $builder->getData();
        $builder->setData($formData['userStates']);

        $builder->add('row_count', ChoiceType::class, ['attr' => ['class' => 'form-control'],
                                                    'row_attr' => ['class' => 'form-group col', 'id' => 'row_count'],
                                                    'choices'  => array_flip($formData['row_count_choice']),
                                                    'choice_translation_domain' => FALSE
                                                    ])
                ->add('current_page', HiddenType::class, ['attr' => ['class' => 'form-control'],
                                                    'row_attr' => ['class' => 'form-group col', 'id' => 'current_page'],
                                                    ])
                ->add('current_order_col', HiddenType::class, ['attr' => ['class' => 'form-control'],
                                                    'row_attr' => ['class' => 'form-group col', 'id' => 'current_order_col'],
                                                    ])
                ->add('current_order_dir', HiddenType::class, ['attr' => ['class' => 'form-control'],
                                                    'row_attr' => ['class' => 'form-group col', 'id' => 'current_order_dir'],
                                                    ]);

    }

}