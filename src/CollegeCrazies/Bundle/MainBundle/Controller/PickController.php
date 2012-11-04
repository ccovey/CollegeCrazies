<?php

namespace CollegeCrazies\Bundle\MainBundle\Controller;

use CollegeCrazies\Bundle\MainBundle\Form\PickFormType;
use CollegeCrazies\Bundle\MainBundle\Form\PickSetFormType;
use CollegeCrazies\Bundle\MainBundle\Entity\Team;
use CollegeCrazies\Bundle\MainBundle\Entity\User;
use CollegeCrazies\Bundle\MainBundle\Entity\Pick;
use CollegeCrazies\Bundle\MainBundle\Entity\PickSet;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PickController extends Controller
{
    /**
     * @Route("/pickset/list", name="pickset_list")
     * @Secure(roles="ROLE_USER")
     * @Template()
     */
    public function listAction()
    {
        return array(
            'pickSets' => $this->getUser()->getPickSets(),
        );
    }

    /**
     * @Route("/pickset/new", name="pickset_new")
     * @Secure(roles="ROLE_USER")
     * @Template("CollegeCraziesMainBundle:Pick:new.html.twig")
     */
    public function newPickAction()
    {
        $user = $this->getUser();

        $pickSet = new PickSet();
        $pickSet->setUser($user);

        if ($this->getRequest()->query->has('leagueId')) {
            $league = $this->findLeague($this->getRequest()->get('leagueId'));
            $pickSet->setLeague($league);
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $games = $em->getRepository('CollegeCraziesMainBundle:Game')->findAll();
        $idx = count($games);
        foreach ($games as $game) {
            $pick = new Pick();
            $pick->setGame($game);
            $pick->setConfidence($idx);

            $pickSet->addPick($pick);
            $idx--;
        }

        $form = $this->getPickSetForm($pickSet);

        return array(
            'form' => $form->createView(),
            'league' => isset($league) ? $league : null,
        );
    }

    /**
     * @Route("/pickset/league/{leagueId}/{picksetId}", name="pickset_league")
     * @Secure(roles="ROLE_USER")
     * @Template("CollegeCraziesMainBundle:Pick:new.html.twig")
     */
    public function setPicksetLeague($leagueId, $picksetId)
    {
        $league = $this->findLeague($leagueId);

        if ($league->isLocked()) {
            $this->get('session')->setFlash('error', 'This league is locked');
        } else {
            $pickset = $this->findPickSet($picksetId);
            $pickset->setLeague($league);

            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($league);
            $em->persist($pickset);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pickset_view', array(
            'id' => $picksetId,
        )));
    }

    /**
     * @Route("/pickset/edit/{id}", name="pickset_edit")
     * @Secure(roles="ROLE_USER")
     * @Template("CollegeCraziesMainBundle:Pick:edit.html.twig")
     */
    public function editPickAction($id)
    {
        $pickSet = $this->findPickSet($id);
        $user = $this->getUser();

        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            if ($pickSet->isLocked()) {
                $this->get('session')->setFlash('info', 'This pickset is now locked');

                return $this->redirect($this->generateUrl('pickset_view', array(
                    'id' => $id
                )));
            }

            if ($pickSet->getUser() !== $user) {
                $this->get('session')->setFlash('error','You cannot edit another users picks');
                return $this->redirect('/');
            }
        }

        $form = $this->getPickSetForm($pickSet);
        return array(
            'form' => $form->createView(),
            'pickSet' => $pickSet,
        );
    }

    /**
     * @Route("/pickset/view/{id}", name="pickset_view")
     * @Secure(roles="ROLE_USER")
     * @Template("CollegeCraziesMainBundle:Pick:view.html.twig")
     */
    public function viewPickAction($id)
    {
        $pickSet = $this->findPickSet($id);
        $user = $this->getUser();

        if ($pickSet->getUser() !== $user && !$pickSet->isLocked()) {
            $this->get('session')->setFlash('error','You cannot view another users picks until the league is locked');
            return $this->redirect('/');
        }

        return array(
            'pickSet' => $pickSet,
        );
    }

    /**
     * @Route("/pickset/create", name="pickset_create")
     * @Secure(roles="ROLE_USER")
     */
    public function createPickAction()
    {
        $pickSet = new PickSet();
        $form = $this->getPickSetForm($pickSet);
        $request = $this->getRequest();
        $form->bindRequest($request);

        if (!$form->isValid()) {
            die('todo');
        }

        $user = $this->getUser();
        $pickSet->setUser($user);

        $em = $this->get('doctrine.orm.entity_manager');

        $user->addPickSet($pickSet);
        $em->persist($user);
        $em->persist($pickSet);
        $em->flush();
        return $this->redirect($this->generateUrl('pickset_edit', array(
            'id' => $pickSet->getId()
        )));
    }

    /**
     * @Route("/pickset/update/{id}", name="pickset_update")
     * @Secure(roles="ROLE_USER")
     */
    public function updatePickAction($id)
    {
        $pickSet = $this->findPickSet($id);

        if ($pickSet->isLocked()) {
            return $this->redirect($this->generateUrl('pickset_view', array(
                'id' => $id
            )));
        }

        $form = $this->getPickSetForm($pickSet);
        $form->bindRequest($this->getRequest());

        $em = $this->get('doctrine.orm.entity_manager');

        $em->persist($pickSet);
        $em->flush();

        return $this->redirect($this->generateUrl('pickset_edit', array(
            'id' => $pickSet->getId()
        )));
    }

    private function getPickForm(Pick $pick)
    {
        return $this->createForm(new PickFormType(), $pick);
    }

    private function getPickSetForm(PickSet $pickSet)
    {
        return $this->createForm(new PickSetFormType(), $pickSet);
    }

    private function findPickSet($id)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $pickSet = $em->createQuery('SELECT ps, u, p from CollegeCraziesMainBundle:Pickset ps
            JOIN ps.user u
            JOIN ps.picks p
            WHERE ps.id = :id
            ORDER BY p.confidence desc'
        )
            ->setParameter('id', $id)
            ->getSingleResult();

        if (!$pickSet) {
            throw new NotFoundHttpException(sprintf('There was no pickSet with id = %s', $id));
        }

        return $pickSet;
    }

    private function findLeague($id)
    {
        $league = $this
            ->get('doctrine.orm.entity_manager')
            ->getRepository('CollegeCraziesMainBundle:League')
            ->find($id);

        if (!$league) {
            throw new NotFoundHttpException(sprintf('There was no league with id = %s', $id));
        }

        return $league;
    }
}
