<?php

namespace App\Http\Middleware;

use App\Support\AdminAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminSession
{
    public function __construct(private AdminAccess $adminAccess)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('login', 'login.post', 'logout')) {
            return $next($request);
        }

        $userId = $request->session()->get('usuario_id');

        if ($this->adminAccess->isAdmin($userId ? (int) $userId : null)) {
            return $next($request);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['mensaje' => 'Solo usuarios con rol Admin pueden ingresar.'], 403);
        }

        return redirect()->route('login')->with('error', 'Solo usuarios con rol Admin pueden ingresar.');
    }
}
