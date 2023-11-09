<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use App\Jobs\UploadSlicedMeidaImages;

class UploadMediaSlice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:upload-slice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload Meida Slice';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $media_id = $this->ask('input media_id');

        $media = Media::query()->find($media_id);

        dispatch(new UploadSlicedMeidaImages($media));

        return Command::SUCCESS;
    }
}
