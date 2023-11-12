<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use App\Jobs\UploadSlicedMeidaImages;

class RefreshMediaSceneName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:refresh-scene-name';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Meida Scene Name';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $media_id = $this->ask('input media_id');

        $media = Media::query()->find($media_id);

        $xml_path = storage_path("vr/{$media->name}/vtour/tour.xml");

        $xml = simplexml_load_file($xml_path);
        $data = json_decode(json_encode($xml), 1);
        $scene_name = $data['scene']['@attributes']['name'];

        $media->scene_name = $scene_name;
        $media->save();

        return Command::SUCCESS;
    }
}
