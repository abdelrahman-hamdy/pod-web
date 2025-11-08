<?php

namespace App\Traits;

use App\Models\User;
use App\NotificationType;
use App\Services\NotificationService;

trait SendsNotifications
{
    /**
     * Get notification service instance.
     */
    protected function getNotificationService(): NotificationService
    {
        return app(NotificationService::class);
    }

    /**
     * Send post liked notification.
     */
    protected function sendPostLikedNotification(User $postOwner, User $liker, int $postId): void
    {
        if ($postOwner->id === $liker->id) {
            return; // Don't notify self
        }

        $this->getNotificationService()->send(
            $postOwner,
            NotificationType::POST_LIKED,
            [
                'title' => 'Post Liked',
                'body' => $liker->name . ' liked your post',
                'post_id' => $postId,
                'liker_id' => $liker->id,
                'liker_name' => $liker->name,
                'avatar' => $liker->avatar,
                'avatar_color' => $liker->avatar_color,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send comment notification.
     */
    protected function sendCommentNotification(User $postOwner, User $commenter, int $postId, int $commentId, ?string $commentPreview = null): void
    {
        if ($postOwner->id === $commenter->id) {
            return; // Don't notify self
        }

        $body = $commenter->name . ' commented on your post';
        if ($commentPreview) {
            $preview = strlen($commentPreview) > 50 ? substr($commentPreview, 0, 50) . '...' : $commentPreview;
            $body .= ': "' . $preview . '"';
        }

        $this->getNotificationService()->send(
            $postOwner,
            NotificationType::COMMENT_ADDED,
            [
                'title' => 'New Comment',
                'body' => $body,
                'post_id' => $postId,
                'comment_id' => $commentId,
                'commenter_id' => $commenter->id,
                'commenter_name' => $commenter->name,
                'avatar' => $commenter->avatar,
                'avatar_color' => $commenter->avatar_color,
                'comment_preview' => $commentPreview,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send comment reply notification.
     */
    protected function sendCommentReplyNotification(User $commentOwner, User $replier, int $postId, int $commentId, ?string $replyPreview = null): void
    {
        if ($commentOwner->id === $replier->id) {
            return; // Don't notify self
        }

        $body = $replier->name . ' replied to your comment';
        if ($replyPreview) {
            $preview = strlen($replyPreview) > 50 ? substr($replyPreview, 0, 50) . '...' : $replyPreview;
            $body .= ': "' . $preview . '"';
        }

        $this->getNotificationService()->send(
            $commentOwner,
            NotificationType::COMMENT_REPLY,
            [
                'title' => 'New Reply',
                'body' => $body,
                'post_id' => $postId,
                'comment_id' => $commentId,
                'replier_id' => $replier->id,
                'replier_name' => $replier->name,
                'avatar' => $replier->avatar,
                'avatar_color' => $replier->avatar_color,
                'reply_preview' => $replyPreview,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send event registration notification.
     */
    protected function sendEventRegistrationNotification(User $user, array $eventData): void
    {
        $this->getNotificationService()->send(
            $user,
            NotificationType::EVENT_REGISTERED,
            [
                'title' => 'Event Registration Confirmed',
                'body' => 'You have successfully registered for ' . $eventData['name'],
                'event_id' => $eventData['id'],
                'event_name' => $eventData['name'],
                'event_date' => $eventData['date'] ?? null,
                'event_location' => $eventData['location'] ?? null,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send event reminder notification.
     */
    protected function sendEventReminderNotification(User $user, array $eventData): void
    {
        $this->getNotificationService()->send(
            $user,
            NotificationType::EVENT_REMINDER,
            [
                'title' => 'Event Reminder',
                'body' => $eventData['name'] . ' starts ' . $eventData['time_until'],
                'subtitle' => $eventData['location'] ?? null,
                'event_id' => $eventData['id'],
                'event_name' => $eventData['name'],
                'event_date' => $eventData['date'],
                'event_time' => $eventData['time'],
                'event_location' => $eventData['location'] ?? null,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send job application notification to employer.
     */
    protected function sendJobApplicationReceivedNotification(User $employer, array $applicationData): void
    {
        $this->getNotificationService()->send(
            $employer,
            NotificationType::JOB_APPLICATION_RECEIVED,
            [
                'title' => 'New Job Application',
                'body' => $applicationData['applicant_name'] . ' applied for ' . $applicationData['job_title'],
                'job_id' => $applicationData['job_id'],
                'application_id' => $applicationData['application_id'],
                'applicant_id' => $applicationData['applicant_id'],
                'applicant_name' => $applicationData['applicant_name'],
                'job_title' => $applicationData['job_title'],
            ],
            ['database', 'push']
        );
    }

    /**
     * Send job application status notification to applicant.
     */
    protected function sendJobApplicationStatusNotification(User $applicant, string $status, array $jobData): void
    {
        $type = match($status) {
            'accepted' => NotificationType::JOB_APPLICATION_ACCEPTED,
            'rejected' => NotificationType::JOB_APPLICATION_REJECTED,
            'interview' => NotificationType::JOB_APPLICATION_INTERVIEW,
            default => null,
        };

        if (!$type) {
            return;
        }

        $title = match($status) {
            'accepted' => 'Application Accepted',
            'rejected' => 'Application Update',
            'interview' => 'Interview Scheduled',
        };

        $body = match($status) {
            'accepted' => 'Congratulations! Your application for ' . $jobData['title'] . ' has been accepted.',
            'rejected' => 'Your application for ' . $jobData['title'] . ' has been reviewed.',
            'interview' => 'You have been selected for an interview for ' . $jobData['title'],
        };

        $this->getNotificationService()->send(
            $applicant,
            $type,
            [
                'title' => $title,
                'body' => $body,
                'job_id' => $jobData['id'],
                'job_title' => $jobData['title'],
                'company' => $jobData['company'] ?? null,
                'application_id' => $jobData['application_id'] ?? null,
                'interview_date' => $jobData['interview_date'] ?? null,
            ],
            ['database', 'push', 'mail']
        );
    }

    /**
     * Send hackathon team invitation notification.
     */
    protected function sendHackathonTeamInvitationNotification(User $invitee, array $invitationData): void
    {
        $this->getNotificationService()->send(
            $invitee,
            NotificationType::HACKATHON_TEAM_INVITED,
            [
                'title' => 'Team Invitation',
                'body' => $invitationData['inviter_name'] . ' invited you to join ' . $invitationData['team_name'],
                'hackathon_id' => $invitationData['hackathon_id'],
                'team_id' => $invitationData['team_id'],
                'team_name' => $invitationData['team_name'],
                'inviter_id' => $invitationData['inviter_id'],
                'inviter_name' => $invitationData['inviter_name'],
                'hackathon_name' => $invitationData['hackathon_name'] ?? null,
            ],
            ['database', 'push']
        );
    }

    /**
     * Send batch notifications to multiple users.
     */
    protected function sendBatchNotification(array $users, NotificationType $type, array $data, array $channels = ['database', 'push']): void
    {
        $service = $this->getNotificationService();
        
        foreach ($users as $user) {
            try {
                $service->send($user, $type, $data, $channels);
            } catch (\Exception $e) {
                \Log::error('Failed to send batch notification', [
                    'user_id' => $user->id,
                    'type' => $type->value,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
