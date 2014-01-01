<?php

namespace HappyR\UserProjectBundle\Controller;

use Eastit\Lego\OpusBundle\Entity\Opus;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use HappyR\UserProjectBundle\Entity\Project;
use Carlin\BaseBundle\Controller\BaseController;
use HappyR\UserProjectBundle\Form\ObjectType;
use HappyR\UserProjectBundle\Model\OpusModel;

/**
 * Class ManagerController
 *
 * @author Tobias Nyholm
 *
 * @Route("/manager/projects/{id}/opus", requirements={"id" = "\d+"})
 *
 */
class OpusesController extends BaseController
{

    /**
     * Add new user to the project
     *
     * @param Request $request
     * @param Project $project
     *
     * @Route("/add", name="_manager_project_opus_add")
     * @Method("POST")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request, Project $project)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('CREATE', $project);
        $response = $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));

        $opusModel = new OpusModel();

        $form = $this->createForm(
            new ObjectType(),
            $opusModel,
            array(
                'company' => $project->getCompany(),
            )
        );

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $this->get('session')->getFlashbag()->add(
                'fail',
                'happyr.user.project.project.flash.opus.is_null'
            );

            return $response;
        }

        $opus = $opusModel->getOpus();

        /*
         * Check if the opus belongs to an other project
         */
        if ($opus->getProject() != null) {
            $opusProject = $opus->getProject();
            if (!$this->get('carlin.user.company.security_manager')->userIsGrantedCheck(
                'DELETE',
                $opusProject,
                false
            )
            ) {
                $this->get('session')->getFlashbag()->add(
                    'fail',
                    'happyr.user.project.project.flash.opus.other_project'
                );

                return $response;
            }
        }

        //add the opus to this project
        $this->get('happyr.user.project.permission_manager')->addOpus($project, $opus);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->flush();

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.opus.added'
        );

        return $response;
    }

    /**
     * Remove an opus from the project
     *
     * @param Project $project
     * @param Opus $opus
     * @Route("/{opus_id}/remove", name="_manager_project_opus_remove", requirements={"opus_id" = "\d+"})
     * @Method("GET")
     *
     * @ParamConverter("project")
     * @ParamConverter("opus", options={"id"="opus_id"})
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeAction(Project $project, Opus $opus)
    {
        $this->get('carlin.user.company.security_manager')->userIsGrantedCheck('DELETE', $project);

        $this->get('happyr.user.project.permission_manager')->removeOpus($project, $opus);

        $em = $this->getEntityManager();
        $em->persist($project);
        $em->persist($opus);
        $em->flush();

        $this->get('session')->getFlashbag()->add(
            'success',
            'happyr.user.project.project.flash.opus.removed'
        );

        return $this->redirect($this->generateUrl('_manager_project_show', array('id' => $project->getId())));
    }
}
