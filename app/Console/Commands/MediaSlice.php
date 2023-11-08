<?php

namespace App\Console\Commands;

use App\Jobs\SliceImage;
use App\Models\Media;
use Illuminate\Console\Command;

class MediaSlice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:slice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Meida Slice';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $media_id = $this->ask('input media_id');

        $media = Media::query()->find($media_id);

        dispatch(new SliceImage($media));


        return Command::SUCCESS;
    }
}
