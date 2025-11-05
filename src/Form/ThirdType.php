<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ThirdType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $readonly = $options['readonly'];
        $builder
            ->add('id', null, [
                'disabled' => $readonly,
                'label' => 'third.id',
            ])
            ->add('nif', null, [
                'disabled' => $readonly,
                'label' => 'third.nif',
            ])
            ->add('full_name', null, [
                'disabled' => $readonly,
                'label' => 'third.full_name',
            ])
            ->add('language_preference', null, [
                'disabled' => $readonly,
                'label' => 'third.language_preference',
            ])
            ->add('address', null, [
                'disabled' => $readonly,
                'label' => 'third.address',
            ])
            ->add('number', null, [
                'disabled' => $readonly,
                'label' => 'third.number',
            ])
            ->add('zone', null, [
                'disabled' => $readonly,
                'label' => 'third.zone',
            ])
            ->add('province', null, [
                'disabled' => $readonly,
                'label' => 'third.province',
            ])
            ->add('country', null, [
                'disabled' => $readonly,
                'label' => 'third.country',
            ])
            ->add('floor', null, [
                'disabled' => $readonly,
                'label' => 'third.floor',
            ])
            ->add('door', null, [
                'disabled' => $readonly,
                'label' => 'third.door',
            ])
            ->add('zip_code', null, [
                'disabled' => $readonly,
                'label' => 'third.zip_code',
            ])
            ->add('type_of_road', null, [
                'disabled' => $readonly,
                'label' => 'third.type_of_road',
            ])
            ->add('default_address', CheckboxType::class, [
                'disabled' => $readonly,
                'label' => 'third.default_address',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'readonly' => false,
            // Configure your form options here
        ]);
    }
}
