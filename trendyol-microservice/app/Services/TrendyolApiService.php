<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class TrendyolApiService
{
    protected $apiKey;
    protected $apiSecret;
    protected $supplierId;

    public function __construct()
    {
        $this->apiKey = env('TRENDYOL_API_KEY');
        $this->apiSecret = env('TRENDYOL_API_SECRET');
        $this->supplierId = env('TRENDYOL_SUPPLIER_ID');
    }

    public function getProducts($page = 0, $size = 100)
    {
        $url = "https://api.trendyol.com/sapigw/suppliers/{$this->supplierId}/products";
        

        $response = Http::withBasicAuth($this->apiKey, $this->apiSecret)
            ->retry(3, 1000)
            ->timeout(30)
            ->get($url, [
                'page' => $page,
                'size' => $size,
            ]);
            

        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }
}
