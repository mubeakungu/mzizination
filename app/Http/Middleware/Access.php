<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;
use App\Http\Middleware\EncryptCookies;
use Auth;
use App\User;

class Access
{
    protected $auth;
    protected $token;

    public function __construct()
    {
        $this->auth = Auth::User();
    }

    public function handle($request, Closure $next, $role)
    {
        if($this->auth) {
            switch($role) {
                case 'manager':
                    if(!$this->auth->is_admin && !$this->auth->is_manager) {
                        return new RedirectResponse(url('/'));
                    }
                break;
                case 'admin':
                    if(!$this->auth->is_admin) {
                        if($this->auth->is_manager) return new RedirectResponse(url('/admin/promocodes'));
                        return new RedirectResponse(url('/'));
                    }
                break;
                default:
                    return new RedirectResponse(url('/'));
                break;
            }
            return $next($request);
        }
        return new RedirectResponse(url('/'));
    }
}
