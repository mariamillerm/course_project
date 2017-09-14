<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authUtils
     *
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/activate/{token}", name="activate")
     * @param string $token
     *
     * @return Response
     */
    public function activateAccountAction(string $token)
    {
        $doctrine = $this->getDoctrine();
        $em = $doctrine->getManager();
        $userRepository = $doctrine->getRepository(User::class);

        $user = $userRepository->findOneBy([
            'confirmationCode' => $token,
        ]);

        if ($user !== null) {
            $user->setIsActive(true);

            $em->flush();

            return $this->render('security/registration_success.html.twig', [
                'message' => 'Your account is confirmed. Please, login.',
            ]);
        } else {
            //TODO: return status code(404)
            return $this->render('security/registration_success.html.twig', [
                'message' => 'There is no such user!',
            ]);
        }
    }

    /**
     * @Route("/signup", name="signup")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signupAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //TODO: put this in service
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);
            $user->setRole('ROLE_USER');

            $userToken = new ConfirmationToken();
            $userToken->setUser($user);
            $userToken->setToken(md5(openssl_random_pseudo_bytes(32)));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->persist($userToken);
            $em->flush();

            $this
                ->get('app.email_support')
                ->sendActivationEmail($user, $userToken);

            return $this->redirectToRoute('homepage');
        }

        $errors = $form->getErrors();
        $error = $errors->current();

        $message = null;

        if ($error !== false) {
            $message = $error->getMessage();
        }

        return $this->render(
            'security/signup.html.twig',
            [
                'form' => $form->createView(),
                'error' => $message,
            ]
        );
    }

}