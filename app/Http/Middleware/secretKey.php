<?php

namespace App\Http\Middleware;

use Closure;

class secretKey
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
        // Разрешаем локальные запросы (от Node.js сервера) и запросы через Cloudflare
        $cfIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null;
        $serverAddr = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        // Если запрос локальный (127.0.0.1 или localhost) - пропускаем
        if (in_array($remoteAddr, ['127.0.0.1', '::1', $serverAddr])) {
            return $next($request);
        }
        
        // Если есть Cloudflare IP и он не совпадает с сервером - блокируем
        if($cfIp && $cfIp != $serverAddr) {
            return response()->json('Invalid Request');
        }
        
        return $next($request);
    }
}
