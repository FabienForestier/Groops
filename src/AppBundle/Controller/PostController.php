<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Entity\User;
use AppBundle\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PostController extends Controller
{
    /**
     * @Route("/posts", name="posts")
     */
    public function indexAction()
    {
        $posts = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->findAll();
        
        return $this->render('posts/index.html.twig',array('posts' => $posts));
    }

    /**
     * @Route("/posts/detail/{id}", name="post-detail")
     */
    public function detailAction($id, Request $request)
    {
        $post = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->find($id);

        $author = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($post->getAuthor());

        return $this->render('posts/detail.html.twig', array(
            'post' => $post,
            'author' => $author/*,
            'commentForm' => $commentForm->createView()*/
        ));
    }

    /**
     * @Route("/comment/{postId}/new", name="comment_new")
     * @Method("POST")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @ParamConverter("post", options={"mapping": {"postId": "id"}})
     *
     * NOTE: The ParamConverter mapping is required because the route parameter
     * (postSlug) doesn't match any of the Doctrine entity properties (slug).
     * See http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html#doctrine-converter
     */
    public function commentNewAction(Request $request)
    {
        // $form = $this->createFormBuilder()
        //     ->add('content',TextareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;min-height:300px;')))
        //     ->add('submit',SubmitType::class, array('attr' => array('value' => 'Send Comment', 'class' => 'btn btn-success', 'style' => 'margin-bottom:15px')))
        //     ->getForm();

        // $form->handleRequest($request);

        // if ($form->isSubmitted() && $form->isValid()) {
        //     /** @var Comment $comment */
        //     $authorId = $this->getUser()->getId();
        //     $postId = $post->getId();
        //     $content = $form['content']->getData();
        //     //$publishedDate = new \DateTime('now');

        //     $comment->setAuthorId($authorId);
        //     $comment->setPostId($postId);
        //     $comment->setContent($content);
        //     //$comment->setPublishedDate($publishedDate);
        //     $comment->setLikes(0);

        //     $em = $this->getDoctrine()->getManager();
        //     $em->persist($comment);
        //     $em->flush();

        //     return $this->redirectToRoute('post-detail', array('id' => $post->getId()));
        // }
        // return $this->render('comment/new.html.twig', array(
        //     'post' => $post,
        //     'form' => $form->createView(),
        // ));
    }

     /**
     * @Route("/posts/create", name="post-create")
     */
    public function createAction(Request $request)
    {
        $post = new Post;
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();

        $form = $this->createFormBuilder($post)
            ->add('title',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('summary',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('content',TextareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px;min-height:300px;')))
            ->add('featuredImage',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('status', ChoiceType::class, array('choices' => array("Draft" => 0, "Published" => 1), 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('submit',SubmitType::class, array('attr' => array('class' => 'btn btn-success', 'style' => 'margin-bottom:15px')))
            ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //Get data
                $title = $form['title']->getData();
                $summary = $form['summary']->getData();
                $content = $form['content']->getData();
                $featuredImage = $form['featuredImage']->getData();
                $author = $this->getUser()->getId();
                $status = $form['status']->getData();
                $publishedDate = new \Datetime('now');

                $slug = str_replace("  ", " ", $title);
                $slug = str_replace("/[-!?\*%]/g", "", $title);
                $slug = str_replace(" ", "-", $title);

                $post->setTitle($title);
                $post->setSummary($summary);
                $post->setContent($content);
                $post->setFeaturedImage($featuredImage);
                $post->setAuthor($author);
                $post->setStatus($status);
                $post->setPublishedDate($publishedDate);
                $post->setSlug($slug);
                $post->setUpdatedDate($publishedDate);

                $em = $this->getDoctrine()->getManager();

                $em->persist($post);
                $em->flush();

                $this->addFlash('notice','Post Created');

                return $this->redirectToRoute('posts');
            }

        return $this->render('posts/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/posts/edit/{id}", name="post-edit")
     */
    public function editAction($id, Request $request)
    {
        $post = $this->getDoctrine()
            ->getRepository('AppBundle:Post')
            ->find($id);

        $form = $this->createFormBuilder($post)
            ->add('title',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('summary',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('content',TextareaType::class, array('attr' => array('id' => 'text-editor', 'class' => 'form-control', 'style' => 'margin-bottom:15px;min-height:300px;')))
            ->add('featuredImage',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('status', ChoiceType::class, array('choices' => array("Draft" => 0, "Published" => 1), 'attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('submit',SubmitType::class, array('attr' => array('class' => 'btn btn-success', 'style' => 'margin-bottom:15px')))
            ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //Get data
                $title = $form['title']->getData();
                $summary = $form['summary']->getData();
                $content = $form['content']->getData();
                $featuredImage = $form['featuredImage']->getData();
                $status = $form['status']->getData();
                $updatedDate = new \Datetime('now');

                $slug = str_replace("  ", " ", $title);
                $slug = str_replace("/[-!?\*%]/g", "", $title);
                $slug = str_replace(" ", "-", $title);

                $post->setTitle($title);
                $post->setSummary($summary);
                $post->setContent($content);
                $post->setFeaturedImage($featuredImage);
                $post->setStatus($status);
                $post->setUpdatedDate($updatedDate);
                $post->setSlug($slug);

                 $em = $this->getDoctrine()->getManager();

                $em->flush();
                
                $this->addFlash('notice','Post Updated');

                return $this->redirectToRoute('posts');
            }

        return $this->render('posts/edit.html.twig',array(
            'post' => $post,
            'form' => $form->createView()
        ));
    }

     /**
     * @Route("/posts/delete/{id}", name="post-delete")
     */
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository('AppBundle:Post')->find($id);

        $em->remove($post);
        $em->flush();

         $this->addFlash('notice','Post Deleted');

        return $this->redirectToRoute('posts');
    }
}
