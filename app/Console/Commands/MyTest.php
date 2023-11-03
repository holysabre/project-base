<?php

namespace App\Console\Commands;

use App\Handlers\ImageUploadHandler;
use App\Models\Image;
use App\Services\KrpanoService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

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
        $uri = 'http://s339r577r.hd-bkt.clouddn.com/lr3ZQJX6CM4is3NjflY6-Zjd1ere';

        // $dir = public_path('temp');
        // if (!is_dir($dir)) {
        //     mkdir($dir);
        // }

        // $filename = $dir . '/test.png';
        // $resource = fopen($filename, 'w');
        // $client = new Client();
        // $ret = $client->get($uri, ['sink' => $resource]);

        // dump($ret);

        // $origin_file = public_path('temp/test.png');
        // $dist_path = public_path('vr/test');

        // $krpanoService = new KrpanoService('test', $origin_file, $dist_path);
        // // $ret = $krpanoService->makePano();
        // // dump($ret);
        // $krpanoService->upload();

        return Command::SUCCESS;
    }
}
