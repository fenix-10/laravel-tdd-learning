<?php

namespace Tests\Feature;

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


        $response = $this->post('/posts', $data);

        $response->assertOk();

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image_url);

        Storage::disk('local')->assertExists($post->image_url);
    }


    /** @test */
    public function attribute_title_required_for_storing_post()
    {
        $data = [
            'title' => '',
            'description' => 'some description',
            'image' => ''
        ];

        $response = $this->post('/posts', $data);

        $response->assertRedirect();
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

        $response->assertRedirect();
        $response->assertInvalid('image');

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

        $response = $this->patch('/posts/' . $post->id, $data);

        $response->assertOk();

        $updatedPost = Post::first();
        $this->assertEquals($data['title'], $updatedPost->title);
        $this->assertEquals($data['description'], $updatedPost->description);
        $this->assertEquals('images/' . $file->hashName(), $updatedPost->image_url);

        $this->assertEquals($post->id, $updatedPost->id);
    }

    /** @test */
    public function response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $response = $this->get('/posts');

        $response->assertViewIs('posts.index');

        $response->assertSeeText('View page');

        $titles = $posts->pluck('title')->toArray();
        $response->assertSeeText($titles);
    }


    /** @test */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();

        $response = $this->get('/posts/' . $post->id);

        $response->assertViewIs('posts.show');
        $response->assertSeeText('Show page');
        $response->assertSeeText($post->title);
        $response->assertSeeText($post->descritption);
    }

    /** @test */
    public function a_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $post = Post::factory()->create();

        $response  = $this->actingAs($user)->delete('/posts/' . $post->id);

        $response->assertOk();


        $this->assertDatabaseCount('posts', 0);

    }

    /** @test */
    public function a_post_can_be_deleted_by_only_auth_user()
    {

        $post = Post::factory()->create();

        $response  = $this->delete('/posts/' . $post->id);

        $response->assertRedirect();
    }

    /** @test */
    public function response_for_route_posts_edit_is_view_post_edit_with_post_editing()
    {
        $this->withoutExceptionHandling();
        $post = Post::factory()->create();
        $response = $this->get('/posts/' . $post->id . '/edit');

        $response->assertViewIs('posts.edit');
        $response->assertSeeText('Edit page');
        $response->assertSeeText($post->title);
        $response->assertSeeText($post->description);
        $response->assertSeeText($post->image_url);

    }

    /** @test */
    public function response_for_route_posts_create_is_view_post_create_with_post_creating()
    {
        $this->withoutExceptionHandling();

        $response = $this->get('/posts/create');

        $response->assertViewIs('posts.create');

        $response->assertSeeText('This is post create page');

    }

}
