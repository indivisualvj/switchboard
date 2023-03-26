<?php

namespace App\Controller;

use App\SubRoutine\RunSubRoutine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
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
        $log = explode(str_repeat('µ', RunSubRoutine::LINE_LENGTH), $log);
        $log = array_pop($log);

        return new JsonResponse([
            'log' => $log,
        ]);
    }


    #[Route(path: '/dashboard/sys-log')]
    public function sysLog(): JsonResponse
    {
        $process = Process::fromShellCommandline(sprintf('tail -n 100 %s/var/log/dev.log', $this->kernelProjectDir));
        $process->run();
        $process->wait();

        return new JsonResponse([
            'log' => $process->getOutput(),
        ]);
    }
}
