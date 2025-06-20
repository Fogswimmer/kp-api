<?php

namespace App\Controller;

use App\Service\VisitorCounterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VisitController extends AbstractController
{
    public function __construct(private VisitorCounterService $counter)
    {
    }
    #[Route('/api/visit', name: 'app_visit')]
    public function visit(Request $request): Response
    {
        $ip = $request->getClientIp();
        $newCount = $this->counter->increment();

        return $this->json([
            'visits' => $newCount,
            'ip' => $ip,
        ]);
    }
}
