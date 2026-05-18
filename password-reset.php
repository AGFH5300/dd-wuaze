<?php ini_set('display_errors',1);error_reporting(E_ALL);require_once __DIR__.'/vendor/autoload.php';use PHPMailer\PHPMailer\PHPMailer;use PHPMailer\PHPMailer\Exception as MailerException;function logError($message,$context=[]){$logFile=__DIR__.'/error.log';$timestamp=date('Y-m-d H:i:s');$contextString=$context?' | '.json_encode($context):'';error_log("[{$timestamp}] {$message}{$contextString}\n",3,$logFile);}function handleFatalError($errno,$errstr,$errfile,$errline){logError("Fatal Error",['errno'=>$errno,'errstr'=>$errstr,'file'=>$errfile,'line'=>$errline]);return false;}set_error_handler('handleFatalError');session_start();header('X-Frame-Options: DENY');header('X-Content-Type-Options: nosniff');$usersFile=__DIR__.'/users.json';$maxResetTokenAge=3600;if(!file_exists($usersFile)){logError("Users file not found, creating: {$usersFile}");file_put_contents($usersFile,json_encode([]));}if(!is_readable($usersFile)||!is_writable($usersFile)){logError("Insufficient permissions for users file",['file'=>$usersFile]);die('Configuration Error: Cannot access user data');}try{$jsonContent=file_get_contents($usersFile);$users=json_decode($jsonContent,true);if(json_last_error()!==JSON_ERROR_NONE){logError("JSON Decode Error",['error'=>json_last_error_msg(),'content'=>$jsonContent]);$users=[];}}catch(Exception $e){logError("Failed to load users",['exception'=>$e->getMessage()]);$users=[];}$response=['success'=>false,'message'=>''];$showRequestForm=true;$tokenError='';if(isset($_GET['token'])){$token=$_GET['token'];$userWithToken=null;foreach($users as&$user){if(isset($user['resetToken'])&&$user['resetToken']===$token){if(!isset($user['tokenExpiry'])||$user['tokenExpiry']>time()){$userWithToken=&$user;$showRequestForm=false;break;}else{$tokenError='Reset link has expired. Please request a new link.';}}}if(!$userWithToken){$tokenError='Invalid or expired reset token.';}}if($_SERVER['REQUEST_METHOD']==='POST'){try{if(isset($_POST['ajax'])){header('Content-Type: application/json');$email=filter_var(trim($_POST['email']),FILTER_VALIDATE_EMAIL);if(!$email){throw new Exception('Invalid email address');}$userFound=false;foreach($users as&$user){if(strtolower($user['email'])===strtolower($email)){$userFound=true;try{$token=bin2hex(random_bytes(32));$user['resetToken']=$token;$user['tokenExpiry']=time()+$maxResetTokenAge;file_put_contents($usersFile,json_encode($users,JSON_PRETTY_PRINT));$resetLink="https://dd.wuaze.com/password-reset.php?token={$token}";$mail=new PHPMailer(true);$mail->isSMTP();$mail->Host='smtp.gmail.com';$mail->SMTPAuth=true;$mail->Username='diatech.ecotechsolutions@gmail.com';$mail->Password='qmofwmptlnfimurv';$mail->SMTPSecure=PHPMailer::ENCRYPTION_STARTTLS;$mail->Port=587;$mail->setFrom('diatech.ecotechsolutions@gmail.com','Shout about UAE');$mail->addAddress($email);$mail->isHTML(true);$mail->Subject='Password Reset Request';$mail->Body="<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 20px;
      background-color: #f4f4f9;
      color: #333;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    
    a {
      color: white;
      text-decoration: none;
    }

    .container {
      max-width: 400px;
      margin: 0 auto;
      padding: 40px;
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    h1 {
      color: #333;
      margin-bottom: 20px;
    }

    p {
      color: #555;
      margin-bottom: 30px;
      line-height: 1.5;
    }

    .btn {
      padding: 12px;
      background-color: #007BFF;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }

    .btn:hover {
      background-color: #0056b3;
    }

    .footer {
      margin-top: 20px;
      font-size: 14px;
      color: #777;
    }

    .fallback-link {
      margin-top: 20px;
      font-size: 14px;
      color: #777;
    }
  </style>
</head>
<body>
  <div class='container'>
    <h1>Password Reset Request</h1>
    <p>We received a request to reset your password. Click the button below to create a new password.</p>
    <a href='https://dd.wuaze.com/password-reset.php?token=$token' class='btn' style='color: white;'>Reset Password</a>
    
    <p class='fallback-link'>
      If the button doesn't work, click the link below or copy-paste it into your web browser:<br>
      <a href='https://dd.wuaze.com/password-reset.php?token=$token' style='color: #007BFF;'>https://dd.wuaze.com/password-reset.php?token=$token</a>
    </p>

    <div class='footer'>
      <p>If you didn't request a password reset, please ignore this email.</p>
    </div>
  </div>
</body>
</html>";$mail->send();$response['success']=true;$response['message']='Password reset email sent!';logError("Password reset email sent",['email'=>$email]);break;}catch(MailerException $e){logError("Email send failed",['email'=>$email,'error'=>$e->getMessage()]);$response['message']='Failed to send reset email';}}}if(!$userFound){$response['message']='No user found with that email';}echo json_encode($response);exit;}if(isset($_POST['reset_password'])){$newPassword=$_POST['new_password'];$confirmPassword=$_POST['confirm_password'];$token=$_POST['token'];if($newPassword!==$confirmPassword){$response['message']='Passwords do not match';}elseif(strlen($newPassword)<8){$response['message']='Password must be 8+ characters';}else{foreach($users as&$user){if(isset($user['resetToken'])&&$user['resetToken']===$token){$user['password']=password_hash($newPassword,PASSWORD_DEFAULT);unset($user['resetToken']);unset($user['tokenExpiry']);file_put_contents($usersFile,json_encode($users,JSON_PRETTY_PRINT));$response['success']=true;$response['message']='Password reset successfully';logError("Password reset successful",['email'=>$user['email']]);break;}}}}}catch(Exception $e){logError("Processing error",['error'=>$e->getMessage()]);$response['message']='An unexpected error occurred';}} ?><!doctypehtml><html lang="en"><head><meta charset="UTF-8"><meta content="width=device-width,initial-scale=1"name="viewport"><title>Password Reset</title><link href="/favicon/favicon.ico"rel="icon"type="image/x-icon"><link href="/favicon/favicon-16x16.png"rel="icon"sizes="16x16"type="image/png"><link href="/favicon/favicon-32x32.png"rel="icon"sizes="32x32"type="image/png"><link href="/favicon/apple-touch-icon.png"rel="apple-touch-icon"sizes="180x180"><link href="/favicon/android-chrome-192x192.png"rel="icon"sizes="192x192"><link href="/favicon/android-chrome-512x512.png"rel="icon"sizes="512x512"><link href="/favicon/site.webmanifest"rel="manifest"><link href="all.css"rel="stylesheet"><link href="Lato.css"rel="stylesheet"><link href="main.css"rel="stylesheet"><style>*{margin:0;padding:0;box-sizing:border-box}body,html{width:100%;height:100%;background-color:#f4f4f9}main{display:flex}.left-section{width:50%;height:100vh;background:url(camel.png) no-repeat center center;background-size:cover;background-color:#333}.right-section{width:50%;height:100vh;background-color:#fff;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px}h1{font-size:2em;margin-bottom:20px}.form-group{position:relative;margin:20px 0;width:100%}label{box-sizing:border-box;color:#5c6062;cursor:default;display:block;font-family:"ABC Diatype",sans-serif;font-size:10.07px;font-weight:400;height:14px;left:16px;letter-spacing:.604px;line-height:14px;position:absolute;text-transform:uppercase;top:12px;z-index:2}.form-input{appearance:none;background-color:#f3f3f3;border:2px solid #fcfcfc;border-radius:8px;height:62px;width:360px;color:#0e1525;font-family:"ABC Diatype",sans-serif;font-size:17.4px;line-height:24px;padding:26px 48px 8px 16px;text-align:start}.form-button{width:360px;padding:12px;background-color:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:16px;margin-top:20px}.password-container{position:relative}.password-eye-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px}.message-container{padding:15px;margin:10px 0;border-radius:4px;width:360px;display:none}.success-message{background-color:#d4edda;color:#155724;border:1px solid #c3e6cb}.error-message{background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb}.signup-link{align-items:stretch;border-bottom-style:solid;border-bottom-width:0;border-left-style:solid;border-left-width:0;border-right-style:solid;border-right-width:0;border-top-style:solid;border-top-width:0;box-sizing:border-box;color:#07080a;color-scheme:light;display:block;flex-basis:auto;flex-direction:column;flex-shrink:0;font-family:"ABC Diatype",sans-serif;font-kerning:normal;font-optical-sizing:auto;font-size:12.08px;font-synthesis-small-caps:none;font-synthesis-style:none;font-synthesis-weight:none;font-weight:400;height:16px;letter-spacing:.242px;line-height:16px;margin-bottom:0;margin-left:0;margin-right:0;margin-top:20px;max-width:100%;min-height:0;min-width:0;outline-color:#07080a;outline-style:none;outline-width:0;overflow-wrap:break-word;overflow-x:hidden;overflow-y:hidden;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:center;text-overflow:ellipsis;-webkit-box-align:stretch;-webkit-font-smoothing:antialiased}.signup-link a{color:#3498db;cursor:pointer;font-family:"ABC Diatype",sans-serif;font-size:12.08px;font-weight:400;height:auto;letter-spacing:.242px;line-height:16px;text-align:center;width:auto}.signup-link a:hover{text-decoration:underline}.verification-message{margin-bottom:20px;padding:15px;border-radius:4px;width:360px;text-align:center}.verification-success{background-color:#d4edda;color:#155724;border:1px solid #c3e6cb}.verification-error{background-color:#f8d7da;color:#721c24;border:1px solid #f5c6cb}.form-button{position:relative;transition:all .3s ease}.form-button.loading{color:transparent;pointer-events:none}.form-button.loading::after{content:'';position:absolute;width:20px;height:20px;top:50%;left:50%;margin:-10px 0 0 -10px;border:3px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s ease-in-out infinite;pointer-events:none}@keyframes spin{to{transform:rotate(360deg)}}.success-view{display:none;text-align:center;padding:40px;justify-content:center}.success-view i{font-size:80px;color:#28a745;justify-content:center}.success-view h2{color:#28a745;margin-bottom:15px}.success-view p{color:#666;margin-bottom:20px}.email-link{display:inline-flex;align-items:center;padding:10px 20px;background-color:#3498db;color:#fff;text-decoration:none;border-radius:5px;margin-top:20px;transition:background-color .3s}.email-link i{font-size:16px;margin-right:8px;color:#fff}.email-link:hover{background-color:#2980b9}.hide{display:none}.error-message{color:#dc3545;margin-top:10px;display:none}</style></head><body><nav class="navbar"><div class="container"><a href="home.html"><img alt="Logo"class="logo"src="logo.png"></a><ul class="nav-links"><li class="nav-item"><a href="home.html"class="nav-link"data-translate="home">Home</a></li><li class="nav-item"><a href="getting-started.html"class="nav-link"data-translate="gettingStarted"aria-expanded="false"aria-haspopup="true">Getting Started <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="important-documents.html"class="dropdown-link"data-translate="importantDocuments">Important Documents</a> <a href="housing.html"class="dropdown-link"data-translate="housing">Housing</a> <a href="healthcare.html"class="dropdown-link"data-translate="healthcare">Healthcare</a> <a href="cost-of-living.html"class="dropdown-link"data-translate="costOfLiving">Cost of Living</a></div></li><li class="nav-item"><a href="living-in-the-uae.html"class="nav-link"data-translate="livingInUAE"aria-expanded="false"aria-haspopup="true">Living in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="culture-and-customs.html"class="dropdown-link"data-translate="cultureAndCustoms">Culture and Customs</a> <a href="transportation.html"class="dropdown-link"data-translate="transportation">Transportation</a> <a href="shopping.html"class="dropdown-link"data-translate="shopping">Shopping</a></div></li><li class="nav-item"><a href="education.html"class="nav-link"data-translate="education"aria-expanded="false"aria-haspopup="true">Education <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="curriculum-guides.html"class="dropdown-link"data-translate="curriculumGuides">Curriculum Guides</a> <a href="school-listings.html"class="dropdown-link"data-translate="schoolListings">School Listings</a></div></li><li class="nav-item"><a href="working-in-the-uae.html"class="nav-link"data-translate="workingInUAE"aria-expanded="false"aria-haspopup="true">Working in the UAE <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="labour-card.html"class="dropdown-link"data-translate="labourCard">Labour Card</a> <a href="job-market-insights.html"class="dropdown-link"data-translate="jobMarketInsights">Job Market Insights</a> <a href="labour-laws.html"class="dropdown-link"data-translate="labourLaws">Labour Laws</a> <a href="becoming-an-entrepreneur.html"class="dropdown-link"data-translate="becomingEntrepreneur">Becoming an Entrepreneur</a> <a href="networking-oppurtunities.html"class="dropdown-link"data-translate="networkingOpportunities">Networking Opportunities</a></div></li><li class="nav-item"><a href="community-and-support.html"class="nav-link"data-translate="communitySupport"aria-expanded="false"aria-haspopup="true">Community & Support <i class="fa-chevron-down fa-regular"></i></a><div class="dropdown-content"><a href="faq.php"class="dropdown-link"data-translate="faq">FAQ</a> <a href="contact-us.php"class="dropdown-link"data-translate="contactUs">Contact Us</a></div></li></ul><div class="nav-right"><div class="search-container"><input name="q"class="search-input"placeholder="Search..."><div class="search-icons-container"><i class="fas close-icon fa-times"id="close-icon"></i> <button class="search-btn"type="button"id="search-btn"><i class="fas fa-search"></i></button></div></div><div class="lang-dropdown"><button class="lang-btn"><i class="fa-globe fal"style="color:#fff"></i><div id="google_translate_element"></div></button></div><a href="sign-up.php"class="sign-up-btn"data-translate="signUp"style="text-decoration:none"><i class="fa-solid fa-user"style="margin-right:7px"></i> <span class="sign-up-text">Sign Up</span> <span><i class="fas fa-arrow-up-right sign-up-icon"style="color:#fff"></i></span></a></div></div></nav><main><div class="left-section"></div><div class="right-section"><?php if($showRequestForm): ?><div class="reset-container"><h1>Password Reset</h1><?php if($tokenError): ?><div class="error-message"style="display:block"><?php echo htmlspecialchars($tokenError); ?></div><?php endif; ?><form class="reset-form"id="resetForm"method="POST"><div class="form-group"><label for="email">Email</label> <input name="email"type="email"class="form-input"id="email"required></div><div class="error-message"></div><button class="form-button"type="submit">Send Reset Link</button> <input name="ajax"type="hidden"value="1"></form><div class="signup-link">Remember your password? <a href="login.php">Login</a></div></div><div class="success-view"><i class="fas fa-check-circle"style="margin-bottom:20px"></i><h2>Email Sent Successfully!</h2><p>Please check your email to reset your password</p><p>A password reset link has been sent to <span id="sent-email"></span></p><a href="https://mail.google.com/mail/u/0/#inbox"class="email-link"target="_blank"><i class="fa-envelope far"></i> Open Email</a></div><?php else: ?><div class="reset-container"><h1>Set New Password</h1><form class="reset-form"id="newPasswordForm"method="POST"><div class="form-group"><label for="new_password">New Password</label> <input name="new_password"type="password"class="form-input"id="new_password"required></div><div class="form-group"><label for="confirm_password">Confirm New Password</label> <input name="confirm_password"type="password"class="form-input"id="confirm_password"required></div><input name="token"type="hidden"value="<?php echo htmlspecialchars($token); ?>"> <input name="reset_password"type="hidden"value="1"><?php if($response['message']): ?><div class="error-message"style="display:block"><?php echo htmlspecialchars($response['message']); ?></div><?php endif; ?><button class="form-button"type="submit">Reset Password</button></form><?php if($response['success']): ?><script>setTimeout(function(){window.location.href="login.php"},3e3)</script><?php endif; ?></div><?php endif; ?></div></main><script>document.addEventListener('DOMContentLoaded', function() {
        const resetForm = document.getElementById('resetForm');
        const newPasswordForm = document.getElementById('newPasswordForm');
        const resetContainer = document.querySelector('.reset-container');
        const successView = document.querySelector('.success-view');
        const errorMessage = document.querySelector('.error-message');
        
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const submitButton = resetForm.querySelector('.form-button');
                submitButton.classList.add('loading');
                errorMessage.style.display = 'none';
                
                const emailInput = document.getElementById('email');
                const emailValue = emailInput.value.trim();
                
                if (!emailValue || !isValidEmail(emailValue)) {
                    submitButton.classList.remove('loading');
                    errorMessage.textContent = 'Please enter a valid email address.';
                    errorMessage.style.display = 'block';
                    return;
                }
                
                const formData = new FormData(resetForm);
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    submitButton.classList.remove('loading');
                    
                    if (data.success) {
                        resetContainer.style.display = 'none';
                        successView.style.display = 'block';
                        document.getElementById('sent-email').textContent = emailValue;
                    } else {
                        errorMessage.textContent = data.message || 'An error occurred. Please try again.';
                        errorMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitButton.classList.remove('loading');
                    errorMessage.textContent = 'An unexpected error occurred. Please try again.';
                    errorMessage.style.display = 'block';
                });
            });
        }
        
        if (newPasswordForm) {
            newPasswordForm.addEventListener('submit', function(e) {
                const newPassword = document.getElementById('new_password');
                const confirmPassword = document.getElementById('confirm_password');
                const errorMessage = newPasswordForm.querySelector('.error-message');
                
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    errorMessage.textContent = 'Passwords do not match.';
                    errorMessage.style.display = 'block';
                } else if (newPassword.value.length < 8) {
                    e.preventDefault();
                    errorMessage.textContent = 'Password must be at least 8 characters long.';
                    errorMessage.style.display = 'block';
                }
            });
        }
        
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }
    });</script><script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script type="text/javascript">function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"en"},"google_translate_element")}</script><script>document.addEventListener("DOMContentLoaded", function () {
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