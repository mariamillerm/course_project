<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use AppBundle\Form\CategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
     *
     * @return Response
     */
    public function homepageAction(int $page = 1)
    {
        return $this->render('main/homepage.html.twig');
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
        return new Response($post->getId());
    }

    /**
     * @Route(
     *     "/post",
     *     methods={"POST"},
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
                //TODO Create PostType like CategoryType
                ->remove('delete');

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

            return $this->render(':main:category_create.html.twig', [
                'form' => $form->createView(),
                'error' => $message,
            ]);
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route(
     *     "/post/{id}",
     *     methods={"POST"},
     *     name="edit_post",
     *     requirements={"id": "\d+"}
     * )
     *
     * @param Post $post
     *
     * @return Response
     */
    public function editPostAction(Post $post)
    {
        return new Response('Edit post' . $post->getId());
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
        return new Response('Delete post' . $post->getId());
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

            $category = new Category('');
            $form = $this
                ->createForm(CategoryType::class, $category)
                ->remove('delete');

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
            return $this->redirectToRoute('homepage');
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

            if ($category !== null) {
                $em->remove($category);
                $em->flush();

                return $this->redirectToRoute('homepage');
            }

            return $this->redirectToRoute('homepage');
        } else {
            return $this->redirectToRoute('homepage');
        }
    }

    /**
     * @Route(
     *     "/category/{category}",
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
                ->createForm(CategoryType::class, $category);

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($form->get('save')->isClicked()) {
                    $category->setName($form->get('name')->getData());
                    $em->flush();

                    return $this->redirectToRoute('homepage');
                } else {
                    $em->remove($category);
                    $em->flush();

                    return $this->redirectToRoute('homepage');
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
            return $this->redirectToRoute('homepage');
        }
    }
}
