<?php

namespace Demo\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username')
            ->add('password')
            ->add('firstName')
            ->add('lastName')
            ->add('email')
            ->add('lastLogin')
            ->add('note')
            ->add('created')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Demo\UserBundle\Entity\User'
        ));
    }

    public function getName()
    {
        return 'demo_userbundle_user';
    }
}
