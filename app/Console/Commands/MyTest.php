<?php

namespace App\Console\Commands;

use App\Handlers\ImageUploadHandler;
use App\Models\Image;
use App\Models\Media;
use App\Notifications\MakePanoFinished;
use App\Services\KrpanoService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\ArrayToXml\ArrayToXml;

class MyTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:my';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My Test';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $media = Media::query()->find(14);

        $origin_file = $media->panorama_image->path;
        $dist_path = storage_path("vr/{$media->name}");
        $list_path = "vr/{$media->name}/vtour/list/";
        $xml_path = storage_path("vr/{$media->name}/vtour/tour.xml");

        $krpanoService = new KrpanoService($media->name, $origin_file, $dist_path, 'qiniu');

        $ret = $krpanoService->makePano();
        $data = $krpanoService->upload();

        $data = [];
        $files = getFilesFromDir(storage_path($list_path));
        foreach ($files as $file) {
            foreach ($file as $name => $path) {
                $data['list'][] = 'vr/' . $media->name . '/vtour/list/' . $name;
            }
        }
        $data['thumb'] = 'vr/' . $media->name . '/vtour/thumb.jpg';

        $rel_type = get_class($media);
        $rel_id = $media->id;
        $now = Carbon::now();

        dump($data);

        DB::beginTransaction();
        try {
            $insert_data = [];
            foreach ($data['list'] as $row) {
                $insert_data[] = [
                    'user_id' => $media->user_id,
                    'type' => 'slice',
                    'path' => $row,
                    'source' => 'qiniu',
                    'rel_type' => $rel_type,
                    'rel_id' => $rel_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            $chunck = collect($insert_data);
            $chunks = $chunck->chunk(10);
            $chunks->all();
            foreach ($chunks as $val) {
                DB::table('images')->insert($val->toArray());
            }

            $thumb_data = [
                'user_id' => $media->user_id,
                'type' => 'thumb',
                'path' => $data['thumb'],
                'source' => 'qiniu',
                'rel_type' => $rel_type,
                'rel_id' => $rel_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $thumb_image_id = DB::table('images')->insertGetId($thumb_data);

            $media->is_slice = 1;
            $media->thumb_image_id = $thumb_image_id;
            $media->dist_path = $list_path;
            $media->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        $user = $media->user;
        $user->notify(new MakePanoFinished($media));

        return Command::SUCCESS;
    }
}
