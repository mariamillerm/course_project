<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;
use AppBundle\Form\ForgotPasswordType;
use AppBundle\Form\ResetPasswordType;
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
        $em = $this->getDoctrine()->getManager();
        $token = $em
            ->getRepository(ConfirmationToken::class)
            ->findOneBy([
                'token' => $token,
            ]);

        if ($token !== null) {
            $user = $token->getUser();
            $user->setIsActive(true);
            $em->remove($token);
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
     * @Route("/reset_password", name="forgotPassword")
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $user = new User();

        $form = $this->createForm(ForgotPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //TODO: put this in service
            $em = $this->getDoctrine()->getManager();
            $user = $form->getData();
            $user = $em
                ->getRepository(User::class)
                ->findOneBy([
                    'email' => $user->getEmail(),
                ]);

            $token = $em
                ->getRepository(ResetToken::class)
                ->findOneBy(['user' => $user]);
            if ($token != null) {
                $em->remove($token);
                $em->flush();
            }

            $userToken = new ResetToken();
            $userToken->setUser($user);
            $userToken->setToken(md5(openssl_random_pseudo_bytes(32)));

            $em->persist($userToken);
            $em->flush();

            $this
                ->get('app.email_support')
                ->sendRecoveryEmail($user, $userToken);

            return $this->redirectToRoute('homepage');
        }

        $errors = $form->getErrors();
        $error = $errors->current();

        $message = null;

        if ($error !== false) {
            $message = $error->getMessage();
        }

        return $this->render(
            'security/forgot_password.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]
        );
    }

    /**
     * @Route("reset_password/{token}", name="resetPassword")
     *
     * @param string $token
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     *
     * @return Response
     */
    public function resetPasswordAction(string $token, Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $em = $this->getDoctrine()->getManager();
        $token = $em
            ->getRepository(ResetToken::class)
            ->findOneBy([
                'token' => $token,
            ]);

        //TODO: put this in service
        if ($token !== null) {
            if (!$this->get('app.token_service')->isResetTokenActive($token)) {
                $em->remove($token);
                $em->flush();

                return $this->render('security/registration_success.html.twig', [
                    'message' => 'Something is going wrong there!',
                ]);
            }

            $user = $token->getUser();
            $form = $this->createForm(ResetPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = $passwordEncoder->encodePassword($user, $user->getPassword());
                $user->setPassword($password);
                $em->remove($token);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            $errors = $form->getErrors();
            $error = $errors->current();

            $message = null;

            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(
                'emails/reset_password.html.twig', [
                    'form' => $form->createView(),
                    'error' => $message,
                ]
            );
        } else {
            //TODO: Return status code 404
            return $this->render('security/registration_success.html.twig', [
                    'message' => 'Something is going wrong!',
            ]);
        }
    }

    /**
     * @Route("/signup", name="signup")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function signupAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.user_service')->createUser($user);

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'security/signup.html.twig',
            [
                'form' => $form->createView(),
                'error' => $this->get('app.form_service')->getFormErrorMessage($form),
            ]
        );
    }

}