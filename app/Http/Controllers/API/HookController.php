<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Http\Request;
use App\Models\Hook;
use App\Http\Resources\HookResource;

class HookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $Hooks = Hook::all();
        return response([ 'Hooks' => HookResource::collection($Hooks), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, $this->rules());
        $webhook = Hook::create($request->only(['title', 'notification_message']));

        $url = config('app.url') . "/publish/{$webhook->identifier}";
        $result = [
            'message' => "hook has been created successfully",
            'data' => "publish URL is {$url}"
        ];
        return response()->json($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Hook  $Hook
     * @return \Illuminate\Http\Response
     */
    public function show(Hook $hook)
    {
        return response(['Hook' => new HookResource($hook), 'message' => 'Retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  \App\Models\Hook  $Hook
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hook $Hook): \Illuminate\Http\Response
    {
        $Hook->update($request->all());

        return response(['Hook' => new HookResource($Hook), 'message' => 'Update successfully'], 200);
    }


    /**
     * @param $webhook
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dispatchNotification($webhook, Request $request): \Illuminate\Http\JsonResponse
    {
        $hooks = User::getIdentifier($webhook);
        $result = [];
        foreach ($hooks as $hook) {
            /** @var User $user */
            $user = $request->user();
            $user->notify(new EventNotification($hook->notification_message));
            $result['message'] = 'Message has been delivered';
        }
        return response()->json($result);
    }

    /**
     * @return string[]
     */
    protected function rules(): array
    {
        return [
            'title' => 'required',
            'notification_message' => 'required'
        ];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hook  $hook
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hook $hook)
    {
        $hook->delete();

        return response(['message' => 'Deleted']);
    }

}
