<?php

namespace App\Controller;

use App\Util\StringUtil;
use Exception;
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
        try {
            $log = file_get_contents($this->kernelProjectDir . '/var/log/pv.log');
            $log = explode(str_repeat('µ', StringUtil::LINE_LENGTH), $log);
            $log = array_pop($log);
        } catch (Exception $e) {
            $log = $e->getMessage();
        }

        return new JsonResponse([
            'log' => $log,
        ]);
    }

    #[Route(path: '/dashboard/sys-info')]
    public function sysInfo(): JsonResponse
    {
        $process = Process::fromShellCommandline('free -m');
        $process->run();
        $process->wait();

        return new JsonResponse([
            'log' => $process->getOutput(),
        ]);
    }

    #[Route(path: '/dashboard/check-service')]
    public function checkService(): JsonResponse
    {
        $filename = $this->kernelProjectDir . '/running';
        file_put_contents($filename, '0');

        $timeout = 20;
        $running = 0;

        while ($timeout > 0 && !($running = file_get_contents($filename))) {
            sleep(2);
            $timeout -= 2;
        }

        return new JsonResponse([
            'success' => (bool)$running,
            'message' => $running ? '&#129321;' : '&#128565;',
        ]);
    }
}
