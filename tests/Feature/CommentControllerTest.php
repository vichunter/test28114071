<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    /** Comment index success */
    public function testIndexSuccess(): void
    {
        $commentCount = 3;
        Comment::factory($commentCount)
            ->for(Post::factory())
            ->create();

        $response = $this->get('/api/comments/')
            ->assertStatus(200);

        $data = $response->json('data');
        $meta = $response->json('meta');

        $this->assertIsArray($data);
        $this->assertCount($commentCount, $data);
        $this->assertEquals($commentCount, $meta['total']);
    }

    /** Comment index has valid data fields */
    public function testIndexReturnsValidFields(): void
    {
        $expectDataFields = [
            'id',
            'created_at',
            'updated_at',
            'content',
        ];

        $commentCount = 3;
        Comment::factory($commentCount)
            ->for(Post::factory())
            ->create();

        $response = $this->get('/api/comments/')
            ->assertStatus(200);

        $data = $response->json('data');
        $this->assertEqualsCanonicalizing($expectDataFields, array_keys($data[0]));
    }

    /** Comment show returns valid data fields */
    public function testShowReturnsValidFields()
    {
        $expectDataFields = [
            'id',
            'created_at',
            'updated_at',
            'content',
        ];

        /** @var Comment $comment */
        $comment = Comment::factory()
            ->for(Post::factory())
            ->create();

        $response = $this->get("/api/comments/{$comment->id}/")
            ->assertStatus(200);

        $data = $response->json('data');

        $this->assertEquals($comment->id, $data['id']);
        $this->assertEqualsCanonicalizing($expectDataFields, array_keys($data));
    }


    /** Comment store creates and returns valid object */
    public function testStoreSuccess()
    {
        $post = Post::factory()->create();

        /** @var Comment $comment */
        $comment = Comment::factory()->for($post)->make();

        $response = $this->post("/api/comments/", $comment->toArray())
            ->assertStatus(201);

        $data = $response->json('data');
        $id = $data['id'];

        $this->assertGreaterThan(0, $id);

        /** @var Comment $dbComment */
        $dbComment = Comment::query()->find($id);

        $this->assertSame($comment->content, $data['content']);
        $this->assertSame($comment->content, $dbComment->content);
    }

    /**  Comment store fails with empty content */
    public function testStoreEmptyContentError()
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->make([
            'content' => '',
        ]);

        $response = $this->post("/api/comments/", $comment->toArray());
        $response->assertStatus(422);
    }


    /** Comment update succeeded */
    public function testUpdateSuccess()
    {
        $post = Post::factory()->create();

        /** @var Comment $comment */
        $comment = Comment::factory()->for($post)->create();

        $update = [
            'content' => 'new content',
        ];

        $this->patch("/api/comments/{$comment->id}", $update)
            ->assertStatus(200);

        /** @var Comment $dbComment */
        $dbComment = Comment::query()->find($comment->id);

        $this->assertSame($update['content'], $dbComment->content);
    }

    /** Comment update failed when empty 'content' field */
    public function testUpdateEmptyContentError()
    {
        $post = Post::factory()->create();

        /** @var Comment $comment */
        $comment = Comment::factory()->for($post)->create();

        $update = [
            'content' => '',
        ];

        $response = $this->patch("/api/comments/{$comment->id}", $update)
            ->assertStatus(422);
    }


    /** Comment delete success */
    public function testDeleteSuccess()
    {
        $post = Post::factory()->create();

        /** @var Comment $comment */
        $comment = Comment::factory()->for($post)->create();

        $response = $this->delete("/api/comments/{$comment->id}")
            ->assertStatus(204);

        $regularComment = Comment::query()->find($comment->id);

        /** @var Comment $deletedComment */
        $deletedComment = Comment::withTrashed()->find($comment->id);

        $this->assertNull($regularComment);
        $this->assertNotNull($deletedComment);
        $this->assertNotNull($deletedComment->deleted_at);
    }
}
