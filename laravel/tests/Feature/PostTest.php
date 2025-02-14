<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Post;


class PostTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    public function test_create_post_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->actingAs($user)->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/posts', [
                'title' => 'Post de prueba',
                'content' => 'Contenido del post de prueba',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'title',
                'content',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    /**
     * Test to create a post with invalid data.
     */
    public function test_create_post_with_invalid_data()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/posts', [
                'title' => '',
                'content' => '',
            ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'title',
                'content',
            ]);
    }



    public function test_get_all_posts()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/posts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'content',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_get_all_posts_without_authorization()
    {

        $response = $this->getJson('/api/posts');
        $response->assertStatus(401);
    }

    public function test_get_single_post_successfully()
    {
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $post = Post::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/posts/' . $post->id);


        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'content',
                'user_id',
                'created_at',
                'updated_at',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_update_post_successfully()
    {

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson('/api/posts/' . $post->id, [
                'title' => 'Post actualizado',
                'content' => 'Contenido del post actualizado',
            ]);


        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'content',
                'user_id',
                'created_at',
                'updated_at',
            ]);
    }

    public function test_update_post_without_authorization()
    {

        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);


        $response = $this->putJson('/api/posts/' . $user->id, [
            'title' => 'Post actualizado',
            'content' => 'Contenido del post actualizado',
        ]);

        $response->assertStatus(401);
    }

    public function test_delete_post_successfully()
    {

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/posts/' . $post->id);

        $response->assertStatus(200)
        ->assertJson(['message' => 'Post eliminado exitosamente']);
    }

    public function test_delete_post_without_authorization()
    {

        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);
        $post = Post::factory()->create(['user_id' => $user->id]);
        $anotherPost = Post::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson('/api/posts/' . $anotherPost->id);

        $response->assertStatus(401)->assertJson([
            'error' => 'No tenes permiso para modificar este post'
        ]);
    }
}
