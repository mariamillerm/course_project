<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConfirmationToken;
use AppBundle\Entity\ResetToken;
use AppBundle\Entity\User;
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
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_BAD_GATEWAY,
                'status_text' => 'You are already in system!',
            ]);
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
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_BAD_REQUEST,
                'status_text' => 'Invalid token! Token has already been used or has not been found.',
            ]);
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
        $form = $this
            ->createForm(UserType::class, $user)
            ->remove('role')
            ->remove('username')
            ->remove('plainPassword');

        $em = $this->getDoctrine()->getManager();
        $tokenService = $this->get('app.token_service');

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $em
                ->getRepository(User::class)
                ->findOneByEmail($form->get('email')->getData());

            if ($user === null) {
                return $this->render(':errors:error.html.twig', [
                    'status_code' => Response::HTTP_BAD_REQUEST,
                    'status_text' => 'There is no user with such email!',
                ]);
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
     * @Route("/reset/{hash}", name="resetPassword")
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

                return $this->render(':errors:error.html.twig', [
                    'status_code' => Response::HTTP_REQUEST_TIMEOUT,
                    'status_text' => 'The token was deleted. Retry the password recovery request.',
                ]);
            }

            $user = $token->getUser();
            //TODO Solve this problem
            $user->setPlainPassword('');
            $form = $this
                ->createForm(UserType::class, $user)
                ->remove('username')
                ->remove('email')
                ->remove('role');

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
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
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_BAD_REQUEST,
                'status_text' => 'Token not found!',
            ]);
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

            return $this->render(':security:signup.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]);
        }
    }

}