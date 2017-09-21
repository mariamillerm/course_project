<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use AppBundle\Form\CategoryType;
use AppBundle\Form\PostType;
use Doctrine\ORM\EntityRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Query;
use Elastica\Query\QueryString;
use Symfony\Component\HttpFoundation\Request;

class MainController extends Controller
{
    /**
     * @Route("/", methods={"GET"}, name="homepage")
     * @Route(
     *     "/posts/{page}",
     *     methods={"GET"},
     *     name="posts_page",
     *     requirements={"page": "\d+"},
     *     defaults={"page": 1}
     * )
     *
     * @param int $page
     * @param Request $request
     *
     * @return Response
     */
    public function homepageAction(int $page = 1, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getRepository(Post::class)->getPostsQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render(':main:show_posts.html.twig', array('pagination' => $pagination));
    }

    /**
     * @Route(
     *     "/post/{id}",
     *     methods={"GET"},
     *     name="post",
     *     requirements={"id": "\d+"}
     * )
     *
     * @param Post $post
     *
     * @return Response
     */
    public function postAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $post->addRating();
        $em->flush();

        return $this->render(':main:show_post.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route(
     *     "/post",
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

            $post = new Post();
            $form = $this
                ->createForm(PostType::class, $post)
                ->remove('creationDate')
                ->add('save', SubmitType::class, [
                    'label' => 'post.create'
                ]);;

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $post->setAuthor($this->getUser());
                $em->persist($post);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':main:post_create.html.twig', [
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

    /**
     * @Route(
     *     "/post/{id}/edit",
     *     methods={"GET", "POST"},
     *     name="edit_post",
     *     requirements={"id": "\d+"}
     * )
     *
     * @param Post $post
     * @param Request $request
     *
     * @return Response
     */
    public function editPostAction(Post $post, Request $request)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();
            $form = $this
                ->createForm(PostType::class, $post)
                ->add('edit', SubmitType::class)
                ->remove('creationDate');

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->flush();

                //TODO Right route
                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':main:post_edit.html.twig', [
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

    /**
     * @Route(
     *     "/post/{id}/delete",
     *     methods={"GET"},
     *     name="delete_post",
     *     requirements={"id": "\d+"}
     * )
     *
     * @param Post $post
     *
     * @return Response
     */
    public function deletePostAction(Post $post)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();

            //TODO Right route
            return $this->redirectToRoute('homepage');
        } else {
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_FORBIDDEN,
                'status_text' => 'You don\'t have permissions to do this!',
            ]);
        }
    }

    /**
     * @Route(
     *     path="/search",
     *     methods={"GET"},
     *     name="post_search"
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function searchAction(Request $request)
    {
        $query = $request->request->get('query');
        if ($query) {
            $finder = $this->get('fos_elastica.finder');
            $keywordQuery = new QueryString();
            $keywordQuery->setQuery('*'.$query.'*');
            $q = new Query();
            $q->setQuery($keywordQuery);
            $posts = $finder->find($q);
            dump($posts);

            return $this->render('search.html.twig', array(
                'posts' => $posts,
                'searched' => $query,
            ));
        }

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route(
     *     path="/category/{category}/posts",
     *     methods={"GET"},
     *     name="category_posts",
     *     requirements={"category": "\d+"}
     * )
     *
     * @param Category $category
     *
     * @return Response
     */
    public function categoryPostsAction(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em
            ->getRepository(Post::class)
            ->findByCategory($category);

        return $this->render('show_posts_by_category.html.twig', [
            'posts' => $posts,
        ]);
    }

    /**
     * @Route(
     *     "/category",
     *     methods={"GET", "POST"},
     *     name="create_category"
     * )
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createCategoryAction(Request $request)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();

            $category = new Category();
            $form = $this
                ->createForm(CategoryType::class, $category)
                ->remove('parent')
                ->add('parent', EntityType::class, [
                    'class' => 'AppBundle\Entity\Category',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                            ->orderBy('c.name', 'ASC');
                    },
                    'choice_label' => 'name',
                ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em->persist($category);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':main:category_create.html.twig', [
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

    /**
     * @Route(
     *     "/category/{category}/delete",
     *     methods={"GET"},
     *     name="delete_category",
     *     requirements={"category": "\d+"}
     * )
     *
     * @param Category $category
     *
     * @return Response
     */
    public function deleteCategoryAction(Category $category)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();

            return $this->redirectToRoute('homepage');
        } else {
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_FORBIDDEN,
                'status_text' => 'You don\'t have permissions to do this!',
            ]);
        }
    }

    /**
     * @Route(
     *     "/category/{category}",
     *     methods={"GET", "POST"},
     *     name="edit_category",
     *     requirements={"category": "\d+"}
     * )
     *
     * @param Category $category
     * @param Request $request
     *
     * @return Response
     */
    public function editCategoryAction(Category $category, Request $request)
    {
        $hasAccess = $this
            ->get('security.authorization_checker')
            ->isGranted('ROLE_MANAGER');
        if ($hasAccess) {
            $em = $this->getDoctrine()->getManager();
            $form = $this
                ->createForm(CategoryType::class, $category, [
                'categoryName' =>$category->getName(),
                ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $category->setName($form->get('name')->getData());
                $em->flush();

                //TODO Right route
                return $this->redirectToRoute('homepage');
            }

            $error = $form->getErrors()->current();
            $message = null;
            if ($error !== false) {
                $message = $error->getMessage();
            }

            return $this->render(':main:category_edit.html.twig', [
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
}
