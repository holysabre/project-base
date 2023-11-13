<?php

namespace App\Jobs;

use App\Models\Production;
use App\Services\ProductionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MakeProductionXml implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $production;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Production $production)
    {
        $this->production = $production;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $production = $this->production;
        $production->load(['production_media', 'production_media.media']);
        $main_media = $production->production_media->where('is_main', 1)->first();
        $productionService = new ProductionService($production, $main_media->media);
        foreach ($production->production_media as $production_media) {
            $productionService->addScene($production_media->media);
        }
        $productionService->saveAsXml();
    }
}
