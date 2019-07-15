<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentType;

class CommentController extends AbstractController
{

    public function getCommentForm(Request $req)
    {
        $form = $this->createForm(CommentType::class);

        return $this->redirectToRoute("main");
    }

    /**
     * @Route("/comment/post/{id}", name="comment")
     * @IsGranted("ROLE_USER", message="No access! Get out!")
     */
    public function setComment(Request $req)
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $post = $this->getDoctrine()->getRepository(Post::class)->findOneBy(["id"=>$req->get("id")]);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid())
        {
            $data = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $comment->setContent($data->getContent());
            $comment->setAuthor($this->getUser());
            $comment->setPost($post);
            $em->persist($comment);

            $em->flush();

            return $this->redirectToRoute("viewPost",['slug' => $post->getSlug()]);
        }
        return $this->redirectToRoute("main");
    }
}
