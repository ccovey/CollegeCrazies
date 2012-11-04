<?php

namespace CollegeCrazies\Bundle\MainBundle\Form;

use CollegeCrazies\Bundle\MainBundle\Form\UserFormType;
use CollegeCrazies\Bundle\MainBundle\Form\PickFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PickSetFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('user', new UserFormType())
            ->add('name', 'text')
            ->add('tiebreakerHomeTeamScore', 'integer')
            ->add('tiebreakerAwayTeamScore', 'integer')
            ->add('picks', 'collection', array(
                'type' => new PickFormType(),
                'allow_add' => true,
            ))
        ;
    }

    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'CollegeCrazies\Bundle\MainBundle\Entity\PickSet'
        );
    }

    public function getName()
    {
        return 'pickset';
    }
}
