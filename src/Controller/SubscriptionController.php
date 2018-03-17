<?php

namespace App\Controller;

use App\Entity\Attendee;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
                'attendees' => $attendees
            ]
        );
    }

    /**
     * @Route("/listraw")
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listRawAction(EntityManagerInterface $em)
    {
        $attendees = $em->getRepository('App:Attendee')->findBy([], ['updatedAt' => 'desc']);

        return $this->render(
            'listraw.html.twig',
            [
                'attendees' => $attendees
            ]
        );
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