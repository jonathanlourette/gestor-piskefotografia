<?php

declare(strict_types=1);

namespace App\Apps\Site\Controllers;

use App\Domains\Order\Actions\RetrieveOrderAction;
use App\Support\Exceptions\BusinessException;
use Illuminate\View\View;

class TrackingController extends BaseSiteController
{
    public function show(string $uuid, RetrieveOrderAction $action): View
    {
        try {
            $order = $action->setData(['uuid' => $uuid])->perform();

            return view('site::tracking.show', [
                'order' => $order,
            ]);
        } catch (BusinessException $e) {
            abort(404);
        }
    }
}
