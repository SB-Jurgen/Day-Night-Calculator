<?php
namespace App\Form;

use App\Validator\Constraints\TimeValid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class Time extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('start_time', TimeType::class,
                [
                    'label' => 'Start time',
                    'input'  => 'datetime',
                    'widget' => 'single_text',
                    'model_timezone' => 'Europe/Tallinn',
                    'constraints' => [
                        new TimeValid(),
                    ],
                ])
            ->add('end_time', TimeType::class,
                [
                    'label' => 'End time',
                    'input'  => 'datetime',
                    'widget' => 'single_text',
                    'model_timezone' => 'Europe/Tallinn',
                    'constraints' => [
                        new TimeValid(),
                    ],
                ])
            ->add('submit', SubmitType::class, ['label' => 'Submit']);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}

