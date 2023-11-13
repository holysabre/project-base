<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Production;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductionService
{
    private $production;
    private $main_media;
    private $xml_path;
    private $add_medias = [];

    public function __construct(Production $production, Media $main_media)
    {
        $this->production = $production;
        $this->main_media = $main_media;
        $this->xml_path = "productions/{$this->production->id}/tour.xml";
        $this->checkDir();
    }

    public function addScene(Media $media)
    {
        $this->add_medias[] = $media;
    }

    public function saveAsXml()
    {
        $sxe = simplexml_load_file(env('QINIU_DOMAIN') . '/' . $this->main_media->xml_image->path);
        foreach ($this->add_medias as $add_media) {
            $this->addMedia($sxe, $add_media);
        }

        $is_write = file_put_contents(storage_path($this->xml_path), $sxe->asXML());
        if (!$is_write) {
            Log::error("can not write production【{$this->production->id}】 xml");
            return false;
        }
        Log::info("write production【{$this->production->id}】 xml done");

        $this->upload();

        return true;
    }

    private function checkDir()
    {
        $dir = storage_path("productions/{$this->production->id}");
        if (!is_dir($dir)) {
            mkdir($dir, '0777', true);
        }
    }

    private function upload($device = null)
    {
        switch ($device) {
            case 'qiniu':
            default:
                return $this->uploadToQiniu();
                break;
        }
    }

    private function uploadToQiniu()
    {
        $disk = Storage::disk('qiniu');

        $path = $this->xml_path;
        if (file_exists(storage_path($path))) {
            $success = $disk->put($path, file_get_contents(storage_path($path)));
            if ($success) {
                Log::info($path . ' uploaded');
                $production = $this->production;
                $rel_type = get_class($production);
                $rel_id = $production->id;
                $now = Carbon::now();
                $xml_data = [
                    'user_id' => $production->user_id,
                    'type' => 'xml',
                    'path' => $path,
                    'source' => 'qiniu',
                    'rel_type' => $rel_type,
                    'rel_id' => $rel_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $xml_image_id = DB::table('images')->insertGetId($xml_data);

                $production->xml_image_id = $xml_image_id;
                $production->save();
            } else {
                Log::error('failed to upload file to qiniu', [$success]);
                return false;
            }
        } else {
            Log::error('file not exists', [$path]);
        }

        return true;
    }

    private function addMedia(&$sxe, $add_media)
    {
        $add_sxe = simplexml_load_file(env('QINIU_DOMAIN') . '/' . $add_media->xml_image->path);

        $origin_scene = $add_sxe->scene;
        $origin_scene_attributes = $origin_scene->attributes();
        $scene = $sxe->addChild('scene');
        $scene->addAttribute('name', $origin_scene_attributes['name']);
        $scene->addAttribute('title', $origin_scene_attributes['title']);
        $scene->addAttribute('thumburl', $origin_scene_attributes['thumburl']);
        $scene->addAttribute('onstart', $origin_scene_attributes['onstart']);
        $scene->addAttribute('lat', $origin_scene_attributes['lat']);
        $scene->addAttribute('lng', $origin_scene_attributes['lng']);
        $scene->addAttribute('alt', $origin_scene_attributes['alt']);
        $scene->addAttribute('heading', $origin_scene_attributes['heading']);

        $origin_control = $origin_scene->control;
        $origin_control_attributes = $origin_control->attributes();
        $control = $scene->addChild('control');
        $control->addAttribute('bouncinglimits', $origin_control_attributes['bouncinglimits']);

        $origin_view = $origin_scene->view;
        $origin_view_attributes = $origin_view->attributes();
        $view = $scene->addChild('view');
        $view->addAttribute('hlookat', $origin_view_attributes['hlookat']);
        $view->addAttribute('vlookat', $origin_view_attributes['vlookat']);
        $view->addAttribute('fovtype', $origin_view_attributes['fovtype']);
        $view->addAttribute('fov', $origin_view_attributes['fov']);
        $view->addAttribute('maxpixelzoom', $origin_view_attributes['maxpixelzoom']);
        $view->addAttribute('fovmin', $origin_view_attributes['fovmin']);
        $view->addAttribute('fovmax', $origin_view_attributes['fovmax']);
        $view->addAttribute('limitview', $origin_view_attributes['limitview']);

        $origin_preview = $origin_scene->preview;
        $origin_preview_attributes = $origin_preview->attributes();
        $preview = $scene->addChild('preview');
        $preview->addAttribute('url', $origin_preview_attributes['url']);

        $origin_image = $origin_scene->image;
        $image = $scene->addChild('image');

        $origin_cube = $origin_image->cube;
        $origin_cube_attributes = $origin_cube->attributes();
        $cube = $image->addChild('cube');
        $cube->addAttribute('url', $origin_cube_attributes['url']);
        $cube->addAttribute('multires', $origin_cube_attributes['multires']);
    }
}
