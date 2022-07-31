<?php

namespace App\Http\Controllers;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index()
    {
        $comments = Comment::query()
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);

        return CommentResource::collection($comments);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreCommentRequest $request
     * @return CommentResource
     */
    public function store(StoreCommentRequest $request)
    {
        Post::query()->findOrFail($request['post_id']);

        $comment = Comment::query()->create($request->only([
            'post_id',
            'content'
        ]));

        return new CommentResource($comment);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return CommentResource
     */
    public function show(int $id)
    {
        $comment = Comment::query()->findOrFail($id);

        return new CommentResource($comment);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateCommentRequest $request
     * @param \App\Models\Comment $comment
     * @return CommentResource
     */
    public function update(UpdateCommentRequest $request, Comment $comment)
    {
        $comment->update($request->only([
            'content'
        ]));

        return new CommentResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Comment $comment
     * @return JsonResponse
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return new JsonResponse(status: 204);
    }
}
