<?php

namespace HappyR\UserProjectBundle\Controller;

use Carlin\BaseBundle\Controller\BaseController;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class NonEnterpriseController
 *
 * @author Tobias Nyholm
 *
 * @Route("/manager/projects")
 *
 */
class NonEnterpriseController extends BaseController
{

    /**
     * Lists all Project entities.
     *
     * @Route("/no-enterprise", name="_manager_projects_non_enterprise")
     * @Method("GET")
     * @Template()
     *
     * @return array
     */
    public function indexAction()
    {
        return array();
    }
}
