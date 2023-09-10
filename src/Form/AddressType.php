<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', TextType::class, [
                'label' => 'Rue',
                'error_bubbling' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => "1 Rue de l'Exemple",
                ],
            ])
            ->add('zipCode', TextType::class, [
                'label' => 'Code postal',
                'error_bubbling' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => '75000',
                ],
            ])
            ->add('city', TextType::class, [
                'label' => 'Ville',
                'error_bubbling' => true,
                'required' => true,
                'attr' => [
                    'placeholder' => 'Paris',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
