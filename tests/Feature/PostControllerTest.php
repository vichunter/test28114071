<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /** Post index success */
    public function testIndexSuccess(): void
    {
        $postCount = 3;
        Post::factory($postCount)->create();

        $response = $this->get('/api/posts/')
            ->assertStatus(200);

        $data = $response->json('data');
        $meta = $response->json('meta');

        $this->assertIsArray($data);
        $this->assertCount($postCount, $data);
        $this->assertEquals($postCount, $meta['total']);
    }

    /** Post index has valid data fields */
    public function testIndexReturnsValidFields(): void
    {
        $expectDataFields = [
            'id',
            'created_at',
            'updated_at',
            'title',
            'content',
        ];

        $postCount = 3;
        Post::factory($postCount)->create();

        $response = $this->get('/api/posts/')
            ->assertStatus(200);

        $data = $response->json('data');
        $this->assertEqualsCanonicalizing($expectDataFields, array_keys($data[0]));
    }

    /** Post show returns valid data fields */
    public function testShowReturnsValidFields()
    {
        $expectDataFields = [
            'id',
            'created_at',
            'updated_at',
            'title',
            'content',
            'comments'
        ];

        $commentCount = 3;

        /** @var Post $post */
        $post = Post::factory()
            ->has(Comment::factory($commentCount))
            ->create();

        $response = $this->get("/api/posts/{$post->id}/")
            ->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($post->id, $data['id']);
        $this->assertEqualsCanonicalizing($expectDataFields, array_keys($data));

        $this->assertIsArray($data['comments']);
        $this->assertCount($commentCount, $data['comments']);
    }

    /** Post show returns valid data fields in comments */
    public function testShowReturnsValidFieldsInComments()
    {
        $expectCommentFields = [
            'id',
            'created_at',
            'updated_at',
            'content',
        ];

        $commentCount = 3;

        /** @var Post $post */
        $post = Post::factory()
            ->has(Comment::factory($commentCount))
            ->create();

        $response = $this->get("/api/posts/{$post->id}/")
            ->assertStatus(200);

        $data = $response->json('data');
        $this->assertEqualsCanonicalizing($expectCommentFields, array_keys($data['comments'][0]));
    }

    /** Post store creates and returns valid object */
    public function testStoreSuccess()
    {
        $post = Post::factory()->make();

        $response = $this->post("/api/posts/", $post->toArray())
            ->assertStatus(201);

        $data = $response->json('data');
        $id = $data['id'];

        $this->assertGreaterThan(0, $id);

        $dbPost = Post::query()->find($id)->getAttributes();

        foreach ($post->getAttributes() as $key => $value) {
            $this->assertSame($value, $data[$key], "Response field mismatch: {$key}");
            $this->assertSame($value, $dbPost[$key], "DB object field mismatch: {$key}");
        }
    }

    /**  Post store fails with empty title */
    public function testStoreEmptyTitleError()
    {
        $post = Post::factory()->make([
            'title' => '',
        ]);

        $response = $this->post("/api/posts/", $post->toArray());
        $response->assertStatus(422);
    }

    /**  Post store fails with empty content */
    public function testStoreEmptyContentError()
    {
        $post = Post::factory()->make([
            'content' => '',
        ]);

        $response = $this->post("/api/posts/", $post->toArray());
        $response->assertStatus(422);
    }


    /** Post update succeeded */
    public function testUpdateSuccess()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $update = [
            'title' => 'new title',
            'content' => 'new content',
        ];

        $response = $this->patch("/api/posts/{$post->id}", $update)
            ->assertStatus(200);

        $dbPost = Post::query()->find($post->id);

        foreach ($update as $key => $value) {
            $this->assertSame($value, $dbPost[$key], "DB object field mismatch: {$key}");
        }
    }

    /** Post update failed when empty 'content' field */
    public function testUpdateEmptyContentError()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $update = [
            'title' => 'new title',
            'content' => '',
        ];

        $response = $this->patch("/api/posts/{$post->id}", $update)
            ->assertStatus(422);
    }

    /** Post update failed when empty 'title' field */
    public function testUpdateEmptyTitleError()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $update = [
            'title' => '',
            'content' => 'new content',
        ];

        $response = $this->patch("/api/posts/{$post->id}", $update)
            ->assertStatus(422);
    }

    /** Post delete success */
    public function testDeleteSuccess()
    {
        /** @var Post $post */
        $post = Post::factory()->create();

        $response = $this->delete("/api/posts/{$post->id}")
            ->assertStatus(204);

        $regularPost = Post::query()->find($post->id);

        /** @var Post $deletedPost */
        $deletedPost = Post::withTrashed()->find($post->id);

        $this->assertNull($regularPost);
        $this->assertNotNull($deletedPost);
        $this->assertNotNull($deletedPost->deleted_at);
    }
}
