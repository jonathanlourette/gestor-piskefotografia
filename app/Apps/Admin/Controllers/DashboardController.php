<?php

declare(strict_types=1);

namespace App\Apps\Admin\Controllers;

use App\Domains\Order\Actions\RetrieveDashboardDataAction;
use Illuminate\View\View;

class DashboardController extends BaseAdminController
{
    public function index(RetrieveDashboardDataAction $action): View
    {
        $data = $action->perform();

        return view('admin::dashboard.index', $data);
    }
}
