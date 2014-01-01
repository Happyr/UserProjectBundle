<?php

namespace HappyR\UserProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class UserType
 *
 * @author Tobias Nyholm
 *
 *
 */
class UserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add(
                'user',
                'entity',
                array(
                    'label' => 'happyr.user.project.project.form.user.label',
                    'class' => 'Eastit\UserBundle\Entity\User',
                    'choices' => $options['choices'],
                    'empty_value' => '',
                    'attr' => array(
                        'data-help' => 'happyr.user.project.project.form.user.help',
                    )
                )
            )
            ->add(
                'submit',
                'submit',
                array(
                    'label' => 'form.add'

                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'HappyR\UserProjectBundle\Model\UserModel',
                'company' => null,
                'choices' => array()
            )
        );

        $resolver->setRequired(
            array(
                'company',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'happyr_user_project_project_user_form';
    }
}
