<?php

namespace AppBundle\Controller

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminController extends Controller
{
/**
     * @Route(path="/admin", name="admin_home")
     */
    public function homeAction()
    {
        return $this->render('admin_home.html.twig');
    }
    /**
     * @Route(path="/admin/posts", name="admin_posts")
     */
}