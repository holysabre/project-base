<?php

namespace App\Console\Commands;

use App\Handlers\ImageUploadHandler;
use App\Handlers\QiniuHandler;
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
        $qiniuHanlder = new QiniuHandler();

        $key = 'lr3ZQJX6CM4is3NjflY6-Zjd1ere';
        $data = $qiniuHanlder->getMetadataFromQiniu($key);

        dump($data);

        return Command::SUCCESS;
    }
}
