<html class="no-js ru" lang="ru">
    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="verification" content="58c54802a9fb9526cd0923353a34a7ae" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
        <link rel="shortcut icon" href="/image/icon.png?v={{time()}}">
        <title>{{ $settings->title }}</title>
        <meta name="description" content="{{ $settings->description }}">
        <meta name="keywords" content="{{ $settings->keywords }}">
        <link rel="preconnect" href="https://fonts.gstatic.com">
	    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@300;400;500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v={{ $settings->file_version }}">
        <script
			src="https://code.jquery.com/jquery-3.7.0.min.js"
			integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g="
			crossorigin="anonymous"
        ></script>
        <!-- VK ID SDK для OneTap - официальный код от VK -->
        <script>
            (function() {
                var script = document.createElement('script');
                script.src = 'https://unpkg.com/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js';
                script.onerror = function() {
                    console.warn('Не удалось загрузить VKID SDK с unpkg, пробуем jsdelivr...');
                    var script2 = document.createElement('script');
                    script2.src = 'https://cdn.jsdelivr.net/npm/@vkid/sdk@<3.0.0/dist-sdk/umd/index.js';
                    script2.onload = function() {
                        console.log('✅ VKID SDK загружен с jsdelivr');
                        checkVKIDSDK();
                    };
                    script2.onerror = function() {
                        console.error('Не удалось загрузить VKID SDK ни с одного CDN');
                        var script3 = document.createElement('script');
                        script3.src = 'https://unpkg.com/@vkid/sdk@latest/dist-sdk/umd/index.js';
                        script3.onload = function() {
                            console.log('✅ VKID SDK загружен с unpkg latest');
                            checkVKIDSDK();
                        };
                        document.head.appendChild(script3);
                    };
                    document.head.appendChild(script2);
                };
                script.onload = function() {
                    console.log('✅ VKID SDK загружен с unpkg');
                    checkVKIDSDK();
                };
                document.head.appendChild(script);
                
                function checkVKIDSDK() {
                    if ('VKIDSDK' in window) {
                        console.log('✅ VKIDSDK загружен');
                        window.dispatchEvent(new Event('vkidsdkloaded'));
                    } else {
                        console.warn('VKIDSDK не найден в window, ожидание...');
                        setTimeout(checkVKIDSDK, 100);
                    }
                }
            })();
        </script>

        <link rel="stylesheet" class="theme" href="#">
    </head>
    <style type="text/css">
        html {
            user-select: none;
            font-family: Rubik, sans-serif;
        }
        html:after {
            font-family: Rubik, sans-serif;
            background: #f5f5f5;

            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0.08;
            z-index: -1;
        }

        .hidden {
            display: none;
        }

        #logo {
    display: block;
    margin: 150px auto 86px;
    width: 450px;
    height: 100px;
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    background-image: url(/png_d.png?v=11);
}

        .login,
        .login-top {
            padding: 20px;
            text-align: center;
            width: 320px;

            -webkit-border-radius: 5px;
            border-radius: 5px;
            min-height: 50px;
            margin: -80px auto 30px;
            overflow: hidden;
        }

        @media (max-width: 420px) {
            #logo {
    display: block;
    margin: 30% auto auto;
    width: 280px;
    height: 70px;
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center;
    background-image: url(/png_d.png?v=11);
    padding-top: 0;
}
        }

        .login,
        .login-top {
            padding: 20px;
            text-align: center;
            -webkit-border-radius: 5px;
            border-radius: 5px;
            min-height: 50px;
            margin: -80px auto 30px;
            overflow: hidden;
            font-weight: 500;
        }

        .eas {
            -webkit-transition: all ease-out 0.4s;
            -moz-transition: all ease-out 0.4s;
            -ms-transition: all ease-out 0.4s;
            -o-transition: all ease-out 0.4s;
            transition: all ease-out 0.4s;
        }

        .default-btn.green {
            background-color: #4f6802;
        }

        .default-btn.green:hover {
            background-color: #6c8f00;
        }

        .default-btn.red {
            background-color: #8f0b28;
        }

        .default-btn.red:hover {
            background-color: #c7183d;
        }

        .default-btn {
            font-size: 18px;
            min-width: 85px;
            font-weight: 500;
            padding: 0 15px;
            height: 40px;
            display: inline-block;
            border-radius: 5px;
            color: #fff;
            line-height: 40px;
            cursor: pointer;
        }

        .button,
        .default-btn,
        a {
            text-decoration: none;
        }

        .button {
            display: inline-block;
            letter-spacing: 0.2px;
            border: 0;
            position: relative;
            max-width: 300px;
            overflow: hidden;
            font-size: 18px;
            min-width: 85px;
            font-weight: 500;
            padding: 0 15px;
            height: 40px;
            line-height: 40px;
            cursor: pointer;
            background: linear-gradient(to right bottom, #4caf50, #8bc34a);
            border-radius: 8px;
            margin-left: 5px;
            margin-right: 5px;
            color: #fff;
        }

        .button:hover {
            background: linear-gradient(to right bottom, rgba(76, 175, 80, 0.8), rgba(139, 195, 74, 0.8));
        }

        .button.red {
            background: linear-gradient(to right bottom, #f66461, #de246a);
        }

        .button.red:hover {
            background: linear-gradient(to right bottom, #ef9290, #de246a);
        }

        .default-btn:hover {
            background-color: #3f8e3d;
            color: #fff;
        }

        .default-btn.vk {
            background-color: #2561d0;
        }

        .default-btn.vk:hover {
            border: 2px #fff70 solid;
            background-color: #6989bf;
        }

        a {
            color: #888;
        }

        a:focus,
        a:hover {
            color: #fff;
        }
    </style>

    <div id="logo"></div>
   <div id="v2" class="login" style="display: none">
        Войти с помощью
        <br>
        <br>
        <div id="vkid-container"></div>
    </div>
    <div id="v3" class="login hidden">
        Войти с помощью<br />
        <br />
        <a class="eas default-btn vk" href="/auth/vkontakte"><img style="top: 6px; position: relative;" src="/assets/image/vk_white.svg" width="25" /> Авторизация</a>
    </div>
    <div id="v1">
        <div class="login-top">Вам есть 18 лет?</div>
        <div class="login-top"><a class="button" onclick="auth()">Да</a> <a class="button red" href="#">Нет</a></div>
    </div>
    <div style="display: none;" class="col-12 content cards mb-3 p-3">
        <a href="https://t.me/tkr_play">taker telegram</a>
        <h1>TAKER - Официальный сайт TAKER</h1>
    </div>
    <div style="display:none;" class="col-12 content cards mb-3 p-3">
    <p>Cервис мгновенных игр. Бонус при регистрации. Произведем выплаты за 5 минут на любую платежную систему МИР/VISA/MasterCard/СБП/TRC20/QIWI.</p>
    <p>Остерегайтесь мошенников и проверяйте адрес сайта <b><a href="taker4i.casino">TAKER</a></b>!
    </p>
    <script type="text/javascript">
        // Делаем функцию auth глобальной, чтобы она была доступна из onclick
        window.auth = function auth() {
            var v = document.getElementById("v1");
            v.style.display = "none";
            var v2 = document.getElementById("v2");
            v2.style.display = "block";
            
            // Инициализируем OneTap
            initVKOneTap();
        }

        function initVKOneTap() {
            if ('VKIDSDK' in window) {
                const VKID = window.VKIDSDK;

                VKID.Config.init({
                    app: {{ env('VKONTAKTE_CLIENT_ID') }},
                    redirectUrl: '{{ env('VKONTAKTE_REDIRECT_URI') }}',
                    responseMode: VKID.ConfigResponseMode.Callback,
                    source: VKID.ConfigSource.LOWCODE,
                    scope: '',
                });

                const oneTap = new VKID.OneTap();

                oneTap.render({
                    container: document.getElementById('vkid-container'),
                    fastAuthEnabled: false,
                    showAlternativeLogin: true
                })
                .on(VKID.WidgetEvents.ERROR, vkidOnError)
                .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function (payload) {
                    const code = payload.code;
                    const deviceId = payload.device_id;

                    VKID.Auth.exchangeCode(code, deviceId)
                        .then(vkidOnSuccess)
                        .catch(vkidOnError);
                });

                function vkidOnSuccess(data) {
                    console.log('VK Auth success:', data);
                    $.ajax({
                        url: '/auth/vkontakte/callback',
                        type: 'post',
                        data: JSON.stringify({
                            access_token: data.access_token,
                            user_id: data.user_id,
                            expires_in: data.expires_in || 0
                        }),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (response) {
                            if (response && response.auth) {
                                location.reload(true);
                            } else {
                                location.href = '/auth/vkontakte';
                            }
                        },
                        error: function() {
                            location.href = '/auth/vkontakte';
                        }
                    });
                }

                function vkidOnError(error) {
                    console.error('VK Auth error:', error);
                }
            } else {
                setTimeout(initVKOneTap, 100);
            }
        }
    </script>
</html>