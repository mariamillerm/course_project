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
        $token = $this->get('app.token_service')->findConfirmationToken($token);

        if ($token !== null) {
            $this->get('app.user_service')->activateUser($token);

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
            $user = $this->get('app.user_service')->findUserByEmail($form->getData());

            if ($user === null) {
                return $this->render('security/registration_success.html.twig', [
                    'message' => 'Something is going wrong there!',
                ]);
            }

            $this->get('app.token_service')->setResetTokenToUser($user);

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'security/forgot_password.html.twig', [
                'form' => $form->createView(),
                'error' => $this->get('app.form_service')->getFormErrorMessage($form),
            ]
        );
    }

    /**
     * @Route("reset_password/{token}", name="resetPassword")
     *
     * @param string $token
     * @param Request $request
     *
     * @return Response
     */
    public function resetPasswordAction(string $token, Request $request)
    {
        $token = $this->get('app.token_service')->findResetToken($token);

        if ($token !== null) {
            if ($this->get('app.token_service')->isResetTokenDisabled($token)) {

                return $this->render('security/registration_success.html.twig', [
                    'message' => 'Something is going wrong there!',
                ]);
            }

            $user = $token->getUser();
            $form = $this->createForm(ResetPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->get('app.user_service')->resetUserPassword($user, $token);

                return $this->redirectToRoute('homepage');
            }

            return $this->render(
                'security/reset_password_type.html.twig', [
                    'form' => $form->createView(),
                    'error' => $this->get('app.form_service')->getFormErrorMessage($form),
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