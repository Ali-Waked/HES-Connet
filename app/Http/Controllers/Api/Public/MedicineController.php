<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Controller;
use App\Models\Medicine;

class MedicineController extends Controller
{
    public function lookup(): Medicine
    {
        return Medicine::select(['uuid', 'name'])->get();
    }
}
