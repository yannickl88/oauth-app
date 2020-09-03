<?php
declare(strict_types=1);

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController
{
    /**
     * @Route("/", name="app.index")
     * @Template("index.html.twig")
     */
    public function indexAction(): array
    {
        return [];
    }
}