<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\BaseController;
use App\Http\Resources\UserResource;
use App\Models\Hook;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class NotificationsController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    /**
     * Display the specified resource.
     *
     * @param User $notification
     * @return Response
     */
    public function show(User $notification): Response
    {
        return response(['notification' => new UserResource($notification), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param $webhook
     * @param Request $request
     * @return Application|Response|ResponseFactory
     * @throws ValidationException
     */
    public function subscribe($webhook, Request $request)
    {
        $hook = Hook::whereIdentifier($webhook)->firstOrFail();

        $data = $this->validate($request, [
            'webhook_url' => 'string|required|url'
        ]);

        $data['hook_identifier'] = $hook->identifier;

        $request->user()->update($data);
        return Response(['url'=> auth()->user()->getWebhookUrl(), 'topic' => $hook->identifier],201 );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $notification
     * @return Response
     */
    public function destroy(User $notification)
    {
        $notification->delete();

        return response(['message' => 'Deleted']);
    }
}
