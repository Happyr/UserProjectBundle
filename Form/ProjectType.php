<?php

namespace Happyr\UserProjectBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProjectType.
 *
 * @author Tobias Nyholm
 */
class ProjectType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add(
                'description',
                'textarea',
                array(
                    'required' => false,
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
                'data_class' => 'Happyr\UserProjectBundle\Entity\Project',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'happyr_user_project_project_form';
    }
}
