<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreMessageRequest;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    /**
     * Store a newly created message.
     */
    public function store(StoreMessageRequest $request): JsonResponse
    {
        $message = $request->user()->messages()->create($request->validated());

        $message->load('user');

        MessageSent::dispatch($message);

        return response()->json($message, 201);
    }
}
