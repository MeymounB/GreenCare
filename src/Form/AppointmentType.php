<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Entity\Plant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'error_bubbling' => true,
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => true,
                'attr' => [
                    'rows' => 10,
                ],
                'error_bubbling' => true,
            ])

            ->add('plannedAt', DateTimeType::class, [
                'label' => 'Date souhaitée',
                'widget' => 'single_text',
                'html5' => false,
                'required' => true,
                'format' => 'yyyy-MM-dd HH:mm',
                'error_bubbling' => true,
                'constraints' => [
                    new Range([
                        'min' => 'now',
                        'minMessage' => 'La date doit être supérieure à la date actuelle',
                    ]),
                ],
            ])

            ->add('isPresential', ChoiceType::class, [
                'choices' => [
                    'Présentiel' => true,
                    'Distanciel' => false,
                ],
                'label' => 'Vous préférez un rendez-vous :',
                'error_bubbling' => true,
                'required' => true,
            ])
            ->add('address', ChoiceType::class, [
                'choices' => $options['address'],
                'choice_label' => function ($value) {
                    if (is_object($value)) {
                        return $value->__toString();
                    } else {
                        return $value;
                    }
                },
                'choice_value' => function ($value) {
                    if (is_object($value)) {
                        return $value->getId();
                    } else {
                        return 0;
                    }
                },
                'label' => 'Adresse',
                'error_bubbling' => true,
                'required' => false,
            ])
            ->add('plants', EntityType::class, [
                'mapped' => false,
                'class' => Plant::class,
                'choices' => $options['plants'],
                'choice_label' => function ($value) {
                    if (is_object($value)) {
                        return $value->__toString();
                    } else {
                        return $value;
                    }
                },
                'choice_value' => function ($value) {
                    if (is_object($value)) {
                        return $value->getId();
                    } else {
                        return 0;
                    }
                },
                'label' => 'Plantes',
                'error_bubbling' => true,
                'required' => true,
                'multiple' => true,

                'constraints' => [
                    new NotBlank([
                        'message' => 'Vous devez choisir au moins une plante',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
        $resolver
            ->setRequired('address')
            ->setAllowedTypes('address', 'array');
        $resolver
            ->setRequired('plants')
            ->setAllowedTypes('plants', 'array');
    }
}
