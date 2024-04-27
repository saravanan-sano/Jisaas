<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryObserver
{
    public function saving(Category $category)
    {
        $company = company();


$category->company_id = company()->id;
        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $category->company_id = company()->id;
        }
    }
}
