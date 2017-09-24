<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use AppBundle\Form\CategoryType;
use AppBundle\Form\PostType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
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
        $categories = $em->getRepository(Category::class)->findAll();
        $query = $em->getRepository(Post::class)->getPostsQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', $page),
            10
        );

        return $this->render(':main:show_posts.html.twig', [
            'pagination' => $pagination,
            'categories' => $categories,
        ]);
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
        $categories = $em->getRepository(Category::class)->findAll();
        $post->addRating();
        $em->flush();

        return $this->render(':main:show_post.html.twig', [
            'post' => $post,
            'categories' => $categories,
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

            $post = new Post($this->getUser());
            $form = $this
                ->createForm(PostType::class, $post)
                ->add('save', SubmitType::class, [
                    'label' => 'post.create'
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

                    return $this->redirectToRoute('homepage');
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

            $oldTitle = $post->getTitle();
            $oldImage = $post->getImage();
            $image = new File($this->getParameter('image_root').'/'.$oldImage);
            $post->setImage($image);

            $form = $this
                ->createForm(PostType::class, $post)
                ->add('edit', SubmitType::class, [
                    'label' => 'post.edit'
                ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($em
                    ->getRepository(Post::class)
                    ->isUnique($form->getData(), $oldTitle)) {

                    $file = $post->getImage();
                    if ($file != null) {
                        $filename = md5(uniqid()).'.'.$file->guessExtension();
                        $file->move(
                            $this->getParameter('image_root'),
                            $filename
                        );
                        $post->setImage($filename);
                    } else {
                        $post->setImage($oldImage);
                    }
                    $em->flush();

                    return $this->redirectToRoute('homepage');
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

            return $this->redirectToRoute('homepage');
        } else {
            return $this->render(':errors:error.html.twig', [
                'status_code' => Response::HTTP_FORBIDDEN,
                'status_text' => 'You don\'t have permissions to do this!',
            ]);
        }
    }

    /**
     * @Route(path="/categories", methods={"GET"}, name="categories")
     *
     * @return Response
     */
    public function categoryListAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findAll();

        return $this->render(':main:categories.html.twig', [
            'categories' => $categories
        ]);
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

        return $this->render(':main:show_posts_by_category.html.twig', [
            'posts' => $posts,
            'category' => $category,
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
                ->add('save', SubmitType::class, [
                    'attr'      => ['class' => 'button-link save'],
                    'label'     => 'category.create',
                ]);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($em
                    ->getRepository(Category::class)
                    ->isUnique($form->getData())) {
                        $em->persist($category);
                        $em->flush();

                        return $this->redirectToRoute('homepage');
                } else {
                    return $this->render(':errors:error.html.twig', [
                        'status_code' => Response::HTTP_CONFLICT,
                        'status_text' => 'There is a category with the same name!',
                    ]);
                }
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
                ->createForm(CategoryType::class, $category)
                ->remove('parent')
                ->add('save', SubmitType::class, [
                    'attr'      => ['class' => 'button-link save'],
                    'label'     => 'category.edit',
                ]);


            $oldName = $category->getName();
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $isUnique = $em
                    ->getRepository(Category::class)
                    ->isUnique($form->getData(), $oldName);
                if ($isUnique) {
                    $category->setName($form->get('name')->getData());
                    $em->flush();

                    return $this->redirectToRoute('homepage');
                } else {
                    return $this->render(':errors:error.html.twig', [
                        'status_code' => Response::HTTP_CONFLICT,
                        'status_text' => 'There is a category with the same name!',
                    ]);
                }
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
