<?php

namespace App\Controller\Userpanel;

use App\Entity\Admin\User;
use App\Entity\Admin\Messages;
use App\Entity\Comments;
use App\Form\Admin\MessagesType;
use App\Repository\Admin\MessagesRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\Admin\UserRepository;
use App\Repository\CommentsRepository;
use App\Repository\ShopcartRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
* @Route("/userpanel")
*/
class UserpanelController extends AbstractController
{
    /**
     * @Route("/", name="userpanel")
     */
    public function index()
    {
        return $this->redirectToRoute('userpanel_show');
    }

    /**
     * @Route("/edit", name="userpanel_edit", methods="GET|POST")
     */
    public function edit(Request $request,SettingRepository $settingRepository,ShopcartRepository $shopcartRepository): Response
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $usersession = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($usersession->getid());
        if($request->isMethod('POST')){
            $submittedToken = $request->request->get('token');
            if ($this->isCsrfTokenValid('user-form',$submittedToken)){
                $user->setName($request->request->get("name"));
                $user->setAddress($request->request->get("address"));
                $user->setPhone($request->request->get("phone"));
                $user->setCity($request->request->get("city"));
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success','Üye Bilgileriniz Başarı ile Güncellendi.');
                return $this->redirectToRoute('userpanel_show');
            }
        }
        return $this->render('userpanel/edit.html.twig',[
            'user' => $user,
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/show", name="userpanel_show", methods="GET|POST")
     */
    public function show(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        return $this->render('userpanel/show.html.twig',[
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/ticket", name="userpanel_ticket", methods="GET|POST")
     */
    public function ticket(SettingRepository $settingRepository,Request $request,ShopcartRepository $shopcartRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash('success','Mesajınız Gönderildi');
            return $this->redirectToRoute('userpanel_show');
        }

        return $this->render('userpanel/ticket.html.twig',[
            'data' => $data,
            'total' => $total,
            'message' => $message,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/ticket/show", name="userpanel_ticket_show", methods="GET|POST")
     */
    public function ticketshow(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository,MessagesRepository $messagesRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $userid = $user->getid();


        return $this->render('userpanel/ticketshow.html.twig',[
            'data' => $data,
            'messages' => $messagesRepository
                ->findBy(['userid'=> $userid]),
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/ticket/showdetail/{id}", name="userpanel_ticket_show_detail", methods="GET|POST")
     */
    public function ticketshowdetail(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository,Messages $messages)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $userid = $user->getid();

        return $this->render('userpanel/ticketshowdetail.html.twig',[
            'data' => $data,
            'messages' => $messages,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/yorumlarim", name="userpanel_yorumlarim", methods="GET|POST")
     */
    public function yorumlarim(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository,CommentsRepository $commentsRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $userid = $user->getid();

        return $this->render('userpanel/yorumlarim.html.twig',[
            'data' => $data,
            'comments' => $commentsRepository
                ->findBy(['userid'=> $userid]),
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("yorumlarim/{id}", name="comments_delete_u", methods={"DELETE"})
     */
    public function delete(Request $request, Comments $comment): Response
    {
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($comment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('userpanel_yorumlarim');
    }


}
