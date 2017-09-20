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
     *
     * @param AuthenticationUtils $authUtils
     *
     * @return Response
     */
    public function loginAction(AuthenticationUtils $authUtils)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY');
        if ($hasAccess) {
            return $this->redirectToRoute('homepage');
        } else {
            $error = $authUtils->getLastAuthenticationError();
            $lastUsername = $authUtils->getLastUsername();

            return $this->render(':security:login.html.twig', [
                'last_username' => $lastUsername,
                'error' => $error,
            ]);
        }
    }

    /**
     * @Route("/activate/{hash}", name="activate")
     *
     * @param string $hash
     *
     * @return Response
     */
    public function activateAction(string $hash)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var ConfirmationToken|null $token
         */
        $token = $this
            ->getDoctrine()
            ->getRepository(ConfirmationToken::class)
            ->findOneByHash($hash);

        if ($token !== null) {
            $user = $token->getUser();
            $user->setIsActive(true);

            $em->remove($token);
            $em->flush();

            return $this->render(':security:login.html.twig', [
                'last_username' => $user->getEmail(),
                'message' => 'account.confirmed',
            ]);
        } else {
            // TODO error page with message "Invalid token"
            return $this->render(':security:registration_success.html.twig', [
                'message' => 'token.invalid',
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/forgot", name="forgotPassword")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function forgotPasswordAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(ForgotPasswordType::class, $user);

        $em = $this->getDoctrine()->getManager();
        $tokenService = $this->get('app.token_service');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $em
                ->getRepository(User::class)
                ->findOneByEmail($form->get('email')->getData());

            if ($user === null) {
                // TODO Error page
                return $this->render(':security:registration_success.html.twig', [
                    'message' => 'user.not_found',
                ])->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            $token = new ResetToken($user, $tokenService->generateToken());

            $em->persist($token);
            $em->flush();

            $this->get('app.email_support')->sendRecoveryEmail($user, $token);

            return $this->redirectToRoute('homepage');
        }

        $error = $form->getErrors()->current();
        $message = null;
        if ($error !== false) {
            $message = $error->getMessage();
        }

        return $this->render(
            ':security:forgot.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]
        );
    }

    /**
     * @Route("reset/{hash}", name="resetPassword")
     *
     * @param string $hash
     * @param Request $request
     *
     * @return Response
     */
    public function resetPasswordAction(string $hash, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /**
         * @var ResetToken $token
         */
        $token = $em->getRepository(ResetToken::class)->findOneByHash($hash);

        if ($token !== null) {
            if ($token->isDisabled()) {
                $em->remove($token);
                $em->flush();

                // TODO Error page
                return $this->render(':security:registration_success.html.twig', [
                    'message' => 'token.expired',
                ]);
            }

            $user = $token->getUser();
            $form = $this->createForm(ResetPasswordType::class, $user);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $user->setPlainPassword($form->get('password')->getData());
                $this->get('app.user_service')->encodePassword($user);
                $em->remove($token);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':security:reset_password.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]);
        } else {
            // TODO Error page
            return $this->render(':security:registration_success.html.twig', [
                'message' => 'token.not_found',
            ])->setStatusCode(Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/signup", name="signup")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function signupAction(Request $request)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('IS_AUTHENTICATED_FULLY');
        if ($hasAccess) {
            return $this->redirectToRoute('homepage');
        } else {
            $em = $this->getDoctrine()->getManager();

            $user = new User();
            $form = $this
                ->createForm(UserType::class, $user)
                ->remove('role');

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $tokenService = $this->get('app.token_service');

                $this->get('app.user_service')->encodePassword($user);
                $token = new ConfirmationToken($user, $tokenService->generateToken());

                $em->persist($user);
                $em->persist($token);
                $em->flush();

                $this->get('app.email_support')->sendActivationEmail($user, $token);

                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render('security/signup.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]);
        }
    }

}