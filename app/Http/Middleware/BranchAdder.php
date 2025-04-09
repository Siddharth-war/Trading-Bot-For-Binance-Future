<?php

namespace App\Http\Middleware;

use App\Model\Branch;
use Closure;
use Illuminate\Support\Facades\Config;

class BranchAdder
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        Config::set('branch_id', $request->header('branch-id') );

        $branch = Branch::where('id', $request->header('branch_id'))->first();
        if (!isset($branch)) {
            $errors = [];
            $errors[] = ['code' => 'auth-001', 'message' => 'Branch not match.'];
            return response()->json(['errors' => $errors], 401);
        }

        return $next($request);
    }
}
