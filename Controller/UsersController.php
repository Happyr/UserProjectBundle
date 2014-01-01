<?php

namespace HappyR\UserProjectBundle\Controller;

use HappyR\UserProjectBundle\Events\ProjectEvent;
use HappyR\UserProjectBundle\ProjectEvents;
use Eastit\Lego\OpusBundle\Entity\Opus;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use HappyR\UserProjectBundle\Entity\Project;
use Carlin\BaseBundle\Controller\BaseController;
use Carlin\CoreBundle\Response\JsonResponse;
use HappyR\UserProjectBundle\Form\UserEditType;
use HappyR\UserProjectBundle\Form\UserType;
use HappyR\UserProjectBundle\Model\UserModel;

use Eastit\UserBundle\Entity\User;

/**
 * Class ManagerController
 *
 * @author Tobias Nyholm
 *
 * @Route("/manager/projects/{id}/user", requirements={"id" = "\d+"})
 *
 */
class UsersController extends BaseController
{

    /**
     * Add new user to the project
     *
     * @param Request $request
     * @param Project $project
     * @param Opus $opus
     *
     * @Route("/add", name="_manager_project_user_add")
     * @Route("/add/{opus_id}", name="_manager_project_user_add_with_opus", requirements={"opus_id" = "\d+"})
     * @Method("POST")
     * @ParamConverter("opus", options={"id"="opus_id"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Project $project, Opus $opus = null)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('MASTER', $project);
        $permissionManager = $this->get('happyr.user.project.permission_manager');

        $userModel = new UserModel();

        $form = $this->createForm(
            new UserType(),
            $userModel,
            array(
                'company' => $project->getCompany(),
                'choices' => $this->getRepo('HappyRUserProjectBundle:Project')->findUsersToProject($project),
            )
        );

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $this->get('session')->getFlashbag()->add(
                'fail',
                'happyr.user.project.project.flash.user.error'
            );

            if ($project->isPublic()) {
                return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
            } else {
                return $this->redirect($this->generateUrl('_manager_projects'));
            }
        }

        //if you try to add a user that already is a part of the project
        if ($project->getUsers()->contains($userModel->getUser())) {
            return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
        }

        /**
         * Make sure that the project is a public project, or create a new public project
         */
        if (!$project->isPublic()) {
            $users = $project->getUsers();

            //we can be sure that a private project only have one user
            $user = $users[0];

            $factory = $this->get('happyr.user.project.project_factory');
            $project = $factory->getNew();
            $project->setName(date('Ymd') . ' - ' . $user->getUsername())
                ->setCompany($user->getCompany());

            $factory->create($project);
            $permissionManager->addUser($project, $user, 'MASTER');

            //add the opus onto this new project
            $permissionManager->addOpus($project, $opus);
        }

        $newUser = $userModel->getUser();
        $permissionManager->addUser($project, $newUser);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        //fire event
        $event = new ProjectEvent($project, $newUser);
        $this->get('event_dispatcher')->dispatch(ProjectEvents::USER_INVITED, $event);

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.user.added'
        );

        return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @Route("/{user_id}/edit", name="_manager_project_user_edit", requirements={"user_id" = "\d+"})
     * @Method("POST")
     * @ParamConverter("project")
     * @ParamConverter("user", options={"id"="user_id"})
     *
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function editAction(Request $request, Project $project, User $user)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('MASTER', $project);

        /*
         * Make sure that the user is in the project
         */
        if ($user->getCompany()->getId() != $project->getCompany()->getId()) {
            throw new \InvalidArgumentException('The User and the Project does not belong to the same company.');
        }

        $mask = $request->request->get('mask');
        $this->get('happyr.user.project.permission_manager')->changePermissions($project, $user, $mask);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        return new JsonResponse();
    }

    /**
     * Remove an user from the project
     *
     * @param Request $request
     * @param Project $project
     * @param User $user
     * @Route("/{user_id}/remove", name="_manager_project_user_remove", requirements={"user_id" = "\d+"})
     * @Method("GET")
     *
     * @ParamConverter("project")
     * @ParamConverter("user", options={"id"="user_id"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, Project $project, User $user)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('MASTER', $project);

        $this->get('happyr.user.project.permission_manager')->removeUser($project, $user);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.user.removed'
        );

        if ($request->query->has('redirect')) {
            return $this->redirect($request->query->get('redirect'));
        }

        return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
    }
}
