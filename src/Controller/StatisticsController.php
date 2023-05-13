<?php

namespace App\Controller;

use App\Manager\StatisticsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

#[Route(path: '/stats')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsManager $statisticsManager,
        private readonly Serializer $serializer,
    )
    {
    }

    #[Route(path: '/table/{interval}', defaults: ['interval' => 60])]
    public function table(int $interval): JsonResponse
    {
        $tables = [];

        $tables[] = $this->createTable('inputs', $this->statisticsManager->getInputStatistics($interval));
        $tables[] = $this->createTable('outputs (%)', $this->statisticsManager->getOutputStatistics($interval));

        $table = $this->renderView('controller/dashboard/component/tables.html.twig', [
            'tables' => $tables,
        ]);

        return new JsonResponse([
            'log' => $table,
        ]);
    }

    #[Route(path: '/plot')]
    public function plot(): JsonResponse
    {
        $values = [];
        $values['inputs'] = $this->statisticsManager->getInputValues(1440);
        $values['outputs'] = $this->statisticsManager->getOutputValues(1440);

        $values = $this->serializer->serialize($values, 'json', ['plot']);

        return new JsonResponse($values, 200, [], true);
    }

    private function createTable(string $title, array $stats): array
    {
        $body = [];
        foreach ($stats as $key => $value) {
            $body[] = [$key, $value];
        }

        return [
            'title' => $title,
            'body' => $body,
        ];
    }

}
