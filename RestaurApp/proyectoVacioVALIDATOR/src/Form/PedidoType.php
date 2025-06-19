<?php

namespace App\Form;

use App\Entity\Atencion;
use App\Entity\Mesa;
use App\Entity\Pedido;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PedidoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fechaHora', null, [
                'widget' => 'single_text'
            ])
            ->add('mesa', EntityType::class, [
                'class' => Mesa::class,
'choice_label' => 'id',
            ])
            ->add('atencion', EntityType::class, [
                'class' => Atencion::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pedido::class,
        ]);
    }
}
