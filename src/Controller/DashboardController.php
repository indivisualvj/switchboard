<?php

namespace App\Controller;

use App\SubRoutine\RunSubRoutine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly string $kernelProjectDir,
    )
    {
    }

    #[Route(path: '/')]
    public function index(): Response
    {
        return $this->render('controller/dashboard/index.html.twig');
    }

    #[Route(path: '/dashboard/pv-log')]
    public function pvLog(): JsonResponse
    {
        $log = file_get_contents($this->kernelProjectDir . '/var/log/pv.log');
        $log = explode(str_repeat('_', RunSubRoutine::LINE_LENGTH), $log);
        $pop = array_pop($log);
        $pop = array_pop($log);

        return new JsonResponse([
            'log' => $pop,
        ]);
    }
}
