<?php

namespace App\Controller;

use App\Manager\StatisticsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/stats')]
class StatisticsController extends AbstractController
{
    public function __construct(
        private readonly StatisticsManager $statisticsManager,
    )
    {
    }

    #[Route(path: '/table')]
    public function table(): JsonResponse
    {
        $tables = [];

        $tables[] = $this->createTable('inputs (1h)', $this->statisticsManager->getInputStatistics(60));
        $tables[] = $this->createTable('rules (1h)', $this->statisticsManager->getRuleStatistics(60));
        $tables[] = $this->createTable('outputs (1h)', $this->statisticsManager->getOutputStatistics(60));

        $table = $this->renderView('controller/dashboard/component/tables.html.twig', [
            'tables' => $tables,
        ]);

        return new JsonResponse([
            'log' => $table,
        ]);
    }

    private function createTable(string $title, array $stats): array
    {
        $head = array_keys($stats);
        $body = [array_values($stats)];

        return [
            'title' => $title,
            'head' => $head,
            'body' => $body,
        ];
    }

}
