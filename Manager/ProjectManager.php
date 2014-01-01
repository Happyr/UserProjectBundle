<?php

namespace HappyR\UserProjectBundle\Manager;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Factory\ProjectFactory;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use HappyR\UserProjectBundle\Services\MailerService;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ProjectManager
 *
 * @author Tobias Nyholm
 *
 * This is the class you should use when managing (adding/removing stuff) your projects
 *
 */
class ProjectManager
{
    /**
     * @var ObjectManager em
     *
     *
     */
    protected $em;

    /**
     * @var PermissionManager permissionManager
     *
     */
    protected $permissionManager;

    /**
     * @var ProjectFactory projectFactory
     *
     */
    protected $projectFactory;

    /**
     * @param ObjectManager $om
     * @param PermissionManager $pm
     * @param ProjectFactory $pf
     */
    public function __construct(
        ObjectManager $om,
        PermissionManager $pm,
        ProjectFactory $pf
    ) {
        $this->em = $om;
        $this->permissionManager = $pm;
        $this->projectFactory = $pf;
    }

    /**
     * Add a request to join the project
     *
     * @param Project &$project
     * @param IdentifierInterface &$user
     *
     */
    public function addJoinRequest(Project &$project, IdentifierInterface &$user)
    {

        //TODO fix this
        /*
         * get emails
         */
        $administrators = array();
        foreach ($project->getUsers() as $u) {
            if ($project->getPermission($u) == 'MASTER') {
                $administrators[] = $u;
            }
        }

        //send the emails
        foreach ($administrators as $administrator) {
            $this->mailer->sendJoinRequest($project, $administrator, $user);
        }
    }

    /**
     * Remove private projects are revoke permission from other projects
     *
     * @param IdentifierInterface &$user
     *
     */
    public function removeUserFromAllProjects(IdentifierInterface &$user)
    {
        $repo = $this->em->getRepository('HappyRUserProjectBundle:Project');
        $privateProject = $repo->findPrivateProject($user);

        $this->projectFactory->remove($privateProject);

        $projects = $repo->findUserProjects($user);
        foreach ($projects as $p) {
            $this->permissionManager->removeUser($p, $user);
        }
    }

    public function addUser(Project $project, ProjectMemberInterface $user)
    {
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

            $project = $this->projectFactory->getNew();
            $project->setName(date('Ymd') . ' - ' . $user->getUsername())
                ->setCompany($user->getCompany());

            $this->projectFactory->create($project);
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
    }

    public function removeUser(Project $project, ProjectMemberInterface $user)
    {
        $this->permissionManager->removeUser($project, $user);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

    }

    public function addObject(Project $project, ProjectObjectInterface $object)
    {
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
        $this->permissionManager->addObject($project, $object);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();
    }

    public function removeObject(Project $project, ProjectObjectInterface $object)
    {
        $this->permissionManager->removeOpus($project, $object);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->persist($object);
        $em->flush();
    }

    /**
     * Alias for PermissionManager
     *
     * @param Project $project
     * @param ProjectMemberInterface $user
     * @param string $mask
     *
     */
    public function changeUserPermissions(Project $project, ProjectMemberInterface $user, $mask)
    {
        $this->permissionManager->changePermissions($project, $user, $mask);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();
    }


}
