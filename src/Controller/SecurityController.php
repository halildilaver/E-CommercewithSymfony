<?php

namespace App\Controller;

use App\Entity\Admin\User;
use App\Repository\Admin\SettingRepository;
use App\Repository\ShopcartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils,SettingRepository $settingRepository,ShopcartRepository $shopcartRepository): Response
    {
        $user =$this->getUser();
        $total= 0;
        $count= 0;
        $userrol = false;
        if($user!=null){
            $userrole= $user->getRoles();
            if( strpos($userrole[0], 'ROLE_ADMIN') !== false)
                $userrol = true;
            $userid = $user->getid();
            $total= $shopcartRepository->getUserShopCartTotal($userid);
            $count= $shopcartRepository->getUserShopCartCount($userid);
        }
        $data= $settingRepository->findAll();
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();



        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'data' => $data,
            'total' => $total,
            'user' => $user,
            'userrol' => $userrol,
            'count' => $count,
            'error' => $error]);
    }

    /**
     * @Route("/loginerror", name="app_login_error")
     */
    public function loginerror(AuthenticationUtils $authenticationUtils,SettingRepository $settingRepository,ShopcartRepository $shopcartRepository): Response
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

        $this->addFlash('error','Girmeye çalıştığınız yere erişim hakkınız yok!');
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'data' => $data,
            'total' => $total,
            'count' => $count,
            'error' => $error]);
    }
}
