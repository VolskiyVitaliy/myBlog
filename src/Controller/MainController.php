<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use Knp\Component\Pager\PaginatorInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(Request $request, PaginatorInterface $paginator)
    {
        $posts_all = $this->getDoctrine()->getRepository(Post::class)->findBy([], ['updated_at' => 'DESC']);
        $posts = $paginator->paginate(
        // Doctrine Query, not results
            $posts_all,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );
        return $this->render("main/index.html.twig",['posts' => $posts]);
    }
}
