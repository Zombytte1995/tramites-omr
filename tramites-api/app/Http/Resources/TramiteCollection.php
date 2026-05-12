<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TramiteCollection extends ResourceCollection
{
    // Sin auto-resolve: Laravel buscaría App\Http\Resources\Tramite, no TramiteResource
    public $collects = TramiteResource::class;
}
