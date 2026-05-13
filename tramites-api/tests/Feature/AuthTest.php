<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

// ── Login ─────────────────────────────────────────────────────────────────────

test('puede hacer login con credenciales válidas', function () {
    User::factory()->create([
        'email'    => 'admin@omr.gob.sv',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'admin@omr.gob.sv',
        'password' => 'password',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'token_type', 'expires_in'])
        ->assertJsonPath('success', true)
        ->assertJsonPath('token_type', 'bearer');
});

test('rechaza login con credenciales inválidas', function () {
    User::factory()->create([
        'email'    => 'admin@omr.gob.sv',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email'    => 'admin@omr.gob.sv',
        'password' => 'contraseña_incorrecta',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false);
});

// ── Guard de autenticación ────────────────────────────────────────────────────

test('rutas protegidas requieren autenticación', function () {
    // POST /tramites requiere auth:api
    $this->postJson('/api/tramites', [])->assertUnauthorized();
    // DELETE /tramites/:id requiere auth:api
    $this->deleteJson('/api/tramites/1')->assertUnauthorized();
    // GET /dashboard/stats requiere auth:api
    $this->getJson('/api/dashboard/stats')->assertUnauthorized();
});

// ── /me ───────────────────────────────────────────────────────────────────────

test('puede obtener usuario autenticado en /me', function () {
    $user  = User::factory()->create(['name' => 'Administrador Test']);
    $token = auth('api')->login($user);

    $response = $this->withToken($token)->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonPath('data.name', 'Administrador Test')
        ->assertJsonPath('data.email', $user->email);
});
