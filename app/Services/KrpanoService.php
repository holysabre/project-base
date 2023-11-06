<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Handlers\QiniuHandler;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class KrpanoService
{
    private $pkg_path;
    private $folder;
    private $origin_file;
    private $dist_path;
    private $temp_dir;
    private $source;

    public function __construct($folder, $origin_file, $dist_path, $source = null)
    {
        $this->pkg_path = env('KRPANO_PKG_PATH');
        $this->folder = $folder;
        $this->origin_file = $origin_file;
        $this->dist_path = $dist_path;
        $this->temp_dir = storage_path('temp');
        $this->source = $source;
        $this->downloadOriginFile();
    }

    /**
     * make pano by use origin picture
     * @param $origin_file 
     * @param $dist_path
     * @return mixed $data
     */
    public function makePano()
    {
        $dist_path = $this->dist_path;

        Log::info('==makePano-info==', [$this->pkg_path, $this->origin_file, $dist_path]);
        // dd($this->origin_file, file_exists($this->origin_file));

        $cmd = (new \Panliang\PhpKrpano\Command\MakePano())
            ->setConfig("{$this->pkg_path}templates/vtour-multires.config") //设置配置文件
            ->setTilePath("{$dist_path}/vtour/list/l%Al[_c]_%Av_%Ah.jpg") //设置切片规则
            //    ->setThumbPath("{$dist_path}/thumb.jpg") //设置主题图生成路径
            //    ->setXmlPath("{$dist_path}/tour.xml") // 设置xml文件生成路径
            ->setPreviewPath("{$dist_path}/vtour/list/preview.jpg") //设置预览图生成路径
            ->setTempCubePath("{$dist_path}/tempcubepath")
            ->setThumbSize(430) // 设置主题图尺寸
            ->setImgPath($this->origin_file) //需要生成的全景球面图路径
            ->setOutput($dist_path . "/vtour"); //生成目录

        Log::info('==makePano-cmd==', [$cmd]);

        //生成vr作品
        $data =  (new \Panliang\PhpKrpano\ExecShell(
            (new \Panliang\PhpKrpano\KrpanoToolsScripts($this->pkg_path . "krpanotools"))
                ->setCmd($cmd)
        ))->exec()->echo();

        Log::info('==makePano-data==', [$data]);

        return $data;
    }

    /**
     * restore picture by use silce pictures
     * @param $origin_file 
     * @param $dist_path
     * @return mixed $data
     */
    public function cubeToSphere()
    {
        // 根据切片获取6张小图
        $sixImage = (new \Panliang\PhpKrpano\Helpers\VrSliceToSixImg())->getSixImage($this->dist_path);

        // 设置CubeToSphere命令
        $cmd = (new \Panliang\PhpKrpano\Command\CubeToSphere())
            ->setImageList($sixImage)
            ->setJpegQuality(90) //设置图片质量 0-100
            //    ->setQuit() // 设置直接退出
            //    ->setSize("1080x1090") //设置图片长宽
            //    ->setJpegSubSamp() //设置图片颜色采样 444,420,420,411，default=444
            //    ->setJpegOptimize()//是使用huffman算法压缩图片，true或false，default=true。
            //    ->setTiffCompress()//设置TIFF压缩方法，none，lzw, zip或jpeg, default=lzw。
            //        ->setTempDir("") // 为临时文件设置自定义目录。
            ->setOutput($this->origin_file); //输出指定图片

        //切片合成全景图
        $data =  (new \Panliang\PhpKrpano\ExecShell(
            (new \Panliang\PhpKrpano\KrpanoToolsScripts($this->pkg_path . "krpanotools"))
                ->setCmd($cmd)
        ))->exec()->echo();

        return $data;
    }

    public function upload($device = null)
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
        $list_path = $this->dist_path . '/vtour/list/';
        $files = getFilesFromDir($list_path);

        $data = [
            'list' => [],
            'thumb' => '',
        ];

        $disk = Storage::disk('qiniu');

        foreach ($files as $file) {
            foreach ($file as $filename => $filepath) {
                $path = 'vr/' . $this->folder . '/vtour/list/' . $filename;
                $success = $disk->put($path, file_get_contents($filepath));
                if ($success) {
                    Log::info($path . ' uploaded');
                    $data['list'][] = $path;
                }
            }
        }

        $thumb_folder_name = $this->folder . '.tiles';
        $path = 'vr/' . $this->folder . '/vtour/panos/' . $thumb_folder_name . '/thumb.jpg';
        $thumb_filename = 'vr/' . $this->folder . '/vtour/thumb.jpg';
        if (file_exists(storage_path($path))) {
            $success = $disk->put($thumb_filename, file_get_contents(storage_path($path)));
            if ($success) {
                Log::info($path . ' uploaded');
                $data['thumb'] = $thumb_filename;
            }
        }

        return $data;
    }

    private function downloadOriginFile()
    {
        if (!is_dir($this->temp_dir)) {
            mkdir($this->temp_dir);
        }

        if ($this->source == 'qiniu') {
            $qiniuHanlder = new QiniuHandler();
            $data = $qiniuHanlder->getMetadataFromQiniu($this->origin_file);
            if (empty($data[0])) {
                throw new CustomException('无法获取七牛云文件元数据');
            }
            switch ($data[0]['mimeType']) {
                case 'image/png':
                    $ext = 'png';
                    break;
                case 'image/jpeg':
                case 'image/jpg':
                default:
                    $ext = 'jpg';
                    break;
            }

            $filename = $this->temp_dir . '/' . $this->origin_file . '.' . $ext;
            $this->origin_file = env('QINIU_DOMAIN') . '/' . $data[0]['hash'];
        } else {
            if (strpos($this->origin_file, 'http') !== false) {
                $name = getFilenameByPath($this->origin_file);
                $filename = $this->temp_dir . '/' . $name;
            } else {
                $filename = $this->origin_file;
            }
        }

        if (!file_exists($filename)) {
            $resource = fopen($filename, 'w');
            $client = new Client();
            $ret = $client->get($this->origin_file, ['sink' => $resource]);
            if ($ret->getStatusCode() != 200) {
                fclose($resource);
                throw new CustomException('无法下载素材文件');
            }
            fclose($resource);
        }
        $this->origin_file = $filename;
    }
}
