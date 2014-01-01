<?php

namespace HappyR\UserProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class OpusType
 *
 * @author Tobias Nyholm
 *
 *
 */
class ObjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = count($options['choices']) == 0 ? $options['company']->getOpuses() : $options['choices'];

        $builder
            ->add(
                'opus',
                'entity',
                array(
                    'label' => 'happyr.user.project.project.form.opus.label',
                    'class' => 'Eastit\Lego\OpusBundle\Entity\Opus',
                    'choices' => $choices,
                    'empty_value' => '',
                    'attr' => array(
                        'data-help' => 'happyr.user.project.project.form.opus.help',
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
            //TODO fix data_class
            array(
                'data_class' => 'HappyR\UserProjectBundle\Model\OpusModel',
                'company' => null,
                'choices' => array()
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'happyr_user_project_project_object_form';
    }
}
