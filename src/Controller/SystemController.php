<?php

namespace App\Controller;

use App\SubRoutine\RunSubRoutine;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/system')]
class SystemController extends AbstractController
{
    public function __construct(
        private readonly string $kernelProjectDir,
    )
    {
    }

    #[Route(path: '/restart')]
    public function restart(): JsonResponse
    {
        $filename = $this->kernelProjectDir . '/restart';
        file_put_contents($filename, '1');
        sleep(1);
        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/idle')]
    public function idle(): JsonResponse
    {
        $filename = $this->kernelProjectDir . '/idle';
        file_put_contents($filename, '1');
        sleep(1);
        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/stop')]
    public function stop(): JsonResponse
    {
        $filename = $this->kernelProjectDir . '/stop';
        file_put_contents($filename, '1');
        sleep(1);
        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/reboot')]
    public function reboot(): void
    {
        $process = Process::fromShellCommandline('sudo reboot');
        $process->start();
    }
}
