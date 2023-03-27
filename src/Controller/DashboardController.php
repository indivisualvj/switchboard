<?php

namespace App\Controller;

use App\SubRoutine\RunSubRoutine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $process = Process::fromShellCommandline(sprintf('tail -n 25 %s/var/log/dev.log', $this->kernelProjectDir));
        $process->run();
        $process->wait();

        return new JsonResponse([
            'log' => $process->getOutput(),
        ]);
    }

    #[Route(path: '/dashboard/start-service')]
    public function startService(): JsonResponse
    {
        $process = Process::fromShellCommandline(sprintf('cd %s; bin/console watch 30 > var/log/pv.log', $this->kernelProjectDir));
        $process->start();
        sleep(2);

        return new JsonResponse([
            'success' => true,
            'message' => $process->getErrorOutput(),
        ]);
    }

    #[Route(path: '/dashboard/stop-service')]
    public function stopService(): JsonResponse
    {
        $filename = $this->kernelProjectDir . '/terminate';
        file_put_contents($filename, '1');

        $timeout = 20;
        $terminate = 0;

        while ($timeout > 0 && ($terminate = file_get_contents($filename))) {
            sleep(2);
            $timeout -= 2;
        }

        return new JsonResponse([
            'success' => !$terminate,
        ]);
    }

    #[Route(path: '/dashboard/load-inputs')]
    public function loadInputs(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('inputs.yaml'),
        ]);
    }

    #[Route(path: '/dashboard/load-rules')]
    public function loadRules(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('rules.yaml'),
        ]);
    }

    #[Route(path: '/dashboard/load-outputs')]
    public function loadOutputs(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('outputs.yaml'),
        ]);
    }

    #[Route(path: '/dashboard/save-inputs')]
    public function saveInputs(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('inputs.yaml', $contents);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/dashboard/save-outputs')]
    public function saveOutputs(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('outputs.yaml', $contents);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/dashboard/save-rules')]
    public function saveRules(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('rules.yaml', $contents);

        return new JsonResponse([
            'success' => true,
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
            'message' => $running ? 'Running' : 'Offline',
        ]);
    }

    private function save($file, $contents): void
    {
        file_put_contents($this->kernelProjectDir . '/' . $file, $contents);
    }

    private function load($file): string
    {
        return file_get_contents($this->kernelProjectDir . '/' . $file);
    }
}
