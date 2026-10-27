<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCustomer
{
    /**
     * Hanya mengizinkan user dengan role customer (2) mengakses route.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role == 2) {
            return $next($request);
        }

        return redirect('/auth/redirect')->with('msgError', 'Anda harus login sebagai customer');
    }
}
