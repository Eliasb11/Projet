<?php

namespace App\Controller;

use App\Entity\Stats;
use App\Entity\User;
use App\Form\ModificationType;
use App\Form\RegistrationType;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
{

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     */

    public function registration(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder, MailerInterface $mailer)
    {
        $user = new User();


        $form = $this->createForm(RegistrationType::class,$user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()){


            $stats= new Stats();
            $stats->setDefaite(0);
            $stats->setVictoire(0);
            $stats->setParties(0);
            $stats->setPartiesEnCours(0);
            $stats->setPartiesTerminees(0);

            $manager->persist($stats);
            $manager->flush();

            $hash = $encoder->encodePassword($user, $user->getPassword());

            $user->setPassword($hash);
            $user->setStats($stats);

            $manager->persist($user);
            $manager->flush();

            $email = (new TemplatedEmail())
                ->from('beyondgameagency@gmail.com')
                ->to($user->getEmail())
                ->subject('Confirmation Inscription')
                ->htmlTemplate('mail/mail.html.twig');

            $mailer->send($email);




            return $this->redirectToRoute('user_profil');
        }

        return $this->render('security/registration.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route ("/modifier", name="app_modifier")
     */

    public function modification(Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder){
        $user =$this->getUser();

        $form = $this->createForm(ModificationType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()&& $form->isValid()){
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('message', 'Profil mis ?? jour');

            return $this->redirectToRoute('user_profil');
        }


        return $this->render('security/modification.html.twig', ['form' => $form->createView()]);
    }


}