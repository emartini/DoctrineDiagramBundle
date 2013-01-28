<?php

namespace Warseph\Bundle\DoctrineDiagramBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Warseph\Bundle\DoctrineDiagramBundle\GraphViz\Generator;
use Warseph\Bundle\DoctrineDiagramBundle\GraphViz\Entity;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $generator = $this->container->get('warseph_doctrine_diagram.generator');
        $svg = $generator->generate();

        return $this->render('WarsephDoctrineDiagramBundle:Default:index.html.twig', array('content' => $svg));
    }
}
