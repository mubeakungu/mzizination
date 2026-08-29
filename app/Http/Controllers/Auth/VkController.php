<?php

namespace App\Http\Controllers\Auth;

use Socialite;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use App\User;

use DB;
use Auth;

class VkController extends Controller {

    public function __construct() {
        parent::__construct();
    }

    public function login($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback($provider = null, Request $request)
    {
        // Если provider не указан (маршрут /vk/auth/callback), устанавливаем 'vkontakte'
        if (empty($provider)) {
            $provider = 'vkontakte';
        }
        
        // Обработка данных от VK One Tap (SuperAppKit)
        $requestData = $request->all();
        
        \Log::info('VK Callback Request', [
            'method' => $request->method(),
            'isJson' => $request->isJson(),
            'hasCode' => isset($requestData['code']),
            'requestData' => $requestData
        ]);
        
        // Проверяем, это обычный OAuth redirect с code параметром
        if ($request->method() === 'GET' && isset($requestData['code'])) {
            // Обычный OAuth flow через Socialite (VK перенаправил с code)
            try {
                $socialiteUser = Socialite::driver($provider)->user();
                // Для обычного OAuth используем данные из Socialite
                $userData = [
                    'id' => $socialiteUser->getId(),
                    'first_name' => $socialiteUser->getName(),
                    'last_name' => '',
                    'photo_200' => $socialiteUser->getAvatar(),
                    'email' => $socialiteUser->getEmail()
                ];
                $result = $this->createOrUpdateUser((object) $userData, $provider, $request);
                
                // Проверяем, что пользователь действительно авторизован
                if (isset($result['auth']) && $result['auth']) {
                    \Log::info('VK Auth Success - User logged in', [
                        'user_id' => Auth::id(),
                        'is_authenticated' => Auth::check(),
                        'session_id' => session()->getId()
                    ]);
                    
                    // Явно сохраняем сессию и проверяем
                    session()->save();
                    
                    \Log::info('VK Auth - Before redirect', [
                        'user_id' => Auth::id(),
                        'is_authenticated' => Auth::check(),
                        'session_id' => session()->getId(),
                        'session_driver' => config('session.driver'),
                        'session_path' => config('session.path')
                    ]);
                    
                    // Простой редирект с явной установкой cookie
                    $redirect = redirect('/')->with('success', 'Авторизация успешна');
                    
                    // Явно устанавливаем cookie сессии
                    $cookie = cookie(
                        config('session.cookie'),
                        session()->getId(),
                        config('session.lifetime'),
                        config('session.path'),
                        config('session.domain'),
                        config('session.secure', true),
                        config('session.http_only', true),
                        false, // raw
                        config('session.same_site', 'lax')
                    );
                    
                    return $redirect->withCookie($cookie);
                }
                
                \Log::warning('VK Auth - createOrUpdateUser returned no auth', ['result' => $result]);
                return response()->json($result);
            } catch (\Exception $e) {
                \Log::error('VK Socialite Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect('/')->with('error', 'Ошибка авторизации');
            }
        }
        
        // Если данные приходят от One Tap (JSON от VKID SDK или POST)
        if ($request->isJson() || $request->method() === 'POST') {
            // Официальный VKID SDK отправляет access_token после обмена на клиенте
            if (isset($requestData['access_token']) && isset($requestData['user_id'])) {
                // Формат от VKID.Auth.exchangeCode() на клиенте
                \Log::info('VK OneTap: Received access_token from client exchange', [
                    'user_id' => $requestData['user_id'],
                    'has_expires_in' => isset($requestData['expires_in'])
                ]);
                
                $accessToken = $requestData['access_token'];
                $userId = $requestData['user_id'];
                
                // Получаем данные пользователя
                $user = $this->getUserByAccessToken($accessToken, $userId);
                
                if (!isset($user['response'][0])) {
                    \Log::error('VK User Error: No user data', [
                        'user_response' => $user
                    ]);
                    return response()->json(['error' => 'Ошибка получения данных пользователя'], 400);
                }
                
                return response()->json($this->createOrUpdateUser((object) $user['response'][0], $provider, $request));
            }
            // Обратная совместимость: старый формат с code и device_id (обмен на сервере)
            elseif (isset($requestData['code']) && isset($requestData['device_id'])) {
                // Официальный формат VKID SDK OAuth 2.1 - обмен на сервере (не рекомендуется)
                \Log::info('VK OneTap: Received code and device_id for server-side exchange', [
                    'code' => substr($requestData['code'], 0, 20) . '...',
                    'device_id' => $requestData['device_id']
                ]);
                
                $tokenResponse = $this->exchangeCodeForToken($requestData['code'], $requestData['device_id']);
                
                \Log::info('VK Code Exchange Response', [
                    'has_access_token' => isset($tokenResponse['access_token']),
                    'has_user_id' => isset($tokenResponse['user_id']),
                    'error' => $tokenResponse['error'] ?? null
                ]);
                
                if (!isset($tokenResponse['access_token'])) {
                    \Log::error('VK Code Exchange Error', [
                        'response' => $tokenResponse
                    ]);
                    return response()->json(['error' => 'Ошибка обмена кода на токен'], 400);
                }
                
                $token = [
                    'response' => [
                        'access_token' => $tokenResponse['access_token'],
                        'user_id' => $tokenResponse['user_id']
                    ]
                ];
            }
            // Обратная совместимость со старым форматом SuperAppKit
            elseif (isset($requestData['payload']) && isset($requestData['payload']['token'])) {
                // Формат с payload (старый формат SuperAppKit)
                if (!isset($requestData['payload']['uuid'])) {
                    \Log::error('VK OneTap: Missing uuid in payload', [
                        'request' => $requestData
                    ]);
                    return response()->json(['error' => 'Отсутствуют обязательные данные авторизации'], 400);
                }
                $token = $this->getAccessBySilent($requestData);
            } elseif (isset($requestData['token']) && isset($requestData['uuid'])) {
                // Формат напрямую от SuperAppKit (старый формат)
                $token = $this->getAccessBySilent([
                    'payload' => [
                        'token' => $requestData['token'],
                        'uuid' => $requestData['uuid']
                    ]
                ]);
            } else {
                \Log::error('VK OneTap: Invalid data format', [
                    'request' => $requestData,
                    'keys' => array_keys($requestData)
                ]);
                return response()->json(['error' => 'Неверный формат данных авторизации'], 400);
            }
        } else {
            // Обычный OAuth flow через Socialite
            try {
                $socialiteUser = Socialite::driver($provider)->user();
                // Для обычного OAuth используем данные из Socialite
                $userData = [
                    'id' => $socialiteUser->getId(),
                    'first_name' => $socialiteUser->getName(),
                    'last_name' => '',
                    'photo_200' => $socialiteUser->getAvatar(),
                    'email' => $socialiteUser->getEmail()
                ];
                return response()->json($this->createOrUpdateUser((object) $userData, $provider, $request));
            } catch (\Exception $e) {
                \Log::error('VK Socialite Error', [
                    'error' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Ошибка авторизации'], 400);
            }
        }

        if(!isset($token['response']['access_token'])) {
            \Log::error('VK Auth Error: No access_token', [
                'request' => $requestData,
                'token_response' => $token
            ]);
            return response()->json(['error' => 'Ошибка получения токена доступа'], 400);
        }

        $user = $this->getUserByAccessToken(
            $token['response']['access_token'], 
            $token['response']['user_id']
        );

        if (!isset($user['response'][0])) {
            \Log::error('VK User Error: No user data', [
                'user_response' => $user,
                'token_response' => $token
            ]);
            return response()->json(['error' => 'Ошибка получения данных пользователя'], 400);
        }

        return response()->json($this->createOrUpdateUser((object) $user['response'][0], $provider, $request));
    }

    public function createOrUpdateUser($user, $provider, Request $request = null) 
    {
        $candidate = User::where('vk_id', $user->id)->first();
        $username = $user->first_name . ' ' . $user->last_name;

        if(!$candidate) {
            @$ref = User::where('unique_id', Session::get('ref'))->first();
            $ref_use = 0;

            if($ref) {
                $ref_use = $ref->id;
            }

            $user = User::create([
                'unique_id' => \Str::random(8),
                'username' => $username,
                'avatar' => $user->photo_200,
                'vk_id' => $user->id,
                'created_ip' => $this->getIp(),
                'used_ip' => $this->getIp(),
                'referral_use' => $ref_use
            ]);

            Auth::login($user, true);
            
            // Регенерируем сессию для безопасности
            if ($request) {
                $request->session()->regenerate();
            } else {
                session()->regenerate();
            }
            
            \Log::info('VK Auth - New user created and logged in', [
                'user_id' => $user->id,
                'vk_id' => $user->vk_id,
                'is_authenticated' => Auth::check(),
                'session_id' => $request->session()->getId()
            ]);
            
            return ['auth' => true];
        }

        $candidate->update([
            'used_ip' => $this->getIp()
        ]);

        Auth::login($candidate, true);
        
        // Регенерируем сессию для безопасности
        if ($request) {
            $request->session()->regenerate();
        } else {
            session()->regenerate();
        }
        
        \Log::info('VK Auth - Existing user logged in', [
            'user_id' => $candidate->id,
            'vk_id' => $candidate->vk_id,
            'is_authenticated' => Auth::check(),
            'session_id' => session()->getId()
        ]);
        
        return ['auth' => true];
    }
}
