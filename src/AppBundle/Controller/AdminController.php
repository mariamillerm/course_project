<?php

namespace AppBundle\Controller;

//use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use AppBundle\Form\UserEdit;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


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
        return new RedirectResponse($this->generateUrl('homepage'));
    }

    /**
     * @Route(path="/admin/users/{id}", name="admin_user", requirements={"id": "\d+"})
     * @param User $user
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function userAction(User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($user);
        $form = $this->createForm(UserEdit::class, array('role' => $user->getRole()[0]));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole($form->get('role')->getData());
            $em->persist($user);
            $em->flush();
            return new RedirectResponse($this->generateUrl('edit_users'));
        }
        return $this->render('user_edit.html.twig', array(
            'form' => $form->createView(),
            'username' => $user->getUsername(),
        ));
    }

    /**
     * @Route(path="/admin/users_show", name="edit_users")
     */
    public function usersAction()
    {
    	$em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('AppBundle:User')->findAll();
        return $this->render('users_show.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * @Route(path="/admin/user/{id}/remove", name="user_remove", requirements={"id": "\d+"})
     *
     * @param User $user
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function userRemoveAction(User $user, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $at = $em->getRepository('AppBundle:ActivationToken')->findOneBy(
            ['user' => $id]
        );
        $em->remove($at);
        $em->remove($user);
        $em->flush();
        return $this->redirectToRoute('admin_users');
    }

    /**
     * @Route(path="/admin/users", name="admin_users")
     */
    public function adminUserAction()
    {
        return $this->render('users_show_ajax.html.twig');
    }

    /**
     * @Route(path="/admin/ajax/users", name="admin_users_show_ajax")
     * @param Request $request
     * @return JsonResponse
     */
    public function usersShowAction(Request $request)
    {
        $page = $request->get('page');
        $rows = $request->get('rows');
        if ($request->getQueryString() == '') {
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
            if (($request->get('sortField')!=null) && ($request->get('field')==null)) {
                if ($request->get('order')=='true') {
                    $order='ASC';
                } else {
                    $order='DESC';
                }

                $pages = ceil(count($repository->createQueryBuilder('u')
                    ->orderBy('u.'.$request->get('sortField'), $order)
                    ->getQuery()->getResult())/$rows);

                $result = $repository->createQueryBuilder('u')
                    ->orderBy('u.'.$request->get('sortField'), $order)
                    ->getQuery()->getResult();
            } else if (($request->get('field')!=null) && ($request->get('sortField')==null)) {
                $pages = ceil(count($em->getRepository('AppBundle:User')->findAll()) / $rows);
                $result = $repository->createQueryBuilder('u')
                    ->where('u.' . $request->get('field') . ' LIKE :pattern')
                    ->setParameter('pattern', '%' .$request->get('pattern') . '%')
                    ->getQuery()->getResult();
            } else {
                $pages=ceil(count($repository->findAll())/$rows);
                $result = $repository->createQueryBuilder('u');
                $result=$result->setFirstResult(($page - 1) * $rows)
                    ->setMaxResults($rows)->getQuery()->getResult();
            }

            $response=[];
            foreach ($result as $user) {
                $response[] = [$user->getId(), $user->getUsername(), $user->getRole()[0]];
            }
            return new JsonResponse([
                'data' => $response,
                'pages' => $pages
            ]);
        }
    }
}