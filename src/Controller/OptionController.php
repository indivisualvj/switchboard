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

#[Route(path: '/option')]
class OptionController extends AbstractController
{
    public function __construct(
        private readonly string $kernelProjectDir,
    )
    {
    }

    #[Route(path: '/load-inputs')]
    public function loadInputs(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('inputs.yaml'),
        ]);
    }

    #[Route(path: '/load-rules')]
    public function loadRules(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('rules.yaml'),
        ]);
    }

    #[Route(path: '/load-outputs')]
    public function loadOutputs(): JsonResponse
    {
        return new JsonResponse([
            'yaml' => $this->load('outputs.yaml'),
        ]);
    }

    #[Route(path: '/save-inputs')]
    public function saveInputs(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('inputs.yaml', $contents);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/save-outputs')]
    public function saveOutputs(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('outputs.yaml', $contents);

        return new JsonResponse([
            'success' => true,
        ]);
    }

    #[Route(path: '/save-rules')]
    public function saveRules(Request $request): JsonResponse
    {
        $contents = $request->get('contents');
        $this->save('rules.yaml', $contents);

        return new JsonResponse([
            'success' => true,
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
