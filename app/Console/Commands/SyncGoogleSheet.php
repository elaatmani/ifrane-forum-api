<?php

namespace App\Console\Commands;

use App\Models\GoogleSheet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Repositories\Eloquent\OrderRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\GoogleSheetRepository;
use App\Http\Controllers\Google\GoogleSheetController;

class SyncGoogleSheet extends Command
{
    public $sheetRepository;
    public $orderRepository;
    public $productRepository;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-google-sheet';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch new orders from google sheet';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        // return;
        $url = route('sheets.sync'); // Use route helper to generate URL
        $response = Http::get($url);
        $this->info('Sheets has been synced successfully!');
        $this->info(json_encode($response->json()));

        
    }
}
