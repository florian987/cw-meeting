<?php

namespace App\Controller;

use App\Entity\Attendee;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class SubscriptionController extends Controller
{
    /**
     * @Route("/")
     */
    public function newAction()
    {
        return $this->render('new.html.twig');
    }

    /**
     * @Route("/subscribe")
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function subscribeAction(Request $request, \Swift_Mailer $mailer)
    {
        if(!$this->getParameter('subscriptions_open')) {
            throw $this->createAccessDeniedException();
        }

        $attendee = new Attendee();

        $form = $this->createForm(SubscriptionType::class, $attendee);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $attendee = $form->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($attendee);
            $entityManager->flush();

            $this->sendPaymentEmail($attendee, $mailer);

            $this->addFlash(
                'success',
                'Ton inscription est bien prise en compte. Nous avons envoyé un email à l\'adresse indiquée.'
            );

            return $this->redirectToRoute('app_subscription_list');
        }

        return $this->render(
            'subscribe.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * @Route("/list")
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(EntityManagerInterface $em)
    {
        $attendees = $em->getRepository('App:Attendee')->findBy([], ['updatedAt' => 'desc']);

        return $this->render(
            'list.html.twig',
            [
                'attendees' => $attendees,
            ]
        );
    }

    /**
     * @Route("/list/iframe")
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listIframeAction(EntityManagerInterface $em)
    {
        $attendees = $em->getRepository('App:Attendee')->findBy([], ['updatedAt' => 'desc']);

        return $this->render(
            'iframe.html.twig',
            [
                'attendees' => $attendees,
            ]
        );
    }

    /**
     * @Route("/admin/attendees")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function adminListAction(EntityManagerInterface $em)
    {
        $attendees = $em->getRepository('App:Attendee')->findBy([], ['createdAt' => 'asc']);

        return $this->render(
            'list_admin.html.twig',
            [
                'attendees' => $attendees,
            ]
        );
    }

    /**
     * @Route("/admin/attendees/{id}/confirm")
     * @param Attendee $attendee
     * @param \Swift_Mailer $mailer
     * @return Response
     */
    public function confirmPaymentAction(Attendee $attendee, \Swift_Mailer $mailer, EntityManagerInterface $em)
    {
        if ($attendee->getHasPaid()) {
            return new Response('Already paid');
        }

        $message = (new \Swift_Message('Inscription Meeting au Pal - 21 avril 2018'))
            ->setFrom('benjamin@coastersworld.fr')
            ->setTo($attendee->getEmail())
            ->setBody(
                $this->renderView(
                    'emails/confirm_payment.txt.twig'
                ),
                'text/plain'
            );

        $mailer->send($message);

        $attendee->setHasPaid(1);
        $em->persist($attendee);
        $em->flush();

        $name = $attendee->getFirstname().' '.$attendee->getName();

        return new Response("Paiment pour $name validé");
    }

    private function sendPaymentEmail(Attendee $attendee, \Swift_Mailer $mailer)
    {
        if ($attendee->getTicketNumber() === 0) {
            $template = 'emails/no_payment.txt.twig';
        } else {
            $template = 'emails/payment.html.twig';
        }

        $message = (new \Swift_Message('Inscription Meeting au Pal - 21 avril 2018'))
            ->setFrom('benjamin@coastersworld.fr')
            ->setTo($attendee->getEmail())
            ->setBody(
                $this->renderView(
                    $template,
                    array('attendee' => $attendee)
                ),
                'text/plain'
            );

        $mailer->send($message);
    }
}