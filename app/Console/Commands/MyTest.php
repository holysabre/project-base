<?php

namespace App\Console\Commands;

use App\Handlers\ImageUploadHandler;
use App\Models\Image;
use Illuminate\Console\Command;

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
        $image = Image::find(1);
        $origin_file = public_path($image->path);

        $imageUploadHelper = new ImageUploadHandler();

        $ret = $imageUploadHelper->uploadToQiniu($image->path, $origin_file);

        dump($ret);

        // $pkgPath = storage_path('krpano/krpano-1.21');

        // $filePath = storage_path('vr/test');

        // $cmd = (new \Panliang\PhpKrpano\Command\MakePano())
        //     ->setConfig("{$pkgPath}templates/vtour-multires.config") //设置配置文件
        //     ->setTilePath("{$filePath}/vtour/list/l%Al[_c]_%Av_%Ah.jpg") //设置切片规则
        //     //    ->setThumbPath("{$filePath}/thumb.jpg") //设置主题图生成路径
        //     //    ->setXmlPath("{$filePath}/tour.xml") // 设置xml文件生成路径
        //     ->setPreviewPath("{$filePath}/vtour/list/preview.jpg") //设置预览图生成路径
        //     ->setTempCubePath("{$filePath}/tempcubepath")
        //     ->setThumbSize(430) // 设置主题图尺寸
        //     ->setImgPath($origin_file) //需要生成的全景球面图路径
        //     ->setOutput($filePath . "/vtour"); //生成目录

        // //生成vr作品
        // $data =  (new \Panliang\PhpKrpano\ExecShell(
        //     (new \Panliang\PhpKrpano\KrpanoToolsScripts("$pkgPath/krpanotools"))
        //         ->setCmd($cmd)
        // ))->exec()->echo();

        // var_dump($data);

        return Command::SUCCESS;
    }
}
