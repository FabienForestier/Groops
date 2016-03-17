<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserController extends Controller
{
    /**
     * @Route("/users", name="users")
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->findAll();
        
        return $this->render('users/index.html.twig',array('users' => $users));
    }

    /**
     * @Route("/users/detail/{id}", name="user-detail")
     */
    public function detailAction($id)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);
        return $this->render('users/detail.html.twig',array('user' => $user));
    }

     /**
     * @Route("/users/create", name="user-create")
     */
    public function createAction(Request $request)
    {
        $user = new User;

        $form = $this->createFormBuilder($user)
            ->add('name',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('email',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('password',PasswordType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('profileImage',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('bio',TextareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('submit',SubmitType::class, array('attr' => array('class' => 'btn btn-success', 'style' => 'margin-bottom:15px')))
            ->getForm();

            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //Get data
                $name = $form['name']->getData();
                $email = $form['email']->getData();
                $password = $form['password']->getData();
                $profileImage = $form['profileImage']->getData();
                $bio = $form['bio']->getData();

                $user->setName($name);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setProfileImage($profileImage);
                $user->setBio($bio);

                $em = $this->getDoctrine()->getManager();

                $em->persist($user);
                $em->flush();

                $this->addFlash('notice','User Created');

                return $this->redirectToRoute('users');
            }

        return $this->render('users/create.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/users/edit/{id}", name="user-edit")
     */
    public function editAction($id, Request $request)
    {
        $user = $this->getDoctrine()
            ->getRepository('AppBundle:User')
            ->find($id);

        $form = $this->createFormBuilder($user)
            ->add('name',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('email',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            //->add('password',PasswordType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('profileImage',TextType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('bio',TextareaType::class, array('attr' => array('class' => 'form-control', 'style' => 'margin-bottom:15px')))
            ->add('submit',SubmitType::class, array('attr' => array('class' => 'btn btn-success', 'style' => 'margin-bottom:15px')))
            ->getForm();


            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid())
            {
                //Get data
                $name = $form['name']->getData();
                $email = $form['email']->getData();
                //$password = $form['password']->getData();
                $profileImage = $form['profileImage']->getData();
                $bio = $form['bio']->getData();

                $user->setName($name);
                $user->setEmail($email);
                //$user->setPassword($password);
                $user->setProfileImage($profileImage);
                $user->setBio($bio);

                $em = $this->getDoctrine()->getManager();

                $em->flush();

                $this->addFlash('notice','User Updated');

                return $this->redirectToRoute('users');
            }

        return $this->render('users/edit.html.twig',array(
            'user' => $user,
            'form' => $form->createView()
        ));
    }

     /**
     * @Route("/users/delete/{id}", name="user-delete")
     */
    public function deleteAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($id);

        $em->remove($user);
        $em->flush();

         $this->addFlash('notice','User Deleted');

        return $this->redirectToRoute('users');
    }
}
