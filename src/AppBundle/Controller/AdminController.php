<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use AppBundle\Entity\Post;
use AppBundle\Form\PostType;
use AppBundle\Entity\Category;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;

class AdminController extends Controller
{
    /**
     * @Route(path="/admin", methods={"GET"}, name="admin_home")
     */
    public function homeAction()
    {
        return $this->render(':admin:account.html.twig');
    }

    /**
     * @Route("/admin/categories", methods={"GET"}, name="categories_show")
     *
     * @return Response
     */
    public function categoryListAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();

        return $this->render(':admin:categories_show.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     * @Route(
     *     "/admin/posts",
     *     methods={"GET"},
     *     name="posts_show",
     *     requirements={"id": "\d+"}
     * )
     *
     * @return Response
     */
    public function postsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository(Category::class)->findAll();
        $posts = $em->getRepository(Post::class)->findAll();

        return $this->render(':admin:posts_show.html.twig', [
            'posts' => $posts,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route(
     *     "/admin/post",
     *     methods={"GET", "POST"},
     *     name="create_post"
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createPostAction(Request $request)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();

            $post = new Post($this->getUser());
            $form = $this
                ->createForm(PostType::class, $post)
                ->add('save', SubmitType::class, [
                    'label' => 'post.create'
                ])
                ->remove('similarPosts')
                ->add('similarPosts', EntityType::class, [
                    'multiple' => true,
                    'class' => 'AppBundle\Entity\Post',
                    'label' => 'post.similarPosts',
                    'required' => false,
                    'empty_data' => null,
                ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($em->getRepository(Post::class)->isUnique($form->getData())) {

                    $file = $post->getImage();
                    if ($file != null) {
                        $filename = md5(uniqid()).'.'.$file->guessExtension();
                        $file->move(
                            $this->getParameter('image_root'),
                            $filename
                        );
                        $post->setImage($filename);
                    } else {
                        $post->setImage('not_found.jpg');
                    }

                    $em->persist($post);
                    $em->flush();

                    return $this->redirectToRoute('posts_show');
                }

                return $this->render(':errors:error.html.twig', [
                    'status_code' => Response::HTTP_CONFLICT,
                    'status_text' => 'There is a post with the same title!',
                ]);
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':admin:post_create.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]);
        } else {
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_FORBIDDEN,
                'status_text' => 'You don\'t have permissions to do this!',
            ]);
        }
    }

//    /**
//     * @Route(path="/admin/users/{user}", name="admin_user", requirements={"user": "\d+"})
//     *
//     * @param User $user
//     * @param Request $request
//     *
//     * @return Response
//     */
//    public function userAction(User $user, Request $request)
//    {
//        // @TODO Remove form, use AJaX
//        $form = $this->createForm(UserType::class, [
//            'role' => $user->getRole()
//        ]);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            $user->setRole($form->get('role')->getData());
//
//            $this->getDoctrine()->getManager()->flush();
//
//            return new RedirectResponse($this->generateUrl('edit_users'));
//        }
//
//        return $this->render(':admin:user_edit.html.twig', [
//            'form' => $form->createView(),
//            'username' => $user->getUsername(),
//        ]);
//    }

    /**
     * @Route(path="/admin/users", name="users_show")
     */
    public function usersAction()
    {
        // @TODO Remove render
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

    	return $this->render(':admin:users_show.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route(path="/admin/users/{user}", name="edit_user", requirements={"user": "\d+"})
     *
     * @param User $user
     * @param Request $request
     *
     * @return Response
     */
    public function userAction(User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var User $user
         */
        $user = $em->getRepository(User::class)->find($user->getId());
        $form = $this
            ->createForm(UserType::class, $user)
            ->remove('username')
            ->remove('email')
            ->remove('plainPassword')
            ->add('edit', SubmitType::class, [
                'label' => 'user.edit',
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRole($form->get('role')->getData());
            $em->flush();

            return new RedirectResponse($this->generateUrl('users_show'));
        }

        return $this->render(':admin:user_edit.html.twig', [
            'form' => $form->createView(),
            'username' => $user->getUsername(),
        ]);
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
        $user->setIsActive(false);
        $em->flush();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render(':admin:users_show.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route(path="/admin/user/{user}/unblock", name="user_unblock", requirements={"user": "\d+"})
     *
     * @param User $user
     *
     * @return Response
     */
    public function unblockUserAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $user->setIsActive(true);
        $em->flush();

        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render(':admin:users_show.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/admin/posts/columns", name="columns_ajax")
     *
     * @return string
     */
    public function getColumnNames()
    {
        return $this->json([
            "id",
            "title",
            "author",
            "creationDate",
        ]);
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
