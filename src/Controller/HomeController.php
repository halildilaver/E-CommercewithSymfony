<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\Admin\User;
use App\Form\Admin\MessagesType;
use App\Form\Admin\UserType;
use App\Entity\Admin\Product;
use App\Repository\Admin\MessagesRepository;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\Admin\ImageRepository;
use App\Repository\CommentsRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(SettingRepository $settingRepository, CategoryRepository $categoryRepository,AuthenticationUtils $authenticationUtils, ImageRepository $imageRepository,ShopcartRepository $shopcartRepository)
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
            // get the login error if there is one
            $error = $authenticationUtils->getLastAuthenticationError();
            // last username entered by the user
            $lastUsername = $authenticationUtils->getLastUsername();

            $cats= $this->fetchCategoryTreeList();
            $cats[0]='<ul id="menu-v">';

            $em = $this->getDoctrine()->getManager();
            $sql = "SELECT * FROM product WHERE status='TRUE' ORDER BY ID DESC LIMIT 10";
            $statement = $em->getConnection()->prepare($sql);
            $statement->execute();
            $sliders = $statement->fetchAll();

            $catid = rand(8,16);
            $emm = $this->getDoctrine()->getManager();
            $sqll = 'SELECT * FROM product WHERE status= "TRUE" AND category_id = :catid ORDER BY ID DESC LIMIT 3';
            $statementt = $emm->getConnection()->prepare($sqll);
            $statementt-> bindValue('catid', $catid);
            $statementt->execute();
            $products = $statementt->fetchAll();

            $catid2 = rand(8,16);
            $em2 = $this->getDoctrine()->getManager();
            $sql2 = 'SELECT * FROM product WHERE status= "TRUE" AND category_id = :catid ORDER BY ID DESC LIMIT 3';
            $statement2 = $em2->getConnection()->prepare($sql2);
            $statement2-> bindValue('catid', $catid2);
            $statement2->execute();
            $products2 = $statement2->fetchAll();


        return $this->render('home/index.html.twig', [
            'data' => $data,
            'cats' => $cats,
            'total' => $total,
            'count' => $count,
            'products' => $products,
            'products2' => $products2,
            'last_username' => $lastUsername,
            'error' => $error,
            'sliders' => $sliders,
        ]);
    }

    /**
     * @Route("/hakkimizda", name="hakkimizda")
     */
    public function hakkimizda(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository)
    {
        $data= $settingRepository->findAll();
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }

        return $this->render('home/hakkimizda.html.twig', [
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/referanslar", name="referanslar")
     */
    public function referanslar(SettingRepository $settingRepository,ShopcartRepository $shopcartRepository)
    {
        $data= $settingRepository->findAll();
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }

        return $this->render('home/referans.html.twig', [
            'data' => $data,
            'total' => $total,
            'count' => $count,
        ]);
    }

    /**
     * @Route("/iletisim", name="iletisim", methods="GET|POST")
     */
    public function iletisim(SettingRepository $settingRepository,Request $request,ShopcartRepository $shopcartRepository)
    {
        $message = new Messages();
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
            $this->addFlash('success','Mesajınız Gönderildi');
            return $this->redirectToRoute('iletisim');
        }


        $data= $settingRepository->findAll();

        return $this->render('home/iletisim.html.twig', [
            'data' => $data,
            'message' => $message,
            'total' => $total,
            'count' => $count,
        ]);
    }

    public function fetchCategoryTreeList($parent = 0, $user_tree_array = ''){

        if (!is_array($user_tree_array))
            $user_tree_array = array();

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM category WHERE status='TRUE' AND parentid =".$parent;
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();

        if (count($result) > 0) {
            $user_tree_array[] ="<ul>";
            foreach ($result as $row){
                if($parent==0){
                    $user_tree_array[]= "<li> <a>". $row['title']."</a>"; //ANA KATEGORİ İSE LİNK VERMEZ
                }
                else{
                    $user_tree_array[]= "<li> <a href='/category/".$row['id']."'>". $row['title']."</a>"; //ALT KATAGORİ LİNK VERİR
                }
                $user_tree_array = $this->fetchCategoryTreeList($row['id'], $user_tree_array);
            }
            $user_tree_array[]= "</li></ul>";
        }
        return $user_tree_array;
    }

    /**
     * @Route("/category/{catid}", name="category_products", methods="GET")
     */
    public function CategoryProducts($catid,SettingRepository $settingRepository,CategoryRepository $categoryRepository,ShopcartRepository $shopcartRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $cats= $this->fetchCategoryTreeList();
        $cats[0]='<ul id="menu-v">';
        $data= $settingRepository->findAll();
        $datacategory = $categoryRepository->findBy(
            ['id'=> $catid]
        );

        $em = $this->getDoctrine()->getManager();
        $sql = 'SELECT * FROM product WHERE status= "TRUE" AND category_id = :catid';
        $statement = $em->getConnection()->prepare($sql);
        $statement-> bindValue('catid', $catid);
        $statement->execute();
        $products = $statement->fetchAll();
       //dump($result);

        return $this->render('home/products.html.twig', [
            'data' => $data,
            'datacategory' => $datacategory,
            'products' => $products,
            'cats' => $cats,
            'total' => $total,
            'count' => $count,
        ]);

    }

    /**
     * @Route("/product/{id}", name="product_detail", methods="GET")
     */
    public function ProductDetail(SettingRepository $settingRepository,$id,ProductRepository $productRepository,ImageRepository $imageRepository,ShopcartRepository $shopcartRepository,CommentsRepository $commentsRepository)
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $dataset= $settingRepository->findAll();

        $data=$productRepository->findBy(
            ['id'=> $id]
        );
        //dump($id);
        //die();

        $enty = $this->getDoctrine()->getManager();
        $sqlcon = 'SELECT * FROM comments WHERE status= "Published" AND productid ='.$id;
        $statementtt = $enty->getConnection()->prepare($sqlcon);
        $statementtt-> bindValue('productid', $id);
        $statementtt->execute();
        $comments = $statementtt->fetchAll();


        $images=$imageRepository->findBy(
            ['product_id' => $id]
        );

        $cats= $this->fetchCategoryTreeList();
        $cats[0]='<ul id="menu-v">';


        return $this->render('home/product_detail.html.twig', [
            'data' => $dataset,
            'datapro' => $data,
            //'comments' => $commentsRepository
            //    ->findBy(['productid'=> $id]),
            'comments' => $comments,
            'images' => $images,
            'cats' => $cats,
            'total' => $total,
            'count' => $count,

        ]);
    }

    /**
     * @Route("/register", name="register", methods="GET|POST")
     */
    public function register(SettingRepository $settingRepository,Request $request,\App\Repository\Admin\UserRepository $userRepository,ShopcartRepository $shopcartRepository)
    {
        //dump($request);
        //die();
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        if($user!=null){
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        $user = new User();
        $form = $this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($this->isCsrfTokenValid('user-form', $submittedToken)){
            if($form->isSubmitted()){
                $emaildata = $userRepository->findBy(['email'=>$user->getEmail()]);
                if($emaildata==null){
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success','Üye Kaydınız Başarıyla Gerçekleştirildi');
                    return $this->redirectToRoute('app_login');
                }
                else{
                    $this->addFlash('error','Aynı E-mail adresi ile yanlızca bir üyelik alınabilir!');
                }
            }
        }

        return $this->render('home/register.html.twig', [
            'data' => $data,
            'user' => $user,
            'form' => $form->createView(),
            'total' => $total,
            'count' => $count,
        ]);
    }
}
