<?php

namespace HappyR\UserProjectBundle\Controller;

use HappyR\UserProjectBundle\Events\ProjectEvent;
use HappyR\UserProjectBundle\ProjectEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Form\UserEditType;
use HappyR\UserProjectBundle\Form\UserType;
use HappyR\UserProjectBundle\Model\UserModel;


/**
 * Class UserController
 *
 * @author Tobias Nyholm
 *
 *
 */
class UserController extends Controller
{

    /**
     * Add new user to the project
     *
     * @param Request $request
     * @param Project $project
     * @param integer $objectId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Project $project, $objectId)
    {
        $this->get('happyr.user.project.security_manager')->verifyUserIsGranted('MASTER', $project);
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
                return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
            } else {
                return $this->redirect($this->generateUrl('happyr_user_project_project_index'));
            }
        }

        //if you try to add a user that already is a part of the project
        if ($project->getUsers()->contains($userModel->getUser())) {
            return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
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

        return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
    }

    /**
     * Displays a form to edit an existing Project entity.
     *
     * @param Request $request
     * @param Project $project
     * @param User $user

     * @ParamConverter("project")
     * @ParamConverter("user", options={"id"="user_id"})
     *
     *
     * @return JsonResponse
     * @throws \InvalidArgumentException
     */
    public function editAction(Request $request, Project $project, User $user)
    {
        $this->get('happyr.user.project.security_manager')->verifyUserIsGranted('MASTER', $project);

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
     *
     * @ParamConverter("project")
     * @ParamConverter("user", options={"id"="user_id"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Request $request, Project $project, User $user)
    {
        $this->get('happyr.user.project.security_manager')->verifyUserIsGranted('MASTER', $project);

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

        return $this->redirect($this->generateUrl('happyr_user_project_project_show', array('id' => $project->getId())));
    }
}
