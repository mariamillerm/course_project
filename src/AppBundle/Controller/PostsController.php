<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Post;
use Elastica\Query;
use Elastica\Query\QueryString;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PostsController extends Controller
{
    /**
     * @Route(path="/posts/{id}", requirements={"id": "\d+"}, name="post")
     *
     * @param Post $post
     *
     * @return Response
     */
    public function postAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();

        $post->setRating($post->getRating() + 1);
        $em->flush();

        return $this->render('show_post.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/posts", name="posts")
     */
    public function postsAction()
    {
        return $this->render('show_posts.html.twig');
    }

    /**
     * @Route(path="/search", name="post_search")
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
     * @TODO requirements
     * @Route(path="/posts/category/{category}", name="by_category")
     *
     * @param Category $category
     *
     * @return Response
     */
    public function showByCategory(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em
            ->getRepository('AppBundle:Post')
            ->findByCategory($category);

        return $this->render('show_posts_by_category.html.twig', array(
            'posts' => $posts,
        ));
    }
}
