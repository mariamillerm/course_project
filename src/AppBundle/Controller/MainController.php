<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
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
     * @return Response
     */
    public function createPostAction()
    {
        return new Response('Post creation');
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
     *     path="/category/{category}",
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
     * @return Response
     */
    public function createCategoryAction()
    {
        return new Response('Category creation');
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
        return new Response('Delete category' . $category->getId());
    }

    /**
     * @Route(
     *     "/category/{category}",
     *     methods={"POST"},
     *     name="edit_category",
     *     requirements={"category": "\d+"}
     * )
     *
     * @param Category $category
     *
     * @return Response
     */
    public function editCategoryAction(Category $category)
    {
        return new Response('Edit category' . $category->getId());
    }
}
