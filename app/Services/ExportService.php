<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    public function exportCsv(array $data, string $filename = 'report.csv'): StreamedResponse
    {
        $rows = $this->flattenForExport($data);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            if (! empty($rows)) {
                fputcsv($handle, array_keys($rows[0]));
                foreach ($rows as $row) {
                    fputcsv($handle, array_map(function ($value) {
                        if (is_array($value)) {
                            return json_encode($value);
                        }
                        if (is_bool($value)) {
                            return $value ? 'true' : 'false';
                        }

                        return (string) ($value ?? '');
                    }, $row));
                }
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function exportPdf(array $data, string $filename = 'report.html'): Response
    {
        $html = $this->generateHtml($data);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    private function flattenForExport(array $data): array
    {
        $rows = [];

        $sectionKeys = ['overview', 'tables'];

        foreach ($sectionKeys as $section) {
            if (! isset($data[$section])) {
                continue;
            }

            $sectionData = $data[$section];

            if (is_array($sectionData)) {
                $rows[] = ['section' => $section, 'key' => '', 'value' => ''];
                $rows[] = ['section' => $section, 'key' => '---', 'value' => '---'];

                $this->flattenArray($sectionData, $section, $rows);
            }
        }

        if (isset($data['charts'])) {
            $rows[] = ['section' => 'charts', 'key' => '', 'value' => ''];
            $rows[] = ['section' => 'charts', 'key' => '---', 'value' => '---'];

            foreach ($data['charts'] as $chartKey => $chartData) {
                if ($chartData instanceof Collection) {
                    $chartData = $chartData->toArray();
                }
                if (is_array($chartData)) {
                    $rows[] = ['section' => 'charts', 'key' => $chartKey, 'value' => json_encode($chartData)];
                }
            }
        }

        return $rows;
    }

    private function flattenArray(array $data, string $prefix, array &$rows, string $parentKey = ''): void
    {
        foreach ($data as $key => $value) {
            $fullKey = $parentKey ? $parentKey.'.'.$key : $key;

            if (is_array($value) && isset($value[0]) && is_array($value[0])) {
                foreach ($value as $index => $item) {
                    $rows[] = ['section' => $prefix, 'key' => $fullKey.'['.$index.']', 'value' => json_encode($item)];
                }
            } elseif (is_array($value)) {
                if ($this->isFlatArray($value)) {
                    $rows[] = ['section' => $prefix, 'key' => $fullKey, 'value' => json_encode($value)];
                } else {
                    $this->flattenArray($value, $prefix, $rows, $fullKey);
                }
            } elseif ($value instanceof Collection) {
                $rows[] = ['section' => $prefix, 'key' => $fullKey, 'value' => json_encode($value->toArray())];
            } else {
                $rows[] = ['section' => $prefix, 'key' => $fullKey, 'value' => (string) ($value ?? '')];
            }
        }
    }

    private function isFlatArray(array $array): bool
    {
        foreach ($array as $value) {
            if (is_array($value) || $value instanceof Collection) {
                return false;
            }
        }

        return true;
    }

    private function generateHtml(array $data): string
    {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
        $html .= '<title>Dashboard Report</title>';
        $html .= '<style>
            body { font-family: DejaVu Sans, sans-serif; padding: 30px; color: #333; }
            h1 { color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 10px; }
            h2 { color: #374151; margin-top: 30px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 13px; }
            th { background: #1a56db; color: white; padding: 10px 12px; text-align: left; }
            td { padding: 8px 12px; border-bottom: 1px solid #e5e7eb; }
            tr:nth-child(even) { background: #f9fafb; }
            .card-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin: 20px 0; }
            .card { background: #f3f4f6; padding: 15px; border-radius: 8px; text-align: center; }
            .card-value { font-size: 24px; font-weight: bold; color: #1a56db; }
            .card-label { font-size: 12px; color: #6b7280; margin-top: 5px; }
            .meta { color: #9ca3af; font-size: 12px; margin-bottom: 20px; }
        </style></head><body>';
        $html .= '<h1>Platform Dashboard Report</h1>';
        $html .= '<div class="meta">Generated: '.now()->toIso8601String().'</div>';

        if (! empty($data['filters_applied'])) {
            $activeFilters = array_filter($data['filters_applied']);
            if (! empty($activeFilters)) {
                $html .= '<p><strong>Filters:</strong> '.json_encode($activeFilters).'</p>';
            }
        }

        if (! empty($data['overview'])) {
            $html .= '<h2>Overview</h2><div class="card-grid">';
            foreach ($data['overview'] as $key => $value) {
                $label = str_replace('_', ' ', ucwords($key, '_'));
                $html .= '<div class="card"><div class="card-value">'.e((string) $value).'</div><div class="card-label">'.e($label).'</div></div>';
            }
            $html .= '</div>';
        }

        if (! empty($data['tables'])) {
            foreach ($data['tables'] as $tableKey => $tableData) {
                if (empty($tableData)) {
                    continue;
                }

                $label = str_replace('_', ' ', ucwords($tableKey, '_'));
                $html .= '<h2>'.e($label).'</h2>';

                $items = is_array($tableData) ? $tableData : ($tableData instanceof Collection ? $tableData->toArray() : []);
                if (empty($items)) {
                    $html .= '<p>No data available.</p>';

                    continue;
                }

                $headers = array_keys((array) $items[0]);
                $html .= '<table><thead><tr>';
                foreach ($headers as $header) {
                    $html .= '<th>'.e(ucwords(str_replace('_', ' ', $header))).'</th>';
                }
                $html .= '</tr></thead><tbody>';

                foreach ($items as $item) {
                    $html .= '<tr>';
                    foreach ($headers as $header) {
                        $value = $item[$header] ?? '';
                        if (is_array($value)) {
                            $display = json_encode($value);
                        } elseif ($value instanceof \UnitEnum) {
                            $display = $value->value;
                        } elseif (is_bool($value)) {
                            $display = $value ? 'Yes' : 'No';
                        } elseif ($value instanceof Carbon) {
                            $display = $value->toIso8601String();
                        } else {
                            $display = (string) ($value ?? '');
                        }
                        $html .= '<td>'.e($display).'</td>';
                    }
                    $html .= '</tr>';
                }

                $html .= '</tbody></table>';
            }
        }

        if (! empty($data['charts'])) {
            $html .= '<h2>Charts Data</h2>';
            foreach ($data['charts'] as $chartKey => $chartData) {
                $label = str_replace('_', ' ', ucwords($chartKey, '_'));
                $items = $chartData instanceof Collection ? $chartData->toArray() : ($chartData ?? []);
                $html .= '<h3>'.e($label).'</h3>';
                if (! empty($items)) {
                    $html .= '<table><thead><tr><th>Label</th><th>Value</th></tr></thead><tbody>';
                    foreach ($items as $item) {
                        $item = (array) $item;
                        $html .= '<tr><td>'.e($item['label'] ?? '').'</td><td>'.e((string) ($item['value'] ?? '')).'</td></tr>';
                    }
                    $html .= '</tbody></table>';
                } else {
                    $html .= '<p>No data available.</p>';
                }
            }
        }

        $html .= '</body></html>';

        return $html;
    }
}
