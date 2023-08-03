<?php

namespace Api;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;


    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->withHeaders([
            'accept' => 'application/json'
        ]);
    }

    /** @test */
    public function a_post_can_be_stored()
    {
        $this->withoutExceptionHandling();

        $file = File::create('my_image.jpg');


        $data = [
          'title' => 'some title',
            'description' => 'some description',
            'image' => $file
        ];


        $response = $this->post('/api/posts', $data);



        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);

        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image_url' => $post->image_url,
        ]);
    }


    /** @test */
    public function attribute_title_required_for_storing_post()
    {
        $data = [
            'title' => '',
            'description' => 'some description',
            'image' => ''
        ];

        $response = $this->post('/api/posts', $data);

        $response->assertStatus(422);
        $response->assertInvalid('title');

    }

    /** @test */
    public function attribute_image_is_file_for_storing_post()
    {

        $data = [
            'title' => 'Title',
            'description' => 'some description',
            'image' => 'dfdfdf'
        ];

        $response = $this->post('/posts', $data);

        $response->assertStatus(422);
        $response->assertInvalid('image');
        $response->assertJsonValidationErrors([
            'image' => 'The image field must be a file.'
        ]);
    }


    /** @test */
    public function a_post_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $file = File::create('image.jpg');

        $data = [
            'title' => 'Title edited',
            'description' => 'some description edited',
            'image' => $file
        ];

        $response = $this->patch('/api/posts/' . $post->id, $data);

        $response->assertJson([
            'id' => $post->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'image_url' => 'images/' . $file->hashName(),
        ]);
    }


    /** @test */
    public function response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $response = $this->get('/api/posts');

        $response->assertOk();

        $json = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'image_url' => $post->image_url,
            ];
        })->toArray();

        $response->assertExactJson($json);
    }

    /** @test */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->get('/api/posts/' . $post->id);

        $response->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image_url' => $post->image_url,
        ]);
    }

    /** @test */
    public function a_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $response  = $this->actingAs($user)->delete('/api/posts/' . $post->id);

        $response->assertOk();


        $this->assertDatabaseCount('posts', 0);

        $response->assertJson([
            'message' => 'deleted',
        ]);
    }

    /** @test */
    public function a_post_can_be_deleted_by_only_auth_user()
    {

        $post = Post::factory()->create();

        $response  = $this->delete('/api/posts/' . $post->id);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('posts', 1);
    }
}
