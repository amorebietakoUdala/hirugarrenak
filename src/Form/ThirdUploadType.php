<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThirdUploadType extends AbstractType
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
            ->add('file', FileType::class, ['label' => 'third.checkFile.file'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
