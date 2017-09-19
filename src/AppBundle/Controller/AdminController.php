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
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;

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

        return new RedirectResponse($this->generateUrl('show_posts'));
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
     * @Route(path="/admin/user/{user}/edit", name="user_edit", requirements={"user": "\d+"})
     *
     * @param User $user
     *
     * @return Response
     */
    public function editUserAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->edit($user);
        $em->flush();

        return $this->json([
            'status' => 'Changed',
        ], 200);
    }

    /**
     * @Route(path="/admin/users_show", name="users_show")
     */
    public function usersAction()
    {
        // @TODO Remove render
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

    	return $this->render('users_show.html.twig', [
            'users' => $users,
        ]);

    /**
     * @var $paginator \Knp\Component\Pager\Paginator
     */
        $paginator  = $this->get('knp_paginator');
        $result = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 10)
        );
    }

    /**
     * @Route(path="/admin/user/{user}/block", name="user_block", requirements={"user": "\d+"})
     *
     * @param User $user
     *
     * @return Response
     */
    public function blockUserAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->block($user);

        return $this->json([
            'status' => 'Blocked',
        ], 200);
    }


    /**
     * @Route(path="/admin/users/ajax", name="admin_users_show_ajax")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function usersShowAction(Request $request)
    {
        /** 
        *@var \Doctrine\ORM\EntityRepository $repository 
        *
        */
        $repository = $this->getDoctrine()->getRepository('AppBundle:User');
        $queryBuilder = $repository->createQueryBuilder('u');

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
