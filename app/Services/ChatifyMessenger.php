<?php

namespace App\Services;

use App\Models\ChFavorite as Favorite;
use App\Models\ChMessage as Message;
use Illuminate\Support\Facades\Auth;
use Pusher\Pusher;

class ChatifyMessenger
{
    public $pusher;

    /**
     * Get max file's upload size in MB.
     *
     * @return int
     */
    public function getMaxUploadSize()
    {
        return config('chatify.attachments.max_upload_size') * 1048576;
    }

    public function __construct()
    {
        // Don't initialize Pusher if broadcasting is disabled
        // This allows chat to work on shared hosting without WebSocket
        $this->pusher = null;
    }

    /**
     * Get Pusher instance (lazy initialization)
     *
     * @return Pusher|null
     */
    protected function getPusherInstance()
    {
        // Only initialize if broadcasting is enabled
        if ($this->pusher === null) {
            try {
                $broadcastDefault = config('broadcasting.default', 'null');
                if ($broadcastDefault === 'null' || $broadcastDefault === null) {
                    return null;
                }

                $key = config('chatify.pusher.key', '');
                $secret = config('chatify.pusher.secret', '');
                $appId = config('chatify.pusher.app_id', '');
                $options = config('chatify.pusher.options', []);

                // Validate required config values
                if (empty($key) || empty($secret) || empty($appId)) {
                    return null;
                }

                // Fix host if needed - extract from APP_URL if REVERB_HOST not set
                if (empty($options['host']) || $options['host'] === '127.0.0.1') {
                    $appUrl = config('app.url', '');
                    if ($appUrl) {
                        $parsed = parse_url($appUrl);
                        if (isset($parsed['host'])) {
                            $options['host'] = $parsed['host'];
                        }
                    }
                }

                $this->pusher = new Pusher($key, $secret, $appId, $options);
            } catch (\Throwable $e) {
                // If Pusher fails to initialize, set to null and continue without real-time
                \Log::warning('Pusher initialization failed: '.$e->getMessage());
                $this->pusher = null;
            }
        }

        return $this->pusher;
    }

    /**
     * Get attachment's url with correct domain.
     *
     * @param  string  $attachment_name
     * @return string
     */
    public function getAttachmentUrl($attachment_name)
    {
        $baseUrl = config('app.url');

        return $baseUrl.'/storage/'.config('chatify.attachments.folder').'/'.$attachment_name;
    }

