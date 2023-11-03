<?php

namespace App\Notifications;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class MakePanoFinished extends Notification
{
    use Queueable;

    private $media;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        if (!empty($content)) {
            // 存入数据库里的数据
            return [
                'id' => $this->media->id,
                'title' => '系统消息',
                'created_at' => Carbon::now(),
                'content' => "上传素材完成",
            ];
        }
    }
}
