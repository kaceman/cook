<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Post;
use AppBundle\Entity\Category;
use AppBundle\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();

        return $this->render('default/index.html.twig', array(
            'posts' => $posts
        ));
    }

    public function saveAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Category::class);
        $categories = $repository->findAll();

        $post = new Post();

        $form = $this->createFormBuilder($post)
            ->add('title', TextType::class)
            ->add('content', TextType::class)
            ->add('category', ChoiceType::class, [
                'choices' => $categories,
                'choice_label' => function($category, $key, $value) {
                    return strtoupper($category->getName());
                },
            ])
            ->add('save', SubmitType::class, array(
                'label' => 'Create a Recipe'
            ))
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post = $form->getData();
            $post->setDateCreation(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->redirectToRoute('home_page');
        }

        return $this->render('default/new.html.twig', array(
            'form' => $form->createView()
        ));
    }
    
    public function showAction($id, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $post = $repository->find($id);

        $form = $this->createFormBuilder(new Comment())
            ->add('content', TextType::class)
            ->add('save', SubmitType::class, array(
                'label' => 'Add'
            ))
            ->getForm();
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $form->getData();



            $comment->setPost($post);
            $comment->setDateCreation(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();

            return $this->redirectToRoute('show_post', array('id' => $post->getId()));
        }

        return $this->render('default/show.html.twig', array(
            'form' => $form->createView(),
            'post' => $post
        ));
    }
}
