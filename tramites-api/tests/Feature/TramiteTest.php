<?php

use App\Models\Institucion;
use App\Models\Tramite;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ── Helpers locales ───────────────────────────────────────────────────────────

function tramitePayload(int $institucionId): array
{
    return [
        'codigo' => 'TRM-TEST',
        'nombre' => 'Trámite de Prueba',
        'descripcion' => 'Descripción del trámite de prueba para el test automatizado.',
        'institucion_id' => $institucionId,
        'dias_habiles' => 5,
    ];
}

// ── Listado ───────────────────────────────────────────────────────────────────

test('puede listar trámites paginados', function () {
    Tramite::factory()->count(3)->create();

    $response = $this->getJson('/api/tramites');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'codigo', 'nombre', 'institucion', 'dias_habiles', 'activo']],
            'meta' => ['current_page', 'total', 'per_page'],
        ]);
});

test('puede filtrar trámites por institución', function () {
    $target = Institucion::factory()->create();
    $other = Institucion::factory()->create();

    Tramite::factory()->count(2)->paraInstitucion($target)->create();
    Tramite::factory()->count(3)->paraInstitucion($other)->create();

    $response = $this->getJson("/api/tramites?institucion_id={$target->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

test('puede buscar trámites por nombre', function () {
    Tramite::factory()->create(['nombre' => 'Registro Civil']);
    Tramite::factory()->create(['nombre' => 'Licencia Comercial']);
    Tramite::factory()->create(['nombre' => 'Permiso de Construcción']);

    $response = $this->getJson('/api/tramites?search=Licencia');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nombre', 'Licencia Comercial');
});

// ── Creación ──────────────────────────────────────────────────────────────────

test('puede crear un trámite válido', function () {
    $token = authToken();
    $institucion = Institucion::factory()->create();

    $response = $this->withToken($token)
        ->postJson('/api/tramites', tramitePayload($institucion->id));

    $response->assertCreated()
        ->assertJsonPath('data.codigo', 'TRM-TEST')
        ->assertJsonPath('data.nombre', 'Trámite de Prueba')
        ->assertJsonPath('data.activo', true);

    $this->assertDatabaseHas('tramites', ['codigo' => 'TRM-TEST']);
});

test('valida campos requeridos al crear trámite', function () {
    $token = authToken();

    $response = $this->withToken($token)->postJson('/api/tramites', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['codigo', 'nombre', 'descripcion', 'institucion_id', 'dias_habiles']);
});

test('valida que la institución exista al crear trámite', function () {
    $token = authToken();

    $payload = tramitePayload(institucionId: 999999);

    $response = $this->withToken($token)->postJson('/api/tramites', $payload);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['institucion_id']);
});

// ── Actualización ─────────────────────────────────────────────────────────────

test('puede actualizar un trámite', function () {
    $token = authToken();
    $tramite = Tramite::factory()->create();

    $response = $this->withToken($token)
        ->putJson("/api/tramites/{$tramite->id}", ['nombre' => 'Nombre Actualizado']);

    $response->assertOk()
        ->assertJsonPath('data.nombre', 'Nombre Actualizado');

    $this->assertDatabaseHas('tramites', [
        'id' => $tramite->id,
        'nombre' => 'Nombre Actualizado',
    ]);
});

// ── Desactivación ─────────────────────────────────────────────────────────────

test('puede desactivar un trámite (soft delete lógico)', function () {
    $token = authToken();
    $tramite = Tramite::factory()->create();

    $response = $this->withToken($token)
        ->deleteJson("/api/tramites/{$tramite->id}");

    $response->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('tramites', [
        'id' => $tramite->id,
        'activo' => false,
    ]);

    // El registro sigue existiendo (no es DELETE físico)
    $this->assertDatabaseCount('tramites', 1);
});

// ── 404 ───────────────────────────────────────────────────────────────────────

test('retorna 404 al buscar trámite inexistente', function () {
    $response = $this->getJson('/api/tramites/999999');

    $response->assertNotFound()
        ->assertJsonPath('success', false);
});
