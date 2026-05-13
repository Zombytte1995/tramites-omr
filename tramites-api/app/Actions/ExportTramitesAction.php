<?php

namespace App\Actions;

use App\Exports\TramitesExport;
use App\Repositories\Contracts\TramiteRepositoryInterface;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportTramitesAction
{
    public function __construct(
        private readonly TramiteRepositoryInterface $tramites,
    ) {}

    public function execute(?int $institucionId, ?string $search): BinaryFileResponse
    {
        $collection = $this->tramites->getForExport($institucionId, $search);
        $filename = 'tramites_'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(new TramitesExport($collection), $filename);
    }
}
