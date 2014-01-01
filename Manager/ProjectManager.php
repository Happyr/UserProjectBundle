<?php

namespace HappyR\UserProjectBundle\Manager;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Factory\ProjectFactory;
use HappyR\UserProjectBundle\Services\MailerService;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class ProjectManager
 *
 * @author Tobias Nyholm
 *
 */
class ProjectManager
{
    /**
     * @var MailerService mailer
     *
     *
     */
    protected $mailer;

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
     * @param MailerService $mailer
     * @param PermissionManager $pm
     * @param ProjectFactory $pf
     */
    public function __construct(
        ObjectManager $om,
        MailerService $mailer,
        PermissionManager $pm,
        ProjectFactory $pf
    ) {
        $this->mailer = $mailer;
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
}
