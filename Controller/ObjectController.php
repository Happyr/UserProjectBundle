<?php

namespace HappyR\UserProjectBundle\Controller;

use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Form\ObjectType;
use HappyR\UserProjectBundle\Model\OpusModel;

/**
 * Class ObjectController
 *
 * @author Tobias Nyholm
 *
 *
 */
class ObjectController extends Controller
{

    /**
     * Add new user to the project
     *
     * @param Request $request
     * @param Project $project
     *
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Project $project)
    {
        $this->get('happyr.user.project.security_manager')->verifyUserIsGranted('CREATE', $project);
        $response = $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));

        $objectModel = new OpusModel();

        $form = $this->createForm(
            new ObjectType(),
            $objectModel,
            array(
                'company' => $project->getCompany(),
            )
        );

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $this->get('session')->getFlashbag()->add(
                'fail',
                'happyr.user.project.project.flash.object.is_null'
            );

            return $response;
        }

        $object = $objectModel->getOpus();

        /*
         * Check if the object belongs to an other project
         */
        //FIXME this is the only time we do $object->getProject(). Remove it
        if ($object->getProject() != null) {
            $objectProject = $object->getProject();
            if (!$this->get('happyr.user.project.security_manager')->userIsGranted(
                'DELETE',
                $objectProject
            )
            ) {
                $this->get('session')->getFlashbag()->add(
                    'fail',
                    'happyr.user.project.project.flash.object.other_project'
                );

                return $response;
            }
        }

        //add the object to this project
        $this->get('happyr.user.project.permission_manager')->addOpus($project, $object);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.object.added'
        );

        return $response;
    }

    /**
     * Remove an object from the project
     *
     * @param Project $project
     * @param Opus $object
     *
     * @ParamConverter("project")
     * @ParamConverter("object", options={"id"="object_id"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Project $project, ProjectObjectInterface $object)
    {
        $this->get('happyr.user.project.security_manager')->verifyUserIsGranted('DELETE', $project);

        $this->get('happyr.user.project.permission_manager')->removeOpus($project, $object);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->persist($object);
        $em->flush();

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.object.removed'
        );

        return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
    }
}
