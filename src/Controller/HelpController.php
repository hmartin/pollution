<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route({
 *     "en": "/help-us",
 *     "fr": "/aidez-nous"
 * })
 */
class HelpController extends AbstractController
{
    /**
     * @Route("/", name="help")
     */
    public function index()
    {
        return $this->render('help/index.html.twig', []);
    }
}
