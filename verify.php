<?php if(session_status()===PHP_SESSION_NONE){session_start();}$styles=<<<S
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f9;
        }
        .verification-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }
        .success {
            color: #155724;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .error {
            color: #721c24;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 1rem;
            border-radius: 4px;
            margin: 1rem 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498DB;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
S;function displayMessage($message,$isSuccess=true){global $styles;echo $styles;echo '<div class="verification-container">';echo '<div class="'.($isSuccess?'success':'error').'">'.htmlspecialchars($message).'</div>';echo '<a href="login.php" class="button">Go to Login</a>';echo '</div></body></html>';exit;}if(!isset($_GET['token'])||!isset($_GET['email'])){displayMessage('Invalid verification link.',false);}$email=filter_var($_GET['email'],FILTER_SANITIZE_EMAIL);$token=$_GET['token'];if(!file_exists('temporary_users.json')){displayMessage('Verification failed: User database not found.',false);}$tempUsers=json_decode(file_get_contents('temporary_users.json'),true)??[];foreach($tempUsers as $user){if($user['email']===$email&&$user['verification_token']===$token){if(moveToVerifiedUsers($email)){displayMessage('Email verified successfully! You can now login.',true);}else{displayMessage('Error moving user to verified users.',false);}break;}}displayMessage('Invalid verification token or email.',false);function moveToVerifiedUsers($email){$tempUsers=[];$verifiedUsers=[];if(file_exists('temporary_users.json')){$tempUsers=json_decode(file_get_contents('temporary_users.json'),true)??[];}if(file_exists('users.json')){$verifiedUsers=json_decode(file_get_contents('users.json'),true)??[];}foreach($tempUsers as $key=>$user){if($user['email']===$email){unset($user['verification_token']);$verifiedUsers[]=$user;unset($tempUsers[$key]);$tempResult=file_put_contents('temporary_users.json',json_encode(array_values($tempUsers),JSON_PRETTY_PRINT));$userResult=file_put_contents('users.json',json_encode($verifiedUsers,JSON_PRETTY_PRINT));return($tempResult!==false&&$userResult!==false);}}return false;} ?><!doctypehtml><html lang="en"><head><meta charset="UTF-8"><meta content="width=device-width,initial-scale=1"name="viewport"><title>Email Verification - Shout About UAE</title><link href="/favicon/favicon.ico"rel="icon"type="image/x-icon"><link href="/favicon/favicon-16x16.png"rel="icon"sizes="16x16"type="image/png"><link href="/favicon/favicon-32x32.png"rel="icon"sizes="32x32"type="image/png"><link href="/favicon/apple-touch-icon.png"rel="apple-touch-icon"sizes="180x180"><link href="/favicon/android-chrome-192x192.png"rel="icon"sizes="192x192"><link href="/favicon/android-chrome-512x512.png"rel="icon"sizes="512x512"><link href="/favicon/site.webmanifest"rel="manifest"><link href="all.css"rel="stylesheet"><link href="Lato.css"rel="stylesheet"><link href="main.css"rel="stylesheet"><style>*{margin:0;padding:0;box-sizing:border-box}body{font-family:"ABC Diatype",sans-serif;background-color:#f4f4f9;display:flex;justify-content:center;align-items:center;min-height:100vh;padding:20px}.verification-container{background-color:#fff;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);padding:40px;max-width:500px;width:100%;text-align:center}.icon-container{margin-bottom:20px}.success-icon{color:#4caf50;font-size:64px}.error-icon{color:#f44336;font-size:64px}h1{color:#333;margin-bottom:20px;font-size:24px}.message{color:#666;margin-bottom:30px;line-height:1.5}.countdown{color:#888;font-size:14px;margin-top:20px}.button{display:inline-block;padding:12px 24px;background-color:#3498db;color:#fff;text-decoration:none;border-radius:4px;transition:background-color .3s}.button:hover{background-color:#2980b9}@keyframes spin{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}</style></head><body><div class="verification-container"><div class="icon-container"><?php if($verificationStatus['success']): ?><i class="fas fa-check-circle success-icon"></i><?php else: ?><i class="fas error-icon fa-times-circle"></i><?php endif; ?></div><h1><?php echo $verificationStatus['success']?'Email Verified!':'Verification Failed'; ?></h1><p class="message"><?php echo $verificationStatus['message']; ?></p><?php if($verificationStatus['success']): ?><a class="button"href="login">Continue to Login</a><p class="countdown">Redirecting in <span id="countdown"><?php echo $verificationStatus['redirect_countdown']; ?></span>seconds...</p><?php else: ?><a class="button"href="sign-up">Back to Sign Up</a><?php endif; ?></div><?php if($verificationStatus['success']): ?><script>let countdown =<?php echo $verificationStatus['redirect_countdown']; ?>;
        const countdownElement = document.getElementById('countdown');

        const timer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = 'login';
            }
        }, 1000);</script><?php endif; ?><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"en"},"google_translate_element")}</script><script>document.addEventListener("DOMContentLoaded", function () {
          function resizeNavLinksByLanguage() {
              const language = document.documentElement.lang;
              const navLinks = document.querySelectorAll('.nav-link');
              let baseFontSize = 16;

              let totalLength = 0;
              navLinks.forEach(link => {
                  totalLength += link.textContent.length;
              });

              let avgLength = totalLength / navLinks.length;

              if (avgLength < 10) {
                  baseFontSize = 15;
              } else if (avgLength >= 10 && avgLength < 20) {
                  baseFontSize = 15;
              } else if (avgLength >= 20) {
                  baseFontSize = 14; 
              }

              navLinks.forEach(link => {
                  link.style.fontSize = `${baseFontSize}px`;
              });
          }

          resizeNavLinksByLanguage();

          const observer = new MutationObserver(resizeNavLinksByLanguage);
          observer.observe(document.documentElement, { attributes: true, attributeFilter: ['lang'] });
      });</script><script src="translator.js"type="text/javascript"></script><script>const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          const iframe = document.querySelector('.VIpgJd-ZVi9od-ORHb-OEVmcd');
          if (iframe) {
            iframe.style.visibility = 'hidden';
          }

          const googGtVtElements = document.querySelectorAll('.goog-gt-vt');
          googGtVtElements.forEach(function(element) {
            element.style.visibility = 'hidden';
          });

          if (iframe || googGtVtElements.length > 0) {
            observer.disconnect();
          }

          const elementsToRemove = [
            '.VIpgJd-yAWNEb-hvhgNd-l4eHX-i3jM8c',
            '.VIpgJd-yAWNEb-hvhgNd-k77Iif-i3jM8c',
            '.VIpgJd-yAWNEb-hvhgNd-N7Eqid-B7I4Od',
            '.ltr',
            '.VIpgJd-ZVi9od-aZ2wEe-wOHMyf',
            '.VIpgJd-ZVi9od-aZ2wEe-OiiCO',
            '.VIpgJd-ZVi9od-aZ2wEe',
            '.VIpgJd-ZVi9od-aZ2wEe-Jt5cK'
          ];
          elementsToRemove.forEach(function(className) {
            const elements = document.querySelectorAll(className);
            elements.forEach(function(element) {
              element.remove();
            });
          });
        });
      });

      observer.observe(document.body, { childList: true, subtree: true });

      setInterval(function() {
        document.body.style.top = '0';
      }, 100);

      window.addEventListener('load', function () {
        function getTextWidth(text) {
          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');
          context.font = window.getComputedStyle(document.body).font;
          return context.measureText(text).width;
        }

        function initTranslateWidget() {
          const selectElement = document.querySelector('.goog-te-combo');
        }

        const observer = new MutationObserver(function (mutationsList) {
          for (let mutation of mutationsList) {
            if (mutation.type === 'childList' && document.querySelector('.goog-te-combo')) {
              observer.disconnect();
              initTranslateWidget();
            }
          }
        });

        observer.observe(document.body, { childList: true, subtree: true });

        const selectElement = document.querySelector('.goog-te-combo');
      });</script><script>document.addEventListener('DOMContentLoaded', function () {
        const observer = new MutationObserver(function (mutations) {
          mutations.forEach(function (mutation) {
            const select = document.querySelector('.goog-te-combo');
            if (select) {
              observer.disconnect();

              observeSelectOptions(select);

              select.addEventListener('change', function () {
                adjustSelectWidth(select);
              });
            }
          });
        });

        observer.observe(document.body, { childList: true, subtree: true });
      });

      function observeSelectOptions(select) {
        const optionsObserver = new MutationObserver(function () {
          if (select.options.length > 0) {
            adjustSelectWidth(select);

            optionsObserver.disconnect();
          }
        });

        optionsObserver.observe(select, { childList: true });
      }

      function adjustSelectWidth(select) {
        if (select.options.length === 0) {
          console.error('The <select> element has no options available.');
          return;
        }

        const selectedOption = select.options[select.selectedIndex];

        if (!selectedOption) {
          console.error('No option is selected in the <select> element.');
          return;
        }

        const span = document.createElement('span');
        span.style.visibility = 'hidden';
        span.style.position = 'absolute';
        span.style.whiteSpace = 'nowrap';
        span.textContent = selectedOption.textContent;
        document.body.appendChild(span);

        const optionWidth = span.offsetWidth + 15;

        select.style.width = optionWidth + 'px'; 

        document.body.removeChild(span);
      }</script><script>var prevScrollpos=window.pageYOffset;window.onscroll=function(){var o=window.pageYOffset,e=document.getElementById("navbar");e&&(o<prevScrollpos?e.style.top="0":window.matchMedia("(pointer: coarse)").matches?e.style.top="-196px":e.style.top="-100px"),prevScrollpos=o}</script></body></html>