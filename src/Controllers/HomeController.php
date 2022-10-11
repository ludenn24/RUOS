<?php

namespace App\Controllers;

use Slim\Views\Twig as View;

Class  HomeController extends Controller
{

    public function index($request, $response)
    {
        return $this->view->render($response, 'home.twig');
    }
	
   public function getViewMapa($request, $response)
    {
        return $this->view->render($response, 'mapa.twig');
    }
	
	public function getViewMapaCultural($request, $response)
    {
        return $this->view->render($response, 'mapa-cultural.twig');
    }

    public function getAlmacenamiento($request, $response)
    {
        return $this->view->render($response, 'auth/almacenamiento.twig');

    }


}
