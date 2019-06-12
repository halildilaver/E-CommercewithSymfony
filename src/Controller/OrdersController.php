<?php

namespace App\Controller;

use App\Entity\OrderDetail;
use App\Entity\Orders;
use App\Form\OrdersType;
use App\Repository\Admin\SettingRepository;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="orders_index", methods={"GET"})
     */
    public function index(OrdersRepository $ordersRepository,SettingRepository $settingRepository,ShopcartRepository $shopcartRepository): Response
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
        $user = $this->getUser();
        $userid = $user->getid();
        return $this->render('orders/index.html.twig', [
            'orders' => $ordersRepository
                ->findBy(['userid'=> $userid]),
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/new", name="orders_new", methods={"GET","POST"})
     */
    public function new(Request $request, ShopcartRepository $shopcartRepository, SettingRepository $settingRepository): Response
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $order = new Orders();
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        $data= $settingRepository->findAll();
        $user = $this->getUser();
        $userid = $user->getid();
        $total= $shopcartRepository->getUserShopCartTotal($userid);

        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('form-order', $submittedToken)){
            if ($form->isSubmitted()) {
                $entityManager = $this->getDoctrine()->getManager();

                $order->getUserid($userid);
                $order->setAmount($total);
                $order->setStatus('New');

                $entityManager->persist($order);
                $entityManager->flush();

                $orderid = $order->getId();
                $shopcart = $shopcartRepository->getUserShopCart($userid);

                foreach ($shopcart as $item){
                    $orderdetail = new OrderDetail();
                    $orderdetail->setOrderid($orderid);
                    $orderdetail->setUserid($userid);
                    $orderdetail->setProductid($item["productid"]);
                    $orderdetail->setPrice($item["sprice"]);
                    $orderdetail->setQuantity($item["quantity"]);
                    $orderdetail->setAmount($item["total"]);
                    $orderdetail->setName($item["title"]);
                    $orderdetail->setStatus("Ordered");

                    $entityManager->persist($orderdetail);
                    $entityManager->flush();
                }

                $entityManager = $this->getDoctrine()->getManager();
                $query = $entityManager->createQuery('
                    DELETE FROM App\Entity\Shopcart s WHERE s.userid=:userid
                ')
                    ->setParameter('userid',$userid);

                $query->execute();
                $this->addFlash('success','Siparişiniz Başarayıla Gerçekleştirilmiştir!');
                return $this->redirectToRoute('orders_index');
            }
        }

        return $this->render('orders/new.html.twig', [
            'order' => $order,
            'total' => $total,
            'data' => $data,
            'count' => $count,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orders_show", methods={"GET"})
     */
    public function show(Orders $order,SettingRepository $settingRepository, OrderDetailRepository $orderDetailRepository,ShopcartRepository $shopcartRepository): Response
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
        $user = $this->getUser();
        $userid = $user->getid();
        $orderid = $order->getId();
        $orderdetail = $orderDetailRepository->findBy(
            ['orderid' => $orderid]
        );

        return $this->render('orders/show.html.twig', ['order' => $order,'data' => $data,'total' => $total,
            'count' => $count,'orderdetail'=> $orderdetail]);
    }

    /**
     * @Route("/{id}/edit", name="orders_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Orders $order,ShopcartRepository $shopcartRepository): Response
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('orders_index', ['id' => $order->getId()]);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'total' => $total,
            'count' => $count,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orders_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Orders $order): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('orders_index');
    }
}
