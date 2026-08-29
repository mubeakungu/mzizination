
<html lang="ru">
<head id="head">
<meta charset="utf-8">
<link rel="shortcut icon" href="/image/icon.png">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,user-scalable=no,viewport-fit=cover">
<meta content name="description">
<link href="/manifest.json" rel="manifest">
<meta content="notranslate" name="google">
<meta content="no-referrer" name="referrer">
<meta content="yes" name="apple-mobile-web-app-capable">
<meta content="yes" name="mobile-web-app-capable">
<meta content="Taker" name="apple-mobile-web-app-title">
<meta content="black" name="apple-mobile-web-app-status-bar-style">
<meta content="Taker" name="application-name">
<meta content="#fff" name="msapplication-TileColor">
<meta content="#fff" name="theme-color">
<title>Taker</title>
<style>
            .play-body-step:before,.play-header-link:active,.play-header-link:hover{background:#2a75ff;color:#fff}body{background-color:#141b34!important;color:#ffffffe0!important;font-family:Rubik,sans-serif;background-image:url(/image/bg_.png?1);margin:0}.pwa-hint{display:flex;flex-direction:column;width:100%;margin:0 auto}.play-header{display:flex;justify-content:space-between;align-items:center;padding:15px 15px;background:#25263a}.play-header-title{display:flex;align-items:center;color:#fff;font-weight:700;font-size:14px;line-height:1;margin:0 auto}.play-body-step:before,.play-header-link{font-size:12px;line-height:1;display:flex;border-radius:4px}.play-header-title img{display:block;width:20px;height:auto;margin-right:10px}.play-header-link{align-items:center;padding:10px 15px;color:#8e91ab;background:#3e415a}.play-header-link img{display:block;width:7px;height:auto;margin-bottom:1px;margin-right:6px;transform:rotate(180deg);opacity:.4}.play-header-link:active img,.play-header-link:hover img{opacity:1}.play-body{padding:0 15px;display:flex;flex-direction:column}.play-body-step{position:relative;margin-top:25px;padding-left:30px}.play-body-step:before{position:absolute;content:attr(data-step);top:0;left:0;margin-top:2px;align-items:center;justify-content:center;width:20px;height:20px;font-weight:700}.play-body-step-title{display:flex;align-items:center;margin-bottom:5px;font-weight:500;font-size:15px;color:#c3c5da;min-height:24px}.play-body-step-title-icon{display:flex;align-items:center;justify-content:center;width:22px;height:22px;margin-left:6px;background:#3e4159;border-radius:4px}.play-body-step-title-icon img{display:block;width:14px;height:14px}.play-body-step-title strong{color:#fff}.play-body-step-text{color:#7c7f9e;font-size:14px;line-height:20px}.play-body-step-text strong{color:#b0b3d4}.play-body-example{position:relative;width:100%;padding-top:100%;background:#3e4158;border-radius:10px;overflow:hidden}.play-body-example-wrapper{width:100%;max-width:400px;margin-top:25px;margin-bottom:80px}.play-body-example>img{position:absolute;top:0;left:0;display:block;width:100%;height:100%}
        </style>
</head>
<body>
<div class="pwa-hint" id="pwa"></div>
<script src="/cdn-cgi/scripts/7d0fa10a/cloudflare-static/rocket-loader.min.js" data-cf-settings="58fadd95e4db10b6cd1aca99-|49" defer></script></body>
<script type="58fadd95e4db10b6cd1aca99-text/javascript">;
        function getMobileOperatingSystem() {
             
            var userAgent = navigator.userAgent || navigator.vendor || window.opera;

            if (/android/i.test(userAgent)) {
                return "Android";
            }
 
            if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
                return "iOS";
            }

            return "unknown";
        }
        if(getMobileOperatingSystem() == 'unknown' || location.href !== 'https://taker4i.casino/play'){
            var x = document.getElementById("head");
                x.remove(x.selectedIndex);
            document.getElementById('pwa').innerHTML = '404 Not Found';

        }else if(getMobileOperatingSystem() == 'iOS'){
            document.getElementById('pwa').innerHTML = '<div class="play-header">\
            <div class="play-header-title"><img src="/image/ios.png" alt="iOS"> <span>Taker для iOS</span></div>\
            </div>\
            <div class="play-body">\
                <div data-step="1" class="play-body-step">\
                    <div class="play-body-step-title"><span>Откройте браузер <strong>Safari</strong></span></div>\
                    <div class="play-body-step-text"><span>Если вы находитесь в каком либо другом браузере, откройте данную страницу в браузере Safari</span></div>\
                </div>\
                <div data-step="2" class="play-body-step">\
                    <div class="play-body-step-title"><span>Нажмите <strong>Поделиться</strong></span>\
                        <div class="play-body-step-title-icon"><img src="/image/pwa-step-share.svg" alt="Share"></div>\
                    </div>\
                    <div class="play-body-step-text"><span>Нажмите на кнопку <strong>Поделиться</strong>, которая находится внизу экрана, после чего откроется окно выбора параметров</span></div>\
                </div>\
                <div data-step="3" class="play-body-step">\
                    <div class="play-body-step-title"><span>Нажмите <strong>На экран «Домой»</strong></span>\
                        <div class="play-body-step-title-icon"><img src="/image/pwa-step-home.svg" alt="Home"></div>\
                    </div>\
                    <div class="play-body-step-text"><span>Нажмите на пункт в меню <strong>На экран «Домой»</strong>, потом нажмите <strong>Добавить</strong>.</span></div>\
                </div>\
            </div>'
        }else if(getMobileOperatingSystem() == 'Android'){
            document.getElementById('pwa').innerHTML = '<div class="play-header">\
            <div class="play-header-title"><img src="/image/androids.png" alt="iOS"> <span>Taker для Android</span></div>\
            </div>\
            <div class="play-body">\
                <div data-step="1" class="play-body-step">\
                    <div class="play-body-step-title"><span>Откройте браузер <strong>Chrome</strong></span></div>\
                    <div class="play-body-step-text"><span>Если вы находитесь в каком либо другом браузере, откройте данную страницу в браузере Chrome</span></div>\
                </div>\
                <div data-step="2" class="play-body-step">\
                    <div class="play-body-step-title"><span>Нажмите на <strong>меню браузера</strong></span>\
                        <div class="play-body-step-title-icon"><img src="/image/dotss.png" alt="Share"></div>\
                    </div>\
                    <div class="play-body-step-text"><span>Меню браузера — это <strong>троеточие</strong>, которое находится в правом верхнем углу вашего браузера</span></div>\
                </div>\
                <div data-step="3" class="play-body-step">\
                    <div class="play-body-step-title"><span>Выберите пункт <strong>"Установить приложение"</strong></span></div>\
                    <div class="play-body-step-text"><span>У вас появится окно с уведомлением <strong>установки приложения</strong>, после установки приложение добавится на рабочий стол вашего устройства</span></div>\
                </div>\
            </div>'
        }
    </script>
</html>