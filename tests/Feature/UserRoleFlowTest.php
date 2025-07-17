<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserRoleFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear roles necesarios
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'cliente']);
    }

    /** @test */
    public function un_usuario_con_permisos_puede_crear_un_usuario_con_roles()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->post(route('users.store'), [
            'name' => 'Nuevo Usuario',
            'email' => 'nuevo@correo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['admin', 'cliente'],
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@correo.com',
        ]);
        $nuevo = User::where('email', 'nuevo@correo.com')->first();
        $this->assertTrue($nuevo->hasRole('admin'));
        $this->assertTrue($nuevo->hasRole('cliente'));
    }

    /** @test */
    public function no_se_puede_crear_usuario_sin_roles()
    {
        $this->withoutExceptionHandling();
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);

        $response = $this->post(route('users.store'), [
            'name' => 'Sin Rol',
            'email' => 'sinrol@correo.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            // 'roles' => [], // No se envía ningún rol
        ]);

        $response->assertSessionHasErrors('roles');
        $this->assertDatabaseMissing('users', [
            'email' => 'sinrol@correo.com',
        ]);
    }
}
