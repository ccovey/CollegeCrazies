<?php

namespace CollegeCrazies\Bundle\MainBundle\Controller;

use CollegeCrazies\Bundle\MainBundle\Form\UserFormType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class HomePageController extends Controller
{
    /**
     * @Route("/")
     */
    public function homepageAction()
    {
        $user = $this->getUser();

        $template = $this->get('security.context')->isGranted('ROLE_USER')
            ? 'CollegeCraziesMainBundle::homepage.auth.html.twig'
            : 'CollegeCraziesMainBundle::homepage.unauth.html.twig';

        $form = $this->createForm(new UserFormType());
        return $this->render($template , array(
            'form' => $form->createView(),
            'user' => $user
        ));
    }

    /**
     * @Route("/how-to", name="howto")
     * @Template("CollegeCraziesMainBundle::howto.html.twig")
     */
    public function howtoAction()
    {
        return array();
    }

    /**
     * @Route("/about", name="about")
     * @Template("CollegeCraziesMainBundle::about.html.twig")
     */
    public function aboutAction()
    {
        return array();
    }

    /**
     * @Route("/donate", name="donate")
     * @Template("CollegeCraziesMainBundle::donate.html.twig")
     */
    public function donateAction()
    {
        return array();
    }

    /**
     * @Route("/donate-thanks", name="donate_thanks")
     * @Template("CollegeCraziesMainBundle::donate-thanks.html.twig")
     */
    public function donateThanksAction()
    {
        return array();
    }

    /**
     * @Route("/bowl-schedule", name="schedule")
     * @Template("CollegeCraziesMainBundle::schedule.html.twig")
     */
    public function scheduleAction()
    {
        return array(
            'games' => $this->get('doctrine.orm.entity_manager')->getRepository('CollegeCraziesMainBundle:Game')->findAllOrderedByDate(),
        );
    }

    /**
     * @Route("/prediction-info", name="prediction_info")
     * @Template("CollegeCraziesMainBundle::prediction-info.html.twig")
     */
    public function predictionInfoAction()
    {
        return array();
    }
}
