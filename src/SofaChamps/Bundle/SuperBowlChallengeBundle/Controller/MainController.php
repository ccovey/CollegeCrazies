<?php

namespace SofaChamps\Bundle\SuperBowlChallengeBundle\Controller;

use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends BaseController
{
    /**
     * @Route("/")
     * @Template()
     */
    public function homeAction()
    {
        return array();
    }
}