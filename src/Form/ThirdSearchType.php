<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThirdSearchType extends AbstractType
{
    final public const CHECK_CHOICES = [
        'check.language' => 'language',
        'check.errolda' => 'errolda',
        'check.debts' => 'debts',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('id', null, ['label' => 'third.id'])
            ->add('nif', null, ['label' => 'third.nif'])
            ->add('check', ChoiceType::class, [
                'label' => 'check.third',
                'choices' => [
                    'check.language' => 'language',
                    'check.errolda' => 'errolda',
                    'check.debts' => 'debts',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
