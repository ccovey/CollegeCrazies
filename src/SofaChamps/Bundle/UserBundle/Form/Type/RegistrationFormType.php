<?php

namespace SofaChamps\Bundle\UserBundle\Form\Type;

use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @DI\FormType(alias="sofachamps_user_registration")
 */
class RegistrationFormType extends BaseType
{
    /**
     * @DI\InjectParams({
     *      "class" = @DI\Inject("%fos_user.model.user.class%")
     * })
     */
    public function __construct($class)
    {
        parent::__construct($class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        // Additional Fields
        $builder
            ->add('firstName', 'text', array(
                'required' => false,
                'label' => 'First Name',
            ))
            ->add('lastName', 'text', array(
                'required' => false,
                'label' => 'Last Name',
            ))
            ->add('emailFromCommish', 'checkbox', array(
                'required' => false,
                'label' => 'Receive Email From League Commissioner',
            ))
        ;
    }

    public function getName()
    {
        return 'sofachamps_user_registration';
    }
}
