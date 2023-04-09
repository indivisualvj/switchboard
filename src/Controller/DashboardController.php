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

    #[Route(path: '/dashboard/sys-info')]
    public function sysInfo(): JsonResponse
    {
        $process = Process::fromShellCommandline('free -h');
        $process->run();
        $process->wait();
        $rows = preg_split('/\n/', $process->getOutput());
        $columns = preg_split('/ +/', $rows[0]);
        $head = array_slice($columns, 0, 4);
        $body = [];
        for ($i = 1; $i < count($rows); $i++) {
            $row = preg_split('/ +/', $rows[$i]);
            if (count($row)) {
                $body[] = array_slice($row, 0, 4);
            }
        }

        $table = $this->renderView('controller/dashboard/component/table.html.twig', [
            'table' => [
                'head' => $head,
                'body' => $body,
            ],
        ]);

        return new JsonResponse([
            'log' => $table,
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

    #[Route(path: '/dashboard/watch-log')]
    public function watchLog(): JsonResponse
    {
        try {
            $tables = [];
            $time = 'log is empty';

            $log = trim(file_get_contents($this->kernelProjectDir . '/var/log/watch.log'));
            if ($log) {
                $log = explode(str_repeat('µ', StringUtil::LINE_LENGTH), $log);
                $log = array_pop($log);
                $rows = explode(str_repeat('_', StringUtil::LINE_LENGTH), $log);

                if (count($rows)) {
                    try {
                        $time = preg_replace('/\n@+ ([^@]+) @+\n/', '$1', $rows[0]);
                        $tables[] = $this->createInputsTable($rows[1]);
                        $tables[] = $this->createRulesTable($rows[2]);
                    } catch (Exception $exception) {
                        $tables[] = [
                            'title' => $exception,
                            'body' => [[$log]],
                        ];
                    }
                }
            }

            $html = $this->renderView('controller/dashboard/component/tables.html.twig', [
                'title' => $time,
                'tables' => $tables,
            ]);

        } catch (Exception $e) {
            $html = $e->getMessage();
        }

        return new JsonResponse([
            'log' => $html,
        ]);
    }

    private function createInputsTable(string $row): array
    {
        try {
            $inputs = preg_split('/\n/', preg_replace('/\|+ ([^\|]+) \|+/', '$1', $row));
            $body = [];
            for ($i = 2; $i < count($inputs); $i++) {
                $body[] = array_slice(preg_split('/(input "|" is: )/', $inputs[$i]), 1, 2);
            }

            return [
                'title' => $inputs[1],
                'body' => $body,
            ];
        } catch (Exception $ex) {
            return [
                'title' => $ex->getMessage(),
                'body' => [[$row]],
            ];
        }
    }

    private function createRuleTable(string $rule): array
    {
        try {
            $rule = preg_split('/\n/', $rule);
            $header = explode(': ', $rule[0]);
            for ($ri = 1; $ri < count($rule); $ri++) {
                $row = $rule[$ri];
                if ('' === $row) {
                    $cells = [' ', ' '];
                } else {
                    $cells = explode(': ', $row);
                    array_unshift($cells);
                }
                $body[] = $cells;
            }

            return [
                'head' => $header,
                'body' => $body,
            ];
        } catch (Exception $ex) {
            return [
                'title' => $ex->getMessage(),
                'body' => [[$rule]],
            ];
        }
    }

    private function createRulesTable(string $rules): array
    {
        try {
            $caption =  preg_replace('/\n\|+ (.+) \|+.*/s', '$1', $rules);
            $table = [
                'title' => $caption,
                'body' => [],
            ];

            $rules = preg_replace('/[^\n]+\n(.+)/s', '$1', $rules);
            $rules = explode(str_repeat('-', StringUtil::LINE_LENGTH), $rules);
            for ($i = 0; $i < count($rules); $i++) {
                $rule = trim($rules[$i]);
                if ('' !== $rule) {
                    $table['body'][] = [$this->createRuleTable($rule)];
                }
            }
        } catch (Exception $ex) {
            $table = [
                'title' => $ex->getMessage(),
                'body' => [[$rules]],
            ];
        }

        return $table;
    }
}
