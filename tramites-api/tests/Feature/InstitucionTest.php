<?php

use App\Enums\TipoInstitucion;
use App\Models\Institucion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Listado ───────────────────────────────────────────────────────────────────

test('puede listar instituciones activas', function () {
    Institucion::factory()->count(3)->create();
    Institucion::factory()->inactiva()->create();

    $response = $this->getJson('/api/instituciones');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['id', 'nombre', 'tipo']]]);
});

// ── Creación ──────────────────────────────────────────────────────────────────

test('puede crear una institución válida', function () {
    $token = authToken();

    $payload = [
        'nombre' => 'Ministerio de Prueba',
        'tipo' => TipoInstitucion::MINISTERIO->value,
    ];

    $response = $this->withToken($token)->postJson('/api/instituciones', $payload);

    $response->assertCreated()
        ->assertJsonPath('data.nombre', 'Ministerio de Prueba')
        ->assertJsonPath('data.tipo.valor', TipoInstitucion::MINISTERIO->value)
        ->assertJsonPath('data.tipo.etiqueta', TipoInstitucion::MINISTERIO->label());

    $this->assertDatabaseHas('instituciones', ['nombre' => 'Ministerio de Prueba']);
});

// ── Validación ────────────────────────────────────────────────────────────────

test('valida campos requeridos al crear institución', function () {
    $token = authToken();

    $response = $this->withToken($token)->postJson('/api/instituciones', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['nombre', 'tipo']);
});

test('rechaza tipo de institución inválido', function () {
    $token = authToken();

    $payload = [
        'nombre' => 'Institución Inválida',
        'tipo' => 'TIPO_INVENTADO',
    ];

    $response = $this->withToken($token)->postJson('/api/instituciones', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['tipo']);
});
