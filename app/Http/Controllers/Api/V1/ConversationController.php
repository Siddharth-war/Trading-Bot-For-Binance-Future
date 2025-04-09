<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Model\Admin;
use App\Model\BusinessSetting;
use App\Model\Conversation;
use App\Model\DcConversation;
use App\Model\DeliveryMan;
use App\Model\Message;
use App\Model\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConversationController extends Controller
{
    public function __construct(
        private Conversation         $conversation,
        private Admin                $admin,
        private BusinessSetting      $business_setting,
        private DcConversation       $deliverymanConversation,
        private Message              $message,
        private Order                $order,
        private DeliveryMan         $deliveryman
    )
    {}

    /**
     * @param Request $request
     * @return mixed
     */
    public function messageList(Request $request): mixed
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;

        $messages = $this->conversation
            ->where(['user_id' => $request->user()->id])
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return $messages;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getAdminMessage(Request $request): array
    {
        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;
        $messages = $this->conversation->where(['user_id' => $request->user()->id])->latest()->paginate($limit, ['*'], 'page', $offset);
        $messages = ConversationResource::collection($messages);

        return [
            'total_size' => $messages->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'messages' => $messages->items()
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeAdminMessage(Request $request): JsonResponse
    {
        if ($request->message == null && $request->image == null) {
            return response()->json(['message' => translate('Message can not be empty')], 403);
        }

        try {
            $imageNames = [];
            if (!empty($request->file('image'))) {
                foreach ($request->image as $img) {
                    $image = Helpers::upload('conversation/', 'png', $img);
                    $imageUrl = asset('storage/app/public/conversation') . '/' . $image;
                    $imageNames[] = $imageUrl;
                }
                $images = $imageNames;
            } else {
                $images = null;
            }
            $conv = $this->conversation;
            $conv->user_id = $request->user()->id;
            $conv->message = $request->message;
            $conv->image = json_encode($images);
            $conv->save();

            $admin = $this->admin->first();
            $data = [
                'title' => $request->user()->f_name . ' ' . $request->user()->l_name . translate(' send a message'),
                'description' => $request->user()->id,
                'order_id' => '',
                'image' => asset('storage/app/public/restaurant') . '/' . $this->business_setting->where(['key' => 'logo'])->first()->value,
                'type' => 'order_status',
            ];

            try {
                Helpers::send_push_notif_to_device($admin->fcm_token, $data);
            } catch (\Exception $exception) {
            }

            return response()->json(['message' => translate('Successfully sent!')], 200);

        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getDeliverymanConversationList(Request $request): array
    {
        $limit = $request->has('limit') ? $request->limit : null;
        $offset = $request->has('offset') ? $request->offset : 1;

        $adminLastConversation = $this->conversation->where(['user_id' => $request->user()->id])
            ->latest('created_at')
            ->first();

        $deliverymanConversations = $this->deliverymanConversation
            ->with([
                'order',
                'order.delivery_man' => function ($query) {
                    $query->select('id', 'f_name', 'l_name', 'phone', 'email', 'image');
                },
                'order.customer' => function ($query) {
                    $query->select('id', 'f_name', 'l_name', 'phone', 'email', 'image');
                },
                'messages'
            ])
            ->whereHas('messages')
            ->whereHas('order.customer', function ($query) use ($request){
                $query->where(['user_id' => $request->user()->id ]);
            })
            ->when($request->has('search'), function ($query) use ($request) {
                $keyword = $request->input('search');
                return $query->where(function ($query) use ($keyword) {
                    $query->whereHas('order', function ($query) use ($keyword) {
                        $query->where('order_id', 'LIKE', '%' . $keyword . '%');
                    })
                        ->orWhereHas('order.delivery_man', function ($query) use ($keyword) {
                            $query->where('f_name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('l_name', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('phone', 'LIKE', '%' . $keyword . '%')
                                ->orWhere('email', 'LIKE', '%' . $keyword . '%');
                        });
                });
            })
            ->latest()
            ->paginate($limit, ['*'], 'page', $offset);

        return [
            'total_size' => $deliverymanConversations->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'admin_last_conversation' => $adminLastConversation,
            'deliveryman_conversations' => $deliverymanConversations->items(),
        ];

    }

    /**
     * @param Request $request
     * @return JsonResponse|array
     */
    public function getMessageByOrder(Request $request): JsonResponse|array
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;

        $conversations = $this->deliverymanConversation->where('order_id', $request->order_id)->first();
        if (!isset($conversations)) {
            return ['total_size' => 0, 'limit' => (int)$limit, 'offset' => (int)$offset, 'messages' => []];
        }
        $conversations = $conversations->setRelation('messages', $conversations->messages()->latest()->paginate($limit, ['*'], 'page', $offset));
        $message = MessageResource::collection($conversations->messages);

        return [
            'total_size' => $message->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'messages' => $message->items()
        ];
    }

    /**
     * @param Request $request
     * @param $sender_type
     * @return JsonResponse
     */
    public function storeMessageByOrder(Request $request, $sender_type): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $senderId = null;
        $order = $this->order->with('delivery_man')->with('customer')->find($request->order_id);

        //if sender is deliveryman
        if ($sender_type == 'deliveryman') {
            $validator = Validator::make($request->all(), [
                'token' => 'required'
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => Helpers::error_processor($validator)], 403);
            }

            $senderId = $order->delivery_man->id;

        } //if sender is customer
        elseif ($sender_type == 'customer') {
            $senderId = $order->customer->id;
        }

        if ($request->message == null && $request->image == null) {
            return response()->json(['message' => translate('Message can not be empty')], 400);
        }

        $imageNames = [];
        if (!empty($request->file('image'))) {
            foreach ($request->image as $img) {
                $image = Helpers::upload('conversation/', 'png', $img);
                $imageUrl = asset('storage/app/public/conversation') . '/' . $image;
                $imageNames[] = $imageUrl;
            }
            $images = $imageNames;
        } else {
            $images = null;
        }

        //if order id is not null
        if ($request->order_id != null) {
            DB::transaction(function () use ($request, $sender_type, $images, $senderId) {
                $dcConversation = $this->deliverymanConversation->where('order_id', $request->order_id)->first();
                if (!isset($dcConversation)) {
                    $dcConversation = $this->deliverymanConversation;
                    $dcConversation->order_id = $request->order_id;
                    $dcConversation->save();
                }

                $message = $this->message;
                $message->conversation_id = $dcConversation->id;
                $message->customer_id = ($sender_type == 'customer') ? $senderId : null;
                $message->deliveryman_id = ($sender_type == 'deliveryman') ? $senderId : null;
                $message->message = $request->message ?? null;
                $message->attachment = json_encode($images);
                $message->save();
            });
        }

        if ($sender_type == 'customer') {
            $receiverFcmToken = $order->delivery_man->fcm_token ?? null;

        } elseif ($sender_type == 'deliveryman') {
            $receiverFcmToken = $order->customer->cm_firebase_token ?? null;
        }

        $data = [
            'title' => translate('New message arrived'),
            'description' => $request->reply,
            'order_id' => $request->order_id ?? null,
            'image' => '',
            'type' => 'message',
        ];

        try {
            Helpers::send_push_notif_to_device($receiverFcmToken, $data);

        } catch (\Exception $exception) {
            return response()->json(['message' => translate('Push notification send failed')], 200);
        }

        return response()->json(['message' => translate('Message successfully sent')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse|array
     */
    public function getOrderMessageForDm(Request $request): JsonResponse|array
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'order_id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $deliveryMan = $this->deliveryman->where(['auth_token' => $request['token']])->first();
        if (!isset($deliveryMan)) {
            return response()->json(['errors' => 'Unauthenticated.'], 401);
        }

        $limit = $request->has('limit') ? $request->limit : 10;
        $offset = $request->has('offset') ? $request->offset : 1;

        $conversations = $this->deliverymanConversation->where('order_id', $request->order_id)->first();
        if (!isset($conversations)) {
            return ['total_size' => 0, 'limit' => (int)$limit, 'offset' => (int)$offset, 'messages' => []];
        }
        $conversations = $conversations->setRelation('messages', $conversations->messages()->latest()->paginate($limit, ['*'], 'page', $offset));
        $message = MessageResource::collection($conversations->messages);

        return [
            'total_size' => $message->total(),
            'limit' => (int)$limit,
            'offset' => (int)$offset,
            'messages' => $message->items()
        ];
    }

}
