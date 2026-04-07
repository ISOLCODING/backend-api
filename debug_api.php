<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

use App\Models\Category;
use App\Http\Resources\CategoryResource;
use App\Traits\ApiResponse;

class Test { use ApiResponse; public function run() {
    $categories = Category::orderBy('name')->get();
    return $this->successResponse(
        CategoryResource::collection($categories),
        'Daftar kategori berhasil diambil.'
    );
}}

$test = new Test();
header('Content-Type: application/json');
echo $test->run()->getContent();
