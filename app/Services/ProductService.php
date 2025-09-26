<?php

namespace App\Services;

use App\Models\Product;
use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use App\Helpers\ResponseHelper;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->refresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function importRows(array $rows): array
    {
        $header = null;
        $inserted = 0;
        $errors = [];

        foreach ($rows as $rowIndex => $row) {
            if ($rowIndex === 0) {
                $header = array_map('trim', $row);
                continue;
            }

            if (count($header) !== count($row)) {
                $errors[] = ['row' => $rowIndex + 1, 'errors' => 'Column count mismatch'];
                continue;
            }

            $data = array_combine($header, $row);

            if (empty($data['name']) || empty($data['sku']) || !is_numeric($data['price']) || !is_numeric($data['quantity'])) {
                $errors[] = ['row' => $rowIndex + 1, 'errors' => 'Missing or invalid required fields'];
                continue;
            }

            if (Product::where('sku', $data['sku'])->exists()) {
                $errors[] = ['row' => $rowIndex + 1, 'errors' => 'SKU already exists'];
                continue;
            }

            $productData = [
                'name' => $data['name'],
                'sku' => $data['sku'],
                'price' => number_format((float)$data['price'], 2, '.', ''),
                'quantity' => (int)$data['quantity'],
            ];

            if (!empty($data['image_url'])) {
                $ext = pathinfo(parse_url($data['image_url'], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $fileName = 'product-' . Str::random(8) . '.' . $ext;
                $storagePath = 'product-images/' . $fileName;
                $url = FileHelper::downloadRemoteFileToStorage($data['image_url'], $storagePath);
                if ($url) $productData['image_path'] = $url;
                else $errors[] = ['row' => $rowIndex + 1, 'errors' => 'Image fetch failed'];
            }

            Product::create($productData);
            $inserted++;
        }

        return ['inserted' => $inserted, 'errors' => $errors];
    }
}
