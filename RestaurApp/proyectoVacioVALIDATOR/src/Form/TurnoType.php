<?php
namespace App\Form;

use App\Entity\Turno;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TurnoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('horaInicio', TimeType::class, [
                'label' => 'Hora de inicio',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('horaFin', TimeType::class, [
                'label' => 'Hora de fin',
                'input' => 'datetime',
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Turno::class,
        ]);
    }
}