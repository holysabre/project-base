<?php

namespace App\Jobs;

use App\Models\Image;
use App\Models\Media;
use App\Notifications\MakePanoFinished;
use App\Services\KrpanoService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SliceImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $media;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @param \App\Models\Media $media */
        $media = $this->media;
        $origin_file = $media->panorama_image->path;
        $dist_path = storage_path("vr/{$media->name}");
        $list_path = "vr/{$media->name}/vtour/list/";
        $krpanoService = new KrpanoService($media->name, $origin_file, $dist_path, 'qiniu');
        $krpanoService->makePano();

        dispatch(new UploadSlicedMeidaImages($media));
    }
}
