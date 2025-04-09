<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\Conversation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ConversationController extends Controller
{
    public function __construct(
        private User         $user,
        private Admin        $admin,
        private Conversation $conversation
    )
    {}

    /**
     * @return Renderable
     */
    public function list(): Renderable
    {
        $conversations = $this->conversation->latest()->get();
        return view('admin-views.messages.index', compact('conversations'));
    }

    /**
     * @param $user_id
     * @return JsonResponse
     */
    public function view($user_id): JsonResponse
    {
        $convs = $this->conversation->where(['user_id' => $user_id])->get();
        $this->conversation->where(['user_id' => $user_id])->update(['checked' => 1]);
        $user = $this->user->find($user_id);

        return response()->json([
            'view' => view('admin-views.messages.partials._conversations', compact('convs', 'user'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @param $user_id
     * @return JsonResponse
     */
    public function store(Request $request, $user_id): JsonResponse
    {
        if (!$request->reply && empty($request->file('images'))) {
            return response()->json([], 403);
        }

        if ($request->images) {
            $conversationImageNames = [];
            foreach ($request->images as $img) {
                $image = Helpers::upload('conversation/', 'png', $img);
                $image_url = $image;
                $conversationImageNames[] = $image_url;
            }
            $images = $conversationImageNames;
        } else {
            $images = null;
        }

        $this->conversation->insert([
            'user_id' => $user_id,
            'reply' => $request->reply,
            'image' => json_encode($images),
            'checked' => 1,
            'is_reply' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $convs = $this->conversation->where(['user_id' => $user_id])->get();
        $user = $this->user->find($user_id);

        //send push notification
        $fcm_token = $user->cm_firebase_token;
        $data = [
            'title' => translate('New message arrived'),
            'description' => Str::limit($request->reply ?? '', 500),
            'order_id' => '',
            'image' => '',
            'type' => 'message',
        ];

        try {
            Helpers::send_push_notif_to_device($fcm_token, $data);
        } catch (\Exception $exception) {
            //
        }

        return response()->json([
            'view' => view('admin-views.messages.partials._conversations', compact('convs', 'user'))->render()
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        try {
            $admin = $this->admin->find(auth('admin')->id());
            $admin->fcm_token = $request->fcm_token;
            $admin->save();

            return response()->json(['message' => translate('FCM token updated successfully.')], 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => translate('FCM token updated failed.')], 200);
        }
    }

    /**
     * @return JsonResponse
     */
    public function getConversations(): JsonResponse
    {
        $conversations = $this->conversation->latest()->get();
        return response()->json([
            'conversation_sidebar' => view('admin-views.messages.partials._list', compact('conversations'))->render(),
        ]);
    }

    /**
     * @return mixed|null
     */
    public function getFirebaseConfig(): mixed
    {
        return Helpers::get_business_settings('firebase_message_config');
    }
}
