<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductService;
use App\Helpers\ResponseHelper;
use App\Helpers\FileHelper;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Http\Requests\Product\UploadRequest;

class ProductController extends Controller
{
    public function __construct(private ProductService $service) {}

    public function index()
    {
        $products = Product::paginate(15);
        return ResponseHelper::paginated($products->items(), [
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = FileHelper::storeUploadedFile($request->file('image'), 'product-images');
        }

        $product = $this->service->create($data);

        return ResponseHelper::success($product, 'Product created', 201);
    }

    public function show(string $id)
    {
        $product = Product::find($id);
        if (!$product) return ResponseHelper::error('Product not found', [], 404);
        return ResponseHelper::success($product, 'Product fetched');
    }

    public function update(UpdateRequest $request, string $id)
    {
        $product = Product::find($id);
        if (!$product) return ResponseHelper::error('Product not found', [], 404);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image_path'] = FileHelper::storeUploadedFile($request->file('image'), 'product-images');
        }

        $product->update($data);

        return ResponseHelper::success($product->refresh(), 'Product updated');
    }

    public function destroy(string $id)
    {
        $product = Product::withCount('orderItems')->find($id);
        if (!$product) return ResponseHelper::error('Product not found', [], 404);

        if ($product->order_items_count > 0) {
            return ResponseHelper::error('Cannot delete product linked to orders', [], 400);
        }

        $product->delete();
        return ResponseHelper::success([], 'Product deleted');
    }

    public function uploadExcel(UploadRequest $request)
    {
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        $rows = [];
        if (class_exists('\Maatwebsite\Excel\Facades\Excel')) {
            try {
                $array = \Maatwebsite\Excel\Facades\Excel::toArray([], $file);
                if (!empty($array) && isset($array[0])) {
                    $rows = $array[0];
                }
            } catch (\Throwable $e) {
                return ResponseHelper::error('Failed to parse Excel file: ' . $e->getMessage(), [], 400);
            }
        } elseif (in_array($ext, ['xls', 'xlsx']) && class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();
            } catch (\Throwable $e) {
                return ResponseHelper::error('Failed to parse spreadsheet: ' . $e->getMessage(), [], 400);
            }
        } elseif (in_array($ext, ['csv', 'txt']) || empty($rows)) {
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');
            if ($handle === false) return ResponseHelper::error('Unable to read file', [], 400);

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $row;
            }
            fclose($handle);
        } else {
            return ResponseHelper::error('To import .xlsx files please install maatwebsite/excel or phpoffice/phpspreadsheet', [], 400);
        }

        $result = $this->service->importRows($rows);
        return ResponseHelper::success($result, 'Upload processed');
    }
}
