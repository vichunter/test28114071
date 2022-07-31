<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use http\Env\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return ResourceCollection
     */
    public function index()
    {
        $posts = Post::query()
            ->without('comments')
            ->latest()
            ->orderByDesc('id')
            ->paginate(10);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StorePostRequest $request
     * @return PostResource
     */
    public function store(StorePostRequest $request)
    {
        $post = Post::query()->create($request->only([
            'title',
            'content'
        ]));

        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return PostResource
     */
    public function show(int $id)
    {
        $post = Post::query()
            ->with('comments')
            ->findOrFail($id);

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdatePostRequest $request
     * @param \App\Models\Post $post
     * @return \App\Http\Resources\PostResource
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->only([
            'title',
            'content',
        ]));

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Post $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return new JsonResponse(status: 204);
    }
}
