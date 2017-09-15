<<?php 

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Query\QueryString;
use Elastica\Query;

class PostsController extends Controller
{
	/**
     * @Route(path="/posts/{id}", requirements={"id": "\d+"}, name="post")
     */
    public function postAction(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $post->setRating($post->getRating() + 1);
        $em->persist($post);
        $em->flush();
        return $this->render('show_post.html.twig', array(
            'post' => $post,
        ));
    }

    /**
     * @Route(path="/search", name="post_search")
     */
    public function searchAction(Request $request)
    {
        $query = $request->request->get('query');
        if ($query) {
            $finder = $this->get('fos_elastica.finder.search.posts');
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
     * @Route(path="/posts/category/{id}", name="by_category")
     */
    public function showByCategory($id)
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository('AppBundle:Post')->findBy(['category' => $id]);
        return $this->render('show_posts_by_category.html.twig', array(
            'posts' => $posts,
        ));
    }

}
