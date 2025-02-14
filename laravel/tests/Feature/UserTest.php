<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;



class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_get_all_users_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        User::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ]
            ]);
    }

    public function test_get_all_users_without_authorization()
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    public function test_get_user_by_id_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_get_single_user_that_does_not_exist()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/users/9999');

        $response->assertStatus(404);
    }

    public function test_update_user_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/users/' . $user->id, [
                'name' => 'nombre nuevo',
                'email' => 'correo_nuevo@test.com',
                'password' => 'nuevacontraseña',
                'password_confirmation' => 'nuevacontraseña',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_update_user_without_authorization()
    {
        $user = User::factory()->create();

        $response = $this->putJson('/api/users/' . $user->id, [
            'name' => 'nombre nuevo',
            'email' => 'correo_nuevo@test.com',
            'password' => 'nuevacontraseña',
            'password_confirmation' => 'nuevacontraseña',
        ]);

        $response->assertStatus(401);
    }

    public function test_delete_user_without_authorization()
    {

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $anotherUser = User::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/users/' . $anotherUser->id);

        $response->assertStatus(403)->assertJson([
            'error' => 'No tenes permiso para eliminar este usuario'
        ]);
    }

    public function test_delete_user_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/users/' . $user->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Usuario eliminado'
            ]);
    }
}
