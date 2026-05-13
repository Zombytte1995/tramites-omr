<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TramitesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function __construct(private readonly Collection $tramites) {}

    public function collection(): Collection
    {
        return $this->tramites->map(fn ($t) => [
            $t->codigo,
            $t->nombre,
            $t->descripcion,
            $t->institucion?->nombre ?? '—',
            $t->dias_habiles,
            $t->activo ? 'Activo' : 'Inactivo',
            $t->created_at?->format('d/m/Y') ?? '',
        ]);
    }

    /** @return array<string> */
    public function headings(): array
    {
        return [
            'Código',
            'Nombre',
            'Descripción',
            'Institución',
            'Días Hábiles',
            'Estado',
            'Fecha de Creación',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F1F5F9'],
                ],
            ],
        ];
    }
}
