<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserEdit;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller
{
    /**
     * @Route(path="/admin", name="admin_home")
     */
    public function homeAction()
    {
        return $this->render('admin_home.html.twig');
    }

    /**
     * @Route(path="/admin/posts", name="admin_posts")
     */
    public function postsAction()
    {
        // @TODO Show posts
        return new RedirectResponse($this->generateUrl('homepage'));
    }

    /**
     * @Route(path="/admin/users/{user}", name="admin_user", requirements={"user": "\d+"})
     *
     * @param User $user
     * @param Request $request
     *
     * @return Response
     */
    public function userAction(User $user, Request $request)
    {
        // @TODO Remove form, use AJaX
        $form = $this->createForm(UserEdit::class, [
            'role' => $user->getRole()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole($form->get('role')->getData());

            $this->getDoctrine()->getManager()->flush();

            return new RedirectResponse($this->generateUrl('edit_users'));
        }

        return $this->render('user_edit.html.twig', [
            'form' => $form->createView(),
            'username' => $user->getUsername(),
        ]);
    }

    /**
     * @Route(path="/admin/users_show", name="edit_users")
     */
    public function usersAction()
    {
        // @TODO Pagination
        // @TODO Remove render
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

    	return $this->render('users_show.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route(path="/admin/user/{user}/remove", name="user_remove", requirements={"user": "\d+"})
     *
     * @param User $user
     *
     * @return Response
     */
    public function removeUserAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->json([
            'status' => 'Removed',
        ], 200);
    }

    /**
     * @Route(path="/admin/ajax/users", name="admin_users_show_ajax")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function usersShowAction(Request $request)
    {
        // @TODO TODO
        $page = $request->get('page');
        $rows = $request->get('rows');
        if ($request->getQueryString() === '') {
            $response = [
                'cols' => [
                    [
                        'name' => 'id',
                    ],
                    [
                        'name' => 'username',
                    ],
                    [
                        'name' => 'role',
                    ],
                ],
                'sortable' => ['id', 'username'],
                'filterable' => ['role']
            ];

            return new JsonResponse($response);
        } else {
            $em = $this->getDoctrine()->getManager();
            $repository= $em->getRepository('AppBundle:User');
            dump($request->get('sortField'));
            dump($request->get('field'));
            if (($request->get('sortField') !== null) && ($request->get('field') === null)) {
                if ($request->get('order') === 'true') {
                    $order = 'ASC';
                } else {
                    $order = 'DESC';
                }

                $pages = ceil(count($repository->createQueryBuilder('u')
                    ->orderBy('u.' . $request->get('sortField'), $order)
                    ->getQuery()->getResult())/$rows);

                $result = $repository->createQueryBuilder('u')
                    ->orderBy('u.' . $request->get('sortField'), $order)
                    ->getQuery()->getResult();
            } else if (($request->get('field') !== null) && ($request->get('sortField') === null)) {
                $pages = ceil(count($em->getRepository('AppBundle:User')->findAll()) / $rows);
                $result = $repository->createQueryBuilder('u')
                    ->where('u.' . $request->get('field') . ' LIKE :pattern')
                    ->setParameter('pattern', '%' . $request->get('pattern') . '%')
                    ->getQuery()
                    ->getResult();
            } else {
                $pages = ceil(count($repository->findAll()) / $rows);
                $result = $repository->createQueryBuilder('u');
                $result = $result->setFirstResult(($page - 1) * $rows)
                    ->setMaxResults($rows)->getQuery()->getResult();
            }

            $response = [];
            foreach ($result as $user) {
                $response[] = [$user->getId(), $user->getUsername(), $user->getRole()];
            }

            return new JsonResponse([
                'data' => $response,
                'pages' => $pages
            ]);
        }
    }
}
