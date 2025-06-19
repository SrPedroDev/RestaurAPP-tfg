<?php

namespace App\Form;

use App\Entity\Atencion;
use App\Entity\Mesa;
use App\Entity\Reserva;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AtencionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('inicio', null, [
                'widget' => 'single_text'
            ])
            ->add('fin', null, [
                'widget' => 'single_text'
            ])
            ->add('comensales')
            ->add('mesa', EntityType::class, [
                'class' => Mesa::class,
'choice_label' => 'id',
            ])
            ->add('reserva', EntityType::class, [
                'class' => Reserva::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Atencion::class,
        ]);
    }
}
