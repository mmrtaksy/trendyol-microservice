<?php
namespace App\Jobs;

use App\Services\TrendyolApiService;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchTrendyolProducts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trendyolApiService;
    public $timeout = 600; // 10 dakika (600 saniye)

    /**
     * FetchTrendyolProducts constructor.
     *
     * @param TrendyolApiService $trendyolApiService
     */
    public function __construct()
    {
        $this->trendyolApiService = new TrendyolApiService();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $startTime = now();
        Log::info('Product fetching started at: ' . $startTime);

        $page = 0;
        $totalProducts = 0;

        do {
            $products = $this->trendyolApiService->getProducts($page);

            if (!$products) {
                Log::error('Failed to fetch products from API on page ' . $page);
                return;
            }
 

            foreach ($products['content'] as $productData) {
                if(isset($productData['stockCode'])){
                    Product::updateOrCreate(
                        ['sku' => $productData['stockCode']],
                        [
                            'name' => $productData['title'],
                            'price' => $productData['salePrice'],
                            'stock' => $productData['quantity'],
                            'description' => $productData['description'] ?? '',
                        ]
                    );
                }
            }

            $totalProducts += count($products['content']);
            $page++;
        } while ($totalProducts < $products['totalElements']);

        $endTime = now();
        Log::info('Product fetching ended at: ' . $endTime);
        Log::info('Total Products Fetched: ' . $totalProducts);
        Log::info('Total time taken: ' . $startTime->diffInSeconds($endTime) . ' seconds');
    }
}
