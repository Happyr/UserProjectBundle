<?php

namespace HappyR\UserProjectBundle\Controller;

use Carlin\CoreBundle\Response\JsonResponse;
use HappyR\UserProjectBundle\Form\ObjectType;
use HappyR\UserProjectBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use JMS\SecurityExtraBundle\Annotation\Secure;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Form\ProjectType;
use Carlin\BaseBundle\Controller\BaseController;

/**
 * Class ManagerController
 *
 * @author Tobias Nyholm
 *
 * @Route("/manager/projects")
 *
 */
class ManagerController extends BaseController
{
    /**
     * Lists all Project entities.
     *
     * @Route("/", name="_manager_projects")
     * @Method("GET")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_COMPANY_ENTERPRISE')) {
            return $this->redirect($this->generateUrl('_manager_projects_non_enterprise'));
        }

        $user = $this->getUser();
        $myProjects = $this->getRepo('HappyRUserProjectBundle:Project')->findUserProjects($user);

        $projects = $this->getRepo('HappyRUserProjectBundle:Project')->findNonUserProjects($user);

        return array(
            'myProjects' => $myProjects,
            'projects' => $projects,
        );
    }

    /**
     * Finds and displays a Project entity.
     *
     * @param Project $project
     * @Route("/{id}", name="_manager_project_show", requirements={"id" = "\d+"})
     * @Method("GET")
     * @ParamConverter("project")
     * @Template()
     *
     * @return array
     */
    public function showAction(Project $project)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('VIEW', $project);
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $userForm = $this->createForm(
            new UserType(),
            null,
            array(
                'action' => $this->generateUrl('_manager_project_user_add', array('id' => $project->getId())),
                'company' => $project->getCompany(),
                'choices' => $this->getRepo('HappyRUserProjectBundle:Project')->findUsersToProject($project),
            )
        );

        $opusForm = $this->createForm(
            new ObjectType(),
            null,
            array(
                'action' => $this->generateUrl('_manager_project_opus_add', array('id' => $project->getId())),
                'company' => $project->getCompany(),
                'choices' => $this->getRepo('HappyRUserProjectBundle:Project')->findOpusesToProject($project),
            )
        );

        return array(
            'userForm' => $userForm->createView(),
            'opusForm' => $opusForm->createView(),
            'project' => $project,
        );
    }

    /**
     * Remove an user from the project
     *
     * @param Project $project
     * @Route("/{id}/leave", name="_manager_project_leave", requirements={"id" = "\d+"})
     * @Method("GET")
     * @ParamConverter("project")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function leaveAction(Project $project)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('VIEW', $project);
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $user = $this->getUser();
        $permissionManager = $this->get('happyr.user.project.permission_manager');
        $permissionManager->removeUser($project, $user);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.user.leave');

        return $this->redirect($this->generateUrl('_manager_projects', array('id' => $project->getId())));
    }

    /**
     * Creates a new Project entity.
     *
     * @param Request $request
     *
     * @Route("/new", name="_manager_project_create")
     * @Template()
     *
     * @return array
     */
    public function newAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_COMPANY_ENTERPRISE')) {
            return $this->redirect($this->generateUrl('_manager_projects_non_enterprise'));
        }

        $company = $this->getUser()->getCompany();
        $factory = $this->get('happyr.user.project.project_factory');
        $project = $factory->getNew();
        $project->setCompany($company);
        $form = $this->createForm(
            new ProjectType(),
            $project,
            array(
                'action' => $this->generateUrl('_manager_project_create'),
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

                $em = $this->getEntityManager();
                $em->persist($project);
                $em->flush();

                $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.created');

                return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
            }
        }

        return array(
            'Project' => $project,
            'form' => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @param Request $request
     * @param Project $project
     * @Route("/{id}/edit", name="_manager_project_edit", requirements={"id" = "\d+"})
     * @ParamConverter("project")
     * @Template()
     *
     * @return array
     */
    public function editAction(Request $request, Project $project)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('MASTER', $project);
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $form = $this->createForm(
            new ProjectType(),
            $project,
            array(
                'action' => $this->generateUrl('_manager_project_edit', array('id' => $project->getId())),
            )
        );
        $form->add('submit', 'submit', array('label' => 'form.update'));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getEntityManager()->flush();

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
     * @Route("/{id}/remove", name="_manager_project_delete", requirements={"id" = "\d+"})
     * @ParamConverter("project")
     * @Template()
     *
     * @return array
     */
    public function deleteAction(Request $request, Project $project)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('MASTER', $project);
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $form = $this->createDeleteForm($project->getId());

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->get('happyr.user.project.project_factory')->remove($project);

                $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.deleted');

                return $this->redirect($this->generateUrl('_manager_projects'));
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
            ->setAction($this->generateUrl('_manager_project_delete', array('id' => $id)))
            ->add('submit', 'submit', array('label' => 'form.remove'))
            ->getForm();
    }

    /**
     * Remove an user from the project
     *
     * @param Project $project
     * @Route("/{id}/join-request", name="_manager_project_join", requirements={"id" = "\d+"})
     * @Method("GET")
     * @ParamConverter("project")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function joinRequestAction(Project $project)
    {
        if (!$project->isPublic()) {
            throw $this->createNotFoundException('Project not found');
        }

        $user = $this->getUser();
        /*
         * Make sure that the user is in the project
         */
        if ($user->getCompany()->getId() != $project->getCompany()->getId()) {
            throw new \InvalidArgumentException('The User and the Project does not belong to the same company.');
        }

        $this->get('happyr.user.project.project_manager')->addJoinRequest($project, $user);

        $this->get('session')->getFlashbag()->add('success', 'happyr.user.project.project.flash.user.join');

        return $this->redirect($this->generateUrl('_manager_projects'));
    }
}
