<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\LogBatch;

class ActivityLogBatch
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Bắt đầu một nhóm log (Batch) cho mỗi request
        LogBatch::startBatch();

        $response = $next($request);

        // Kết thúc nhóm log
        LogBatch::endBatch();

        return $response;
    }
}
