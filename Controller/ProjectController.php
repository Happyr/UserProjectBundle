<?php

namespace Happyr\UserProjectBundle\Controller;

use Happyr\UserProjectBundle\Form\ObjectType;
use Happyr\UserProjectBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Form\ProjectType;

/**
 * Class ProjectController
 *
 * @author Tobias Nyholm
 *
 *
 */
class ProjectController extends Controller
{
    /**
     * Lists all Project entities.
     *
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $repo=$this->getDoctrine()->getRepository('HappyrUserProjectBundle:Project');
        $myProjects = $repo->findUserProjects($user);

        $projects = $repo->findNonUserProjects($user);

        return array(
            'myProjects' => $myProjects,
            'projects' => $projects,
        );
    }

    /**
     * Finds and displays a Project entity.
     *
     * @param Project $project
     *
     * @Template()
     *
     * @return array
     */
    public function showAction(Project $project)
    {
        $security=$this->get('happyr.user.project.security_manager');
        $security->verifyUserIsGranted('VIEW', $project);
        $security->verifyProjectIsPublic($project);

        return array(
            'project' => $project,
        );
    }

    /**
     * Remove an user from the project
     *
     * @param Project $project
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveAction(Project $project)
    {
        $security=$this->get('happyr.user.project.security_manager');
        $security->verifyUserIsGranted('VIEW', $project);
        $security->verifyProjectIsPublic($project);

        $user = $this->getUser();
        $this->get('happyr.user.project.permission_manager')->removeUser($project, $user);

        $em = $this->getDoctrine()->getManager();
        $em->persist($project);
        $em->flush();

        return $this->redirect($this->generateUrl('happyr_user_project_project_index'));
    }

    /**
     * Creates a new Project entity.
     *
     * @param Request $request
     *
     * @Template()
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        $factory = $this->get('happyr.user.project.project_factory');
        $project = $factory->getNew();

        $form = $this->createForm(
            new ProjectType(),
            $project,
            array(
                'action' => $this->generateUrl('happyr_user_project_project_create'),
            )
        );
        $form->add('submit', 'submit', array('label' => 'form.create'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                //save project before adding users
                $factory->create($project);

                //add current user to the project
                $user = $this->getUser();
                $permissionManager = $this->get('happyr.user.project.permission_manager');
                $permissionManager->addUser($project, $user, 'MASTER');

                $this->getDoctrine()->getManager()->flush();

                $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.created');

                return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
            }
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @param Request $request
     * @param Project $project
     *
     * @Template()
     *
     * @return array
     */
    public function editAction(Request $request, Project $project)
    {
        $security=$this->get('happyr.user.project.security_manager');
        $security->verifyUserIsGranted('MASTER', $project);
        $security->verifyProjectIsPublic($project);

        $form = $this->createForm(
            new ProjectType(),
            $project,
            array(
                'action' => $this->generateUrl('happyr_user_project_project_edit', array('id' => $project->getId())),
            )
        );
        $form->add('submit', 'submit', array('label' => 'form.update'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em=$this->getDoctrine()->getManager();
                $em->persist($project);
                $em->flush();

                $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.updated');
            }
        }

        return array(
            'project' => $project,
            'form' => $form->createView(),
        );
    }

    /**
     * Deletes a Project entity.
     *
     * @param Request $request
     * @param Project $project
     *
     * @Template()
     *
     * @return array
     */
    public function deleteAction(Request $request, Project $project)
    {
        $security=$this->get('happyr.user.project.security_manager');
        $security->verifyUserIsGranted('MASTER', $project);
        $security->verifyProjectIsPublic($project);

        $form = $this->createDeleteForm($project->getId());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('happyr.user.project.project_factory')->remove($project);

                return $this->redirect($this->generateUrl('happyr_user_project_project_index'));
            }
        }

        return array(
            'form' => $form->createView(),
            'project' => $project,
        );
    }

    /**
     * Creates a form to delete a Project entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('happyr_user_project_project_delete', array('id' => $id)))
            ->add('submit', 'submit', array('label' => 'form.remove'))
            ->getForm();
    }

    /**
     * Remove an user from the project
     *
     * @param Project $project
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function joinRequestAction(Project $project)
    {
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $user = $this->getUser();
        $this->get('happyr.user.project.project_manager')->addJoinRequest($project, $user);

        return $this->redirect($this->generateUrl('happyr_user_project_project_index'));
    }
}