    /**
     * Get user with avatar (formatted).
     *
     * @param  Collection  $user
     * @return Collection
     */
    public function getUserWithAvatar($user)
    {
        if ($user->avatar == 'avatar.png' && config('chatify.gravatar.enabled')) {
            $imageSize = config('chatify.gravatar.image_size');
            $imageset = config('chatify.gravatar.imageset');
            $user->avatar = 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($user->email))).'?s='.$imageSize.'&d='.$imageset;
        } else {
            $user->avatar = $this->getUserAvatarUrl($user->avatar);
        }

        return $user;
    }

    /**
     * Get user avatar url with correct domain.
     *
     * @param  string  $user_avatar_name
     * @return string
     */
    public function getUserAvatarUrl($user_avatar_name)
    {
        $baseUrl = config('app.url');

        // If no avatar name provided, use default
        if (empty($user_avatar_name)) {
            $user_avatar_name = 'avatar.png';
        }

        // Check if it's already a full URL
        if (str_starts_with($user_avatar_name, 'http://') || str_starts_with($user_avatar_name, 'https://')) {
            return $user_avatar_name;
        }

        // Remove /storage/ prefix if it exists
        if (str_starts_with($user_avatar_name, '/storage/')) {
            $user_avatar_name = substr($user_avatar_name, 9); // Remove '/storage/'
        }

        // Check if path already includes folder structure (e.g., 'avatars/avatar_1.jpg')
        if (str_contains($user_avatar_name, '/')) {
            // Path already includes folder structure, use as-is
            return $baseUrl.'/storage/'.$user_avatar_name;
        }

        // Try to find avatar in different locations
        $storage = $this->storage();

        // Check 'avatars/' folder first (ProfileController path)
        if ($storage->exists('avatars/'.$user_avatar_name)) {
            return $baseUrl.'/storage/avatars/'.$user_avatar_name;
        }

        // Check 'users-avatar/' folder (Chatify path)
        if ($storage->exists(config('chatify.user_avatar.folder').'/'.$user_avatar_name)) {
            return $baseUrl.'/storage/'.config('chatify.user_avatar.folder').'/'.$user_avatar_name;
        }

        // Default: try 'avatars/' folder
        return $baseUrl.'/storage/avatars/'.$user_avatar_name;
    }

    /**
     * Get storage instance.
     *
     * @return \Illuminate\Support\Facades\Storage
     */
    public function storage()
    {
        return \Illuminate\Support\Facades\Storage::disk(config('chatify.storage_disk_name'));
    }

    /**
     * Default fetch messages query between a Sender and Receiver.
     *
     * @param  int  $user_id
     * @return Message|\Illuminate\Database\Eloquent\Builder
     */
    public function fetchMessagesQuery($user_id)
    {
        return \App\Models\ChMessage::where('from_id', \Illuminate\Support\Facades\Auth::user()->id)->where('to_id', $user_id)
            ->orWhere('from_id', $user_id)->where('to_id', \Illuminate\Support\Facades\Auth::user()->id);
    }

    /**
     * Get contact item HTML.
     *
     * @param  int  $messenger_id
     * @param  Collection  $user
     * @return string
     */
    public function getContactItem($user)
    {
        try {
            // get last message
            $lastMessage = $this->getLastMessageQuery($user->id);
            // Get Unseen messages counter
            $unseenCounter = $this->countUnseenMessages($user->id);
            if ($lastMessage) {
                $lastMessage->created_at = $lastMessage->created_at->toIso8601String();
                $lastMessage->timeAgo = $lastMessage->created_at->diffForHumans();
            }

            return view('Chatify::layouts.listItem', [
                'get' => 'users',
                'user' => $this->getUserWithAvatar($user),
                'lastMessage' => $lastMessage,
                'unseenCounter' => $unseenCounter,
            ])->render();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * Get last message query.
     *
     * @param  int  $user_id
     * @return Message|Collection|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getLastMessageQuery($user_id)
    {
        return $this->fetchMessagesQuery($user_id)->latest()->first();
    }

    /**
     * Count Unseen messages.
     *
     * @param  int  $user_id
     * @return Collection
     */
    public function countUnseenMessages($user_id)
    {
        return \App\Models\ChMessage::where('from_id', $user_id)->where('to_id', \Illuminate\Support\Facades\Auth::user()->id)->where('seen', 0)->count();
    }

    /**
     * Count total number of conversations with unread messages.
     *
     * @return int
     */
    public function countUnreadConversations()
    {
        $userId = Auth::id();

        // Get all unique user IDs who have sent messages to the current user
        $senders = \App\Models\ChMessage::where('to_id', $userId)
            ->where('seen', 0)
            ->distinct()
            ->pluck('from_id')
            ->toArray();

        return count($senders);
    }

    /**
     * Parse message data.
     *
     * @param  Message  $prefetchedMessage
     * @param  int  $id
     * @return array
     */
    public function parseMessage($prefetchedMessage = null, $id = null)
    {
        $msg = null;
        $attachment = null;
        $attachment_type = null;
        $attachment_title = null;
        if ((bool) $prefetchedMessage) {
            $msg = $prefetchedMessage;
        } else {
            $msg = \App\Models\ChMessage::where('id', $id)->first();
            if (! $msg) {
                return [];
            }
        }
        if (isset($msg->attachment)) {
            $attachmentOBJ = json_decode($msg->attachment);
            $attachment = $attachmentOBJ->new_name;
            $attachment_title = htmlentities(trim($attachmentOBJ->old_name), ENT_QUOTES, 'UTF-8');
            $ext = pathinfo($attachment, PATHINFO_EXTENSION);
            $attachment_type = in_array($ext, $this->getAllowedImages()) ? 'image' : 'file';
        }

        return [
            'id' => $msg->id,
            'from_id' => $msg->from_id,
            'to_id' => $msg->to_id,
            'message' => $msg->body,
            'attachment' => (object) [
                'file' => $attachment,
                'title' => $attachment_title,
                'type' => $attachment_type,
            ],
            'timeAgo' => $msg->created_at->diffForHumans(),
            'created_at' => $msg->created_at->toIso8601String(),
            'isSender' => ($msg->from_id == \Illuminate\Support\Facades\Auth::user()->id),
            'seen' => $msg->seen,
        ];
    }

    /**
     * Return a message card with the given data.
     *
     * @param  Message  $data
     * @param  bool  $isSender
     * @return string
     */
    public function messageCard($data, $renderDefaultCard = false)
    {
        if (! $data) {
            return '';
        }
        if ($renderDefaultCard) {
            $data['isSender'] = false;
        }

        return view('Chatify::layouts.messageCard', $data)->render();
    }

    /**
     * Make messages as seen for a specific user.
     *
     * @param  int  $user_id
     * @return bool
     */
    public function makeSeen($user_id)
    {
        \App\Models\ChMessage::where('from_id', $user_id)
            ->where('to_id', \Illuminate\Support\Facades\Auth::user()->id)
            ->where('seen', 0)
            ->update(['seen' => 1]);

        return 1;
    }

    /**
     * Create a new message to database.
     *
     * @param  array  $data
     * @return Message
     */
    public function newMessage($data)
    {
        $message = new \App\Models\ChMessage;
        $message->from_id = $data['from_id'];
        $message->to_id = $data['to_id'];
        $message->body = $data['body'];
        $message->attachment = $data['attachment'];
        $message->save();

        return $message;
    }

    /**
     * This method returns the allowed image extensions
     * to attach with the message.
     *
     * @return array
     */
    public function getAllowedImages()
    {
        return config('chatify.attachments.allowed_images');
    }

    /**
     * This method returns the allowed file extensions
     * to attach with the message.
     *
     * @return array
     */
    public function getAllowedFiles()
    {
        return config('chatify.attachments.allowed_files');
    }

    /**
     * Returns an array contains messenger's colors
     *
     * @return array
     */
    public function getMessengerColors()
    {
        return config('chatify.colors');
    }

    /**
     * Returns a fallback primary color.
     *
     * @return array
     */
    public function getFallbackColor()
    {
        $colors = $this->getMessengerColors();

        return $colors[0];
    }

    /**
     * Trigger an event using Pusher
     *
     * @param  string  $channel
     * @param  string  $event
     * @param  array  $data
     * @return void
     */
    public function push($channel, $event, $data)
    {
        // Check if broadcasting is enabled
        if (config('broadcasting.default') === 'null' || config('broadcasting.default') === null) {
            // Broadcasting is disabled, skip silently
            return;
        }

        $pusher = $this->getPusherInstance();
        if (! $pusher) {
            // Pusher not available, skip silently (chat works without real-time)
            return;
        }

        try {
            $pusher->trigger($channel, $event, $data);
        } catch (\Exception $e) {
            // Silently fail - allows graceful degradation when Reverb is not running
            \Log::info('Broadcasting skipped (Reverb not available): '.$e->getMessage());
        }
    }

    /**
     * Authentication for pusher
     *
     * @param  mixed  $user
     * @param  mixed  $authUser
     * @param  string  $channelName
     * @param  string  $socketId
     * @return array
     */
    public function pusherAuth($user, $authUser, $channelName, $socketId)
    {
        $pusher = $this->getPusherInstance();
        if (! $pusher) {
            // Return error response if Pusher is not available
            return response()->json(['error' => 'Broadcasting not available'], 403);
        }

        return $pusher->socket_auth($channelName, $socketId);
    }

    /**
     * Get user's messages
     *
     * @param  int  $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function fetchMessages($user_id)
    {
        return Message::where('from_id', Auth::id())
            ->where('to_id', $user_id)
            ->orWhere('from_id', $user_id)
            ->where('to_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Send a message
     *
     * @param  int  $user_id
     * @param  string  $message
     * @param  string  $attachment
     * @return Message
     */
    public function sendMessage($user_id, $message, $attachment = null)
    {
        $message = Message::create([
            'from_id' => Auth::id(),
            'to_id' => $user_id,
            'body' => $message,
            'attachment' => $attachment,
        ]);

        // Trigger real-time update
        $this->push('private-chatify', 'messaging', [
            'from_id' => Auth::id(),
            'to_id' => $user_id,
            'message' => $message,
        ]);

        return $message;
    }

    /**
     * Check if user is in favorites
     *
     * @param  int  $user_id
     * @return bool
     */
    public function inFavorite($user_id)
    {
        return Favorite::where('user_id', Auth::id())
            ->where('favorite_id', $user_id)
            ->exists();
    }

    /**
     * Make user favorite
     *
     * @param  int  $user_id
     * @param  int  $action
     * @return bool
     */
    public function makeInFavorite($user_id, $action)
    {
        if ($action > 0) {
            // Star - Create favorite
            $star = Favorite::create([
                'user_id' => Auth::id(),
                'favorite_id' => $user_id,
            ]);

            return $star ? true : false;
        } else {
            // UnStar - Delete favorite
            $star = Favorite::where('user_id', Auth::id())
                ->where('favorite_id', $user_id)
                ->delete();

            return $star ? true : false;
        }
    }

    /**
     * Make user unfavorite
     *
     * @param  int  $user_id
     * @return bool
     */
    public function makeUnfavorite($user_id)
    {
        return Favorite::where('user_id', Auth::id())
            ->where('favorite_id', $user_id)
            ->delete();
    }

    /**
     * Get user's favorites
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFavorites()
    {
        return Favorite::where('user_id', Auth::id())
            ->with('favorite')
            ->get();
    }

    /**
     * Get contacts list
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getContacts()
    {
        $contacts = Message::join('users', function ($join) {
            $join->on('ch_messages.from_id', '=', 'users.id')
                ->orOn('ch_messages.to_id', '=', 'users.id');
        })
            ->where(function ($q) {
                $q->where('ch_messages.from_id', Auth::id())
                    ->orWhere('ch_messages.to_id', Auth::id());
            })
            ->where('users.id', '!=', Auth::id())
            ->select('users.*', 'ch_messages.created_at', 'ch_messages.from_id', 'ch_messages.to_id')
            ->orderBy('ch_messages.created_at', 'desc')
            ->get()
            ->unique('id');

        return $contacts;
    }

    /**
     * Update contact item
     *
     * @param  int  $user_id
     * @return array
     */
    public function updateContactItem($user_id)
    {
        $user = \App\Models\User::find($user_id);
        $contactItem = view('Chatify::layouts.listItem', [
            'get' => 'user',
            'user' => $user,
            'lastMessage' => $this->getLastMessageQuery($user_id),
        ])->render();

        return [
            'contactItem' => $contactItem,
            'contactID' => $user_id,
        ];
    }

    /**
     * Get shared photos
     *
     * @param  int  $user_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getSharedPhotos($user_id)
    {
        $images = []; // Default
        // Get messages
        $msgs = $this->fetchMessagesQuery($user_id)->orderBy('created_at', 'DESC');
        if ($msgs->count() > 0) {
            foreach ($msgs->get() as $msg) {
                // If message has attachment
                if ($msg->attachment) {
                    $attachment = json_decode($msg->attachment);
                    // determine the type of the attachment
                    if (in_array(pathinfo($attachment->new_name, PATHINFO_EXTENSION), $this->getAllowedImages())) {
                        array_push($images, $attachment->new_name);
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Delete conversation
     *
     * @param  int  $user_id
     * @return bool
     */
    public function deleteConversation($user_id)
    {
        return Message::where('from_id', Auth::id())
            ->where('to_id', $user_id)
            ->orWhere('from_id', $user_id)
            ->where('to_id', Auth::id())
            ->delete();
    }

    /**
     * Delete message
     *
     * @param  int  $message_id
     * @return bool
     */
    public function deleteMessage($message_id)
    {
        return Message::where('id', $message_id)
            ->where('from_id', Auth::id())
            ->delete();
    }

    /**
     * Set active status
     *
     * @param  int  $status
     * @return bool
     */
    public function setActiveStatus($status)
    {
        return Auth::user()->update(['active_status' => $status]);
    }

    /**
     * Get active status
     *
     * @return int
     */
    public function getActiveStatus()
    {
        return Auth::user()->active_status ?? 0;
    }

    /**
     * Update settings
     *
     * @param  array  $data
     * @return bool
     */
    public function updateSettings($data)
    {
        return Auth::user()->update($data);
    }

    /**
     * Search users
     *
     * @param  string  $query
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchUsers($query)
    {
        return \App\Models\User::where('id', '!=', Auth::id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
    }
}
