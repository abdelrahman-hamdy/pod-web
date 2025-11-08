<?php

namespace App\Http\Controllers\Chatify\Api;

use App\Models\ChFavorite as Favorite;
use App\Models\ChMessage as Message;
use App\Models\User;
use App\Facades\ChatifyMessenger as Chatify;
use App\NotificationType;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class MessagesController extends Controller
{
    protected ?NotificationService $notificationService = null;

    public function __construct()
    {
        // Inject notification service if available
        try {
            $this->notificationService = app(NotificationService::class);
        } catch (\Exception $e) {
            // Service not available, notifications will be skipped
            $this->notificationService = null;
        }
    }
    protected $perPage = 30;

    /**
     * Authinticate the connection for pusher
     *
     * @return void
     */
    public function pusherAuth(Request $request)
    {
        return Chatify::pusherAuth(
            $request->user(),
            Auth::user(),
            $request['channel_name'],
            $request['socket_id']
        );
    }

    /**
     * Fetch data by id for (user/group)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function idFetchData(Request $request)
    {
        // Favorite
        $favorite = Chatify::inFavorite($request['id']);

        // User data
        if ($request['type'] == 'user') {
            $fetch = User::where('id', $request['id'])->first();
            if ($fetch) {
                $userAvatar = Chatify::getUserWithAvatar($fetch)->avatar;
            }
        }

        // send the response
        return Response::json([
            'favorite' => $favorite,
            'is_pinned' => (bool) $favorite,
            'fetch' => $fetch ?? null,
            'user_avatar' => $userAvatar ?? null,
        ]);
    }

    /**
     * This method to make a links for the attachments
     * to be downloadable.
     *
     * @param  string  $fileName
     * @return \Illuminate\Http\JsonResponse
     */
    public function download($fileName)
    {
        $path = config('chatify.attachments.folder').'/'.$fileName;
        if (Chatify::storage()->exists($path)) {
            return response()->json([
                'file_name' => $fileName,
                'download_path' => Chatify::storage()->url($path),
            ], 200);
        } else {
            return response()->json([
                'message' => 'Sorry, File does not exist in our server or may have been deleted!',
            ], 404);
        }
    }

    /**
     * Send a message to database
     *
     * @return JSON response
     */
    public function send(Request $request)
    {
        // default variables
        $error = (object) [
            'status' => 0,
            'message' => null,
        ];
        $attachment = null;
        $attachment_title = null;

        // if there is attachment [file]
        if ($request->hasFile('file')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();
            $allowed_files = Chatify::getAllowedFiles();
            $allowed = array_merge($allowed_images, $allowed_files);

            $file = $request->file('file');
            // check file size
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->extension()), $allowed)) {
                    // get attachment name
                    $attachment_title = $file->getClientOriginalName();
                    // upload attachment and store the new name
                    $attachment = Str::uuid().'.'.$file->extension();
                    $file->storeAs(config('chatify.attachments.folder'), $attachment, config('chatify.storage_disk_name'));
                } else {
                    $error->status = 1;
                    $error->message = 'File extension not allowed!';
                }
            } else {
                $error->status = 1;
                $error->message = 'File size you are trying to upload is too large!';
            }
        }

        if (! $error->status) {
            // send to database
            $message = Chatify::newMessage([
                'type' => $request['type'],
                'from_id' => Auth::user()->id,
                'to_id' => $request['id'],
                'body' => htmlentities(trim($request['message']), ENT_QUOTES, 'UTF-8'),
                'attachment' => ($attachment) ? json_encode((object) [
                    'new_name' => $attachment,
                    'old_name' => htmlentities(trim($attachment_title), ENT_QUOTES, 'UTF-8'),
                ]) : null,
            ]);

            // fetch message to send it with the response
            $messageData = Chatify::parseMessage($message);

            // send to user using pusher
            if (Auth::user()->id != $request['id']) {
                Chatify::push('private-chatify.'.$request['id'], 'messaging', [
                    'from_id' => Auth::user()->id,
                    'to_id' => $request['id'],
                    'message' => $messageData,
                ]);
                
                // Send push notification
                $this->sendMessageNotification($request['id'], $messageData);
            }
        }

        // send the response
        return Response::json([
            'status' => '200',
            'error' => $error,
            'message' => $messageData ?? [],
            'tempID' => $request['temporaryMsgId'],
        ]);
    }

    /**
     * Send push notification for new message.
     */
    protected function sendMessageNotification($recipientId, $messageData)
    {
        if (!$this->notificationService) {
            return;
        }

        try {
            $recipient = \App\Models\User::find($recipientId);
            
            if (!$recipient || !$recipient->fcm_token) {
                return;
            }

            $sender = Auth::user();
            $messageBody = $messageData['message'] ?? 'Sent an attachment';
            
            // Truncate message for notification
            if (strlen($messageBody) > 100) {
                $messageBody = substr($messageBody, 0, 100) . '...';
            }

            $this->notificationService->send(
                $recipient,
                NotificationType::MESSAGE_RECEIVED,
                [
                    'title' => 'New Message',
                    'body' => $sender->name . ': ' . $messageBody,
                    'sender_id' => $sender->id,
                    'sender_name' => $sender->name,
                    'sender_avatar' => $sender->avatar,
                    'message_id' => $messageData['id'] ?? null,
                    'conversation_id' => $sender->id, // In chatify, conversation is identified by sender ID
                    'avatar' => $sender->avatar,
                ],
                ['database', 'push']
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send message notification: ' . $e->getMessage());
        }
    }

    /**
     * fetch [user/group] messages from database
     *
     * @return JSON response
     */
    public function fetch(Request $request)
    {
        $query = Chatify::fetchMessagesQuery($request['id'])->latest();
        $messages = $query->paginate($request->per_page ?? $this->perPage);
        $totalMessages = $messages->total();
        $lastPage = $messages->lastPage();

        // Parse each message to add computed fields like timeAgo, isSender, etc.
        $parsedMessages = array_map(function ($message) {
            return Chatify::parseMessage($message);
        }, $messages->items());

        $response = [
            'total' => $totalMessages,
            'last_page' => $lastPage,
            'last_message_id' => collect($parsedMessages)->last()->id ?? null,
            'messages' => $parsedMessages,
        ];

        return Response::json($response);
    }

    /**
     * Make messages as seen
     *
     * @return void
     */
    public function seen(Request $request)
    {
        // make as seen
        $seen = Chatify::makeSeen($request['id']);

        // send the response
        return Response::json([
            'status' => $seen,
        ], 200);
    }

    /**
     * Get contacts list
     *
     * @return \Illuminate\Http\JsonResponse response
     */
    public function getContacts(Request $request)
    {
        $authId = Auth::id();
        $perPage = $request->per_page ?? $this->perPage;

        $latestMessageIds = Message::query()
            ->selectRaw('CASE WHEN from_id = ? THEN to_id ELSE from_id END AS contact_id', [$authId])
            ->selectRaw('MAX(id) AS last_message_id')
            ->where(function ($query) use ($authId) {
                $query->where('from_id', $authId)
                    ->orWhere('to_id', $authId);
            })
            ->groupBy('contact_id');

        $unreadCounts = Message::query()
            ->selectRaw('from_id AS contact_id, COUNT(*) AS unread_count')
            ->where('to_id', $authId)
            ->where('seen', 0)
            ->groupBy('from_id');

        $contacts = User::query()
            ->joinSub($latestMessageIds, 'latest', function ($join) {
                $join->on('users.id', '=', 'latest.contact_id');
            })
            ->leftJoin('ch_messages as last_message', 'last_message.id', '=', 'latest.last_message_id')
            ->leftJoin('ch_favorites as pinned', function ($join) use ($authId) {
                $join->on('pinned.favorite_id', '=', 'users.id')
                    ->where('pinned.user_id', '=', $authId);
            })
            ->leftJoinSub($unreadCounts, 'unread', function ($join) {
                $join->on('users.id', '=', 'unread.contact_id');
            })
            ->where('users.id', '!=', $authId)
            ->select([
                'users.*',
                'latest.last_message_id',
                DB::raw('COALESCE(unread.unread_count, 0) as unread_count'),
                DB::raw('pinned.id as pinned_id'),
            ])
            ->orderByRaw('CASE WHEN pinned.id IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('last_message.created_at')
            ->paginate($perPage);

        $messageMap = Message::query()
            ->whereIn('id', $contacts->getCollection()->pluck('last_message_id')->filter()->all())
            ->get()
            ->keyBy('id');

        $formatted = $contacts->getCollection()->map(function (User $contact) use ($messageMap, $authId) {
            $userData = collect($contact->only([
                'id',
                'first_name',
                'last_name',
                'name',
                'email',
                'email_verified_at',
                'phone',
                'city',
                'country',
                'gender',
                'bio',
                'title',
                'company',
                'avatar',
                'avatar_color',
                'skills',
                'experience_level',
                'education',
                'portfolio_links',
                'linkedin_url',
                'github_url',
                'twitter_url',
                'website_url',
                'profile_views',
                'role',
                'profile_completed',
                'profile_onboarding_seen',
                'is_active',
                'active_status',
                'created_at',
                'updated_at',
                'fcm_token',
            ]));

            $userWithAvatar = Chatify::getUserWithAvatar($contact->replicate());
            $userData->put('avatar', $userWithAvatar->avatar);

            $lastMessage = null;
            if ($contact->last_message_id && $messageMap->has($contact->last_message_id)) {
                $lastMessage = Chatify::parseMessage($messageMap->get($contact->last_message_id));
            }

            return [
                'user' => $userData->toArray(),
                'last_message' => $lastMessage,
                'unread_count' => (int) ($contact->unread_count ?? 0),
                'is_pinned' => ! is_null($contact->pinned_id),
                // Backwards compatibility for older clients expecting is_favorite
                'is_favorite' => ! is_null($contact->pinned_id),
            ];
        });

        return response()->json([
            'contacts' => $formatted->values(),
            'total' => $contacts->total() ?? 0,
            'last_page' => $contacts->lastPage() ?? 1,
        ], 200);
    }

    /**
     * Put a user in the favorites list
     *
     * @return void
     */
    public function favorite(Request $request)
    {
        $userId = $request['user_id'];
        // check action [star/unstar]
        $favoriteStatus = Chatify::inFavorite($userId) ? 0 : 1;
        Chatify::makeInFavorite($userId, $favoriteStatus);

        // send the response
        return Response::json([
            'status' => @$favoriteStatus,
            'is_pinned' => (bool) $favoriteStatus,
        ], 200);
    }

    /**
     * Get favorites list
     *
     * @return void
     */
    public function getFavorites(Request $request)
    {
        $favorites = Favorite::where('user_id', Auth::user()->id)->get();
        foreach ($favorites as $favorite) {
            $favorite->user = User::where('id', $favorite->favorite_id)->first();
        }

        return Response::json([
            'total' => count($favorites),
            'favorites' => $favorites ?? [],
            'pinned' => $favorites ?? [],
        ], 200);
    }

    /**
     * Search in messenger
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $input = trim((string) $request->input('input', ''));
        $perPage = (int) ($request->per_page ?? $this->perPage);

        if ($input === '') {
            return Response::json([
                'records' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $perPage,
                'has_more' => false,
            ], 200);
        }

        $columns = [
            'id',
            'name',
            'email',
            'avatar',
            'avatar_color',
            'active_status',
            'title',
            'company',
            'city',
            'country',
        ];

        $paginator = User::select($columns)
            ->where('id', '!=', Auth::user()->id)
            ->where(function ($query) use ($input) {
                $query->where('name', 'LIKE', "%{$input}%")
                    ->orWhere('email', 'LIKE', "%{$input}%");
            })
            ->orderByRaw('LOWER(name)')
            ->paginate($perPage);

        $items = collect($paginator->items())
            ->map(function ($user) {
                $withAvatar = Chatify::getUserWithAvatar($user);

                return [
                    'id' => $withAvatar->id,
                    'name' => $withAvatar->name,
                    'email' => $withAvatar->email,
                    'avatar' => $withAvatar->avatar ?? null,
                    'avatar_color' => $withAvatar->avatar_color ?? null,
                    'active_status' => (bool) $withAvatar->active_status,
                    'title' => $withAvatar->title,
                    'company' => $withAvatar->company,
                    'city' => $withAvatar->city,
                    'country' => $withAvatar->country,
                ];
            })
            ->values();

        return Response::json([
            'records' => $items,
            'total' => $paginator->total(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
        ], 200);
    }

    /**
     * Get shared photos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function sharedPhotos(Request $request)
    {
        $images = Chatify::getSharedPhotos($request['user_id']);

        foreach ($images as $image) {
            $image = asset(config('chatify.attachments.folder').$image);
        }

        // send the response
        return Response::json([
            'shared' => $images ?? [],
        ], 200);
    }

    /**
     * Delete conversation
     *
     * @return void
     */
    public function deleteConversation(Request $request)
    {
        // delete
        $delete = Chatify::deleteConversation($request['id']);

        // send the response
        return Response::json([
            'deleted' => $delete ? 1 : 0,
        ], 200);
    }

    public function updateSettings(Request $request)
    {
        $msg = null;
        $error = $success = 0;

        // dark mode
        if ($request['dark_mode']) {
            $request['dark_mode'] == 'dark'
                ? User::where('id', Auth::user()->id)->update(['dark_mode' => 1])  // Make Dark
                : User::where('id', Auth::user()->id)->update(['dark_mode' => 0]); // Make Light
        }

        // If messenger color selected
        if ($request['messengerColor']) {
            $messenger_color = trim(filter_var($request['messengerColor']));
            User::where('id', Auth::user()->id)
                ->update(['messenger_color' => $messenger_color]);
        }
        // if there is a [file]
        if ($request->hasFile('avatar')) {
            // allowed extensions
            $allowed_images = Chatify::getAllowedImages();

            $file = $request->file('avatar');
            // check file size
            if ($file->getSize() < Chatify::getMaxUploadSize()) {
                if (in_array(strtolower($file->extension()), $allowed_images)) {
                    // delete the older one
                    if (Auth::user()->avatar != config('chatify.user_avatar.default')) {
                        $path = Chatify::getUserAvatarUrl(Auth::user()->avatar);
                        if (Chatify::storage()->exists($path)) {
                            Chatify::storage()->delete($path);
                        }
                    }
                    // upload
                    $avatar = Str::uuid().'.'.$file->extension();
                    $update = User::where('id', Auth::user()->id)->update(['avatar' => $avatar]);
                    $file->storeAs(config('chatify.user_avatar.folder'), $avatar, config('chatify.storage_disk_name'));
                    $success = $update ? 1 : 0;
                } else {
                    $msg = 'File extension not allowed!';
                    $error = 1;
                }
            } else {
                $msg = 'File size you are trying to upload is too large!';
                $error = 1;
            }
        }

        // send the response
        return Response::json([
            'status' => $success ? 1 : 0,
            'error' => $error ? 1 : 0,
            'message' => $error ? $msg : 0,
        ], 200);
    }

    /**
     * Set user's active status
     *
     * @return void
     */
    public function setActiveStatus(Request $request)
    {
        $activeStatus = $request['status'] > 0 ? 1 : 0;
        $status = User::where('id', Auth::user()->id)->update(['active_status' => $activeStatus]);

        return Response::json([
            'status' => $status,
        ], 200);
    }

 

    /**
     * Get unread conversations count
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount()
    {
        $count = Chatify::countUnreadConversations();

        return Response::json([
            'count' => $count,
        ], 200);
    }
}
