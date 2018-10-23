<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        return $this->render('home/index.html.twig', []);
    }
    
    /**
     * @Route("/login", name="login")
     */
    public function login()
    {
        return $this->render('home/login.html.twig', []);
    }
    
    /**
     * @Route("/estimez-votre-pollution", name="estimate_pollution")
     */
    public function estimatePollution()
    {
        return $this->render('home/estimate_pollution.html.twig', []);
    }
}
