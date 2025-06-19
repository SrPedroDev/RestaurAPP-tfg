<?php

namespace App\Form;

use App\Entity\Pedido;
use App\Entity\PedidoItem;
use App\Entity\Producto;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PedidoItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cantidad')
            ->add('pedido', EntityType::class, [
                'class' => Pedido::class,
'choice_label' => 'id',
            ])
            ->add('producto', EntityType::class, [
                'class' => Producto::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PedidoItem::class,
        ]);
    }
}
