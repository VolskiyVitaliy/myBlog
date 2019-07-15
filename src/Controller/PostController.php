<?php

namespace App\Controller;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Entity\Comment;
use App\Form\PostType;
use App\Form\CommentType;


class PostController extends AbstractController
{
    /**
     * @Route("/post/new", name="create_post")
     * @IsGranted("ROLE_USER", message="No access! Get out!")
     */
    public function create(Request $req)
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($req);
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($post);
            $entityManager->flush();
            return $this->redirectToRoute('main');
        }
        return $this->render("post/create.html.twig", ['form' => $form->createView()]);
    }

    /**
     * @Route("/post/delete/{id}", name="delete_post")
     * @IsGranted("ROLE_USER", message="No access! Get out!")
     */
    public function delete(Request $req)
    {
        $id = $req->get('id');
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['id' => $id]);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('view-my');
    }

    /**
     * @Route("/post/edit/{slug}", name="edit_post")
     * @IsGranted("ROLE_USER", message="No access! Get out!")
     */
    public function update(Request $req, Post $post)
    {
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            return $this->redirectToRoute('view-my');
        }
        return $this->render("post/create.html.twig", ['form' => $form->createView()]);
    }

    /**
     * @Route("/post/{slug}", name="view_post")
     */
    public function viewPost(Request $req)
    {
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(['slug' => $req->get('slug')]);
        $comment_form = $this->createForm(CommentType::class);
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findBy(['post' => $post->getId()]);
        return $this->render("post/viewPost.html.twig", ['post' => $post, 'comment' => $comment_form->createView(), 'comments' => $comments]);
    }

    /**
     * @Route("/post/all/view-my", name="view_my_post")
     * @IsGranted("ROLE_USER", message="No access! Get out!")
     */
    public function viewMy(Request $request, PaginatorInterface $paginator)
    {
        $posts_all = $this->getDoctrine()->
        getRepository(Post::class)->
        findBy(['author' => $this->getUser()]);
        $posts = $paginator->paginate(
        // Doctrine Query, not results
            $posts_all,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );
        return $this->render("post/viewMy.html.twig", ['posts' => $posts]);
    }
}
