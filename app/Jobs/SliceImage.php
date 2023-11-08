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
        $krpanoService->upload();

        $data = [];
        $files = getFilesFromDir(storage_path($list_path));
        foreach ($files as $file) {
            foreach ($file as $name => $path) {
                $data['list'][] = 'vr/' . $media->name . '/vtour/list/' . $name;
            }
        }
        $data['thumb'] = 'vr/' . $media->name . '/vtour/thumb.jpg';
        $data['xml'] = 'vr/' . $media->name . '/vtour/tour.xml';

        $rel_type = get_class($media);
        $rel_id = $media->id;
        $now = Carbon::now();

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

            $xml_data = [
                'user_id' => $media->user_id,
                'type' => 'xml',
                'path' => $data['xml'],
                'source' => 'qiniu',
                'rel_type' => $rel_type,
                'rel_id' => $rel_id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $xml_image_id = DB::table('images')->insertGetId($xml_data);

            $media->is_slice = 1;
            $media->thumb_image_id = $thumb_image_id;
            $media->xml_image_id = $xml_image_id;
            $media->dist_path = $list_path;
            $media->save();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

        $user = $media->user;
        $user->notify(new MakePanoFinished($media));
    }
}
