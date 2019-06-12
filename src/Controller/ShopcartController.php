<?php

namespace App\Controller;

use App\Entity\Shopcart;
use App\Form\ShopcartType;
use App\Repository\Admin\SettingRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/shopcart")
 */
class ShopcartController extends AbstractController
{
    /**
     * @Route("/", name="shopcart_index", methods={"GET"})
     */
    public function index(ShopcartRepository $shopcartRepository,SettingRepository $settingRepository): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user =$this->getUser();
        $userid = $user->getid();
        $total= $shopcartRepository->getUserShopCartTotal($userid);
        $count= $shopcartRepository->getUserShopCartCount($userid);
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT p.title,p.sprice, s.*
                FROM shopcart s,product p 
                WHERE s.productid = p.id and userid=:userid";

        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('userid',$user->getid());
        $statement->execute();
        $shopcart = $statement->fetchAll();



        $data= $settingRepository->findAll();
        return $this->render('shopcart/index.html.twig', [
            'shopcarts' => $shopcart,
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/new", name="shopcart_new", methods={"GET","POST"})
     */
    public function new(Request $request,ShopcartRepository $shopcartRepository): Response
    {
        $shopcart = new Shopcart();
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);

        echo $submittedToken = $request->request->get('token');
       // if($this->isCsrfTokenValid('add-item',$submittedToken)){
            if ($form->isSubmitted()) {
                $entityManager = $this->getDoctrine()->getManager();
                $user = $this->getUser();


                $shopcart->setUserid($user->getid());

                $entityManager->persist($shopcart);
                $entityManager->flush();

                return $this->redirectToRoute('shopcart_index');
            }
       // }
        return $this->render('shopcart/new.html.twig', [
            'shopcart' => $shopcart,
            'total' => $total,
            'count' => $count,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="shopcart_show", methods={"GET"})
     */
    public function show(Shopcart $shopcart): Response
    {
        return $this->render('shopcart/show.html.twig', ['shopcart' => $shopcart]);
    }

    /**
     * @Route("/{id}/edit", name="shopcart_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Shopcart $shopcart,ShopcartRepository $shopcartRepository): Response
    {
        $form = $this->createForm(ShopcartType::class, $shopcart);
        $form->handleRequest($request);
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('shopcart_index', ['id' => $shopcart->getId()]);
        }

        return $this->render('shopcart/edit.html.twig', [
            'shopcart' => $shopcart,
            'total' => $total,
            'count' => $count,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="shopcart_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Shopcart $shopcart): Response
    {
        if ($this->isCsrfTokenValid('delete'.$shopcart->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($shopcart);
            $entityManager->flush();
            $this->addFlash('success','Ürün sepetten silindi');
        }

        return $this->redirectToRoute('shopcart_index');

    }
}
