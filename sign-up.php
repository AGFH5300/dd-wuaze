<?php
  if(session_status() === PHP_SESSION_NONE) {
      session_start();
  }
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  
  require 'vendor/autoload.php';
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;
  
  $response = [
      'error' => false,
      'message' => '',
      'email' => '',
      'name' => '',
      'lastname' => '',
      'country' => '',
      'passwordErrors' => []
  ];
  
  $badWordsJson = file_get_contents('default.json');
  $badWordsData = json_decode($badWordsJson, true);
  $badWords = array_column($badWordsData, 'word');
  
  function containsBadWords($text, $badWords) {
      foreach($badWords as $badWord) {
          if(stripos($text, $badWord) !== false) {
              return true;
          }
      }
      return false;
  }
  
  function getPasswordErrors($password) {
      $errors = [];
      
      if (strlen($password) < 8) {
          $errors[] = "Password must be at least 8 characters long";
      }
      if (!preg_match('/[A-Z]/', $password)) {
          $errors[] = "Password must contain at least one uppercase letter";
      }
      if (!preg_match('/[a-z]/', $password)) {
          $errors[] = "Password must contain at least one lowercase letter";
      }
      if (!preg_match('/[0-9]/', $password)) {
          $errors[] = "Password must contain at least one number";
      }
      if (!preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password)) {
          $errors[] = "Password must contain at least one special character (!@#$%^&*(),.?\":{}|<>_-)";
      }
      if (trim($password) === '') {
          $errors[] = "Password cannot be empty";
      }
      
      return $errors;
  }
  
  function isValidPassword($password) {
      $errors = getPasswordErrors($password);
      return empty($errors);
  }
  
  function saveTemporaryUser($userData) {
      $tempUsers = [];
      if(file_exists('temporary_users.json')) {
          $tempUsers = json_decode(file_get_contents('temporary_users.json'), true) ?? [];
      }
      $tempUsers[] = $userData;
      file_put_contents('temporary_users.json', json_encode($tempUsers, JSON_PRETTY_PRINT));
  }
  
  function moveToVerifiedUsers($email) {
      $tempUsers = [];
      $verifiedUsers = [];
      
      if(file_exists('temporary_users.json')) {
          $tempUsers = json_decode(file_get_contents('temporary_users.json'), true) ?? [];
      }
      
      if(file_exists('users.json')) {
          $verifiedUsers = json_decode(file_get_contents('users.json'), true) ?? [];
      }
      
      foreach($tempUsers as $key => $user) {
          if($user['email'] === $email) {
              $verifiedUsers[] = $user;
              unset($tempUsers[$key]);
              break;
          }
      }
      
      file_put_contents('temporary_users.json', json_encode(array_values($tempUsers), JSON_PRETTY_PRINT));
      file_put_contents('users.json', json_encode($verifiedUsers, JSON_PRETTY_PRINT));
      return true;
  }
  
  function sendVerificationEmail($email, $token) {
      $mail = new PHPMailer(true);
      $verificationLink = "https://dd.wuaze.com/verify.php?token=$token&email=" . urlencode($email);
      
      try {
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'diatech.ecotechsolutions@gmail.com';
          $mail->Password = 'qmofwmptlnfimurv';
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
          $mail->Port = 587;
          
          $mail->setFrom('diatech.ecotechsolutions@gmail.com', 'Shout About UAE');
          $mail->addAddress($email);
          $mail->isHTML(true);
          $mail->Subject = 'Email Verification';
          
          // Email template remains the same as in your original code
          $mail->Body = " <!DOCTYPE html>
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
      <h1>Verify your Email</h1>
      <p>We received a request to verify your email. Click the button below to verify your email.</p>
      <a href='{$verificationLink}' class='btn' style='color: white;'>Verify Email</a>
      
      <p class='fallback-link'>
        If the button doesn't work, click the link below or copy-paste it into your web browser:<br>
        <a href='{$verificationLink}' style='color: #007BFF;'>{$verificationLink}</a>
      </p>
  
      <div class='footer'>
        <p>If you didn't request this email, please ignore it.</p>
      </div>
    </div>
  </body>
  </html>
  ";
          
          $mail->send();
          return true;
      } catch(Exception $e) {
          throw new Exception("Mail could not be sent. Mailer Error: {$mail->ErrorInfo}");
      }
  }
  
  if(isset($_GET['token']) && isset($_GET['email'])) {
      $email = filter_var($_GET['email'], FILTER_SANITIZE_EMAIL);
      $token = $_GET['token'];
      
      $tempUsers = json_decode(file_get_contents('temporary_users.json'), true) ?? [];
      foreach($tempUsers as $user) {
          if($user['email'] === $email && $user['verification_token'] === $token) {
              if(moveToVerifiedUsers($email)) {
                  header('Location: login.php?verified=true');
                  exit;
              }
          }
      }
      header('Location: login.php?verified=false');
      exit;
  }
  
  if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
      $email = strtolower(trim($_POST['email']));
      $name = trim($_POST['name']);
      $lastname = trim($_POST['lastname']);
      $password = $_POST['password'] ?? '';
      $reenterPassword = $_POST['reenter_password'] ?? '';
      $country = $_POST['country'] ?? '';
      
      if(empty($email) || empty($password) || empty($name) || empty($lastname) || empty($reenterPassword) || empty($country)) {
          $response['error'] = true;
          $response['message'] = "All fields are required.";
      }
      elseif(strlen($name) < 3 || strlen($name) > 15) {
          $response['error'] = true;
          $response['message'] = "Name must be between 3 and 15 characters.";
      }
      elseif(strlen($lastname) < 3 || strlen($lastname) > 30) {
          $response['error'] = true;
          $response['message'] = "Last name must be between 3 and 30 characters.";
      }
      elseif(containsBadWords($name, $badWords) || containsBadWords($lastname, $badWords)) {
          $response['error'] = true;
          $response['message'] = "Name contains inappropriate words.";
      }
      else {
          $passwordErrors = getPasswordErrors($password);
          if (!empty($passwordErrors)) {
              $response['error'] = true;
              $response['passwordErrors'] = $passwordErrors;
              $response['message'] = "Password validation failed";
          }
          elseif($password !== $reenterPassword) {
              $response['error'] = true;
              $response['message'] = "Passwords do not match.";
          }
          elseif(file_exists('temporary_users.json')) {
              $tempUsers = json_decode(file_get_contents('temporary_users.json'), true) ?? [];
              $verifiedUsers = file_exists('users.json') ? json_decode(file_get_contents('users.json'), true) ?? [] : [];
              
              $emailExists = false;
              foreach(array_merge($tempUsers, $verifiedUsers) as $user) {
                  if($user['email'] === $email) {
                      $emailExists = true;
                      break;
                  }
              }
              
              if($emailExists) {
                  $response['error'] = true;
                  $response['message'] = "Email already registered.";
              }
              else {
                  $token = bin2hex(random_bytes(16));
                  $userData = [
                      'email' => $email,
                      'name' => $name,
                      'lastname' => $lastname,
                      'password' => password_hash($password, PASSWORD_DEFAULT),
                      'country' => $country,
                      'verification_token' => $token,
                      'created_at' => date('Y-m-d H:i:s')
                  ];
                  
                  try {
                      saveTemporaryUser($userData);
                      sendVerificationEmail($email, $token);
                      $response['message'] = "Registration successful. Please check your email for verification. If you don’t see the email in your inbox, please check your spam or junk folder. If it’s there, be sure to mark it as 'Not Spam' to avoid future issues.";
                  } catch(Exception $e) {
                      error_log("Debug - Error occurred: " . $e->getMessage());
                      $response['error'] = true;
                      $response['message'] = "Error sending verification email. Please try again later.";
                  }
              }
          }
      }
      
      echo json_encode($response);
      exit;
  }
  ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta content="width=device-width,initial-scale=1"name="viewport">
    <meta name="google-signin-client_id" content="522906603359-qpc7qvh9nastdqe3l16s2rq9kti7i7mg.apps.googleusercontent.com">
    <title>Sign Up</title>
    <link href="/favicon/favicon.ico"rel="icon"type="image/x-icon">
    <link href="/favicon/favicon-16x16.png"rel="icon"sizes="16x16"type="image/png">
    <link href="/favicon/favicon-32x32.png"rel="icon"sizes="32x32"type="image/png">
    <link href="/favicon/apple-touch-icon.png"rel="apple-touch-icon"sizes="180x180">
    <link href="/favicon/android-chrome-192x192.png"rel="icon"sizes="192x192">
    <link href="/favicon/android-chrome-512x512.png"rel="icon"sizes="512x512">
    <link href="/favicon/site.webmanifest"rel="manifest">
    <link href="all.css"rel="stylesheet">
    <link href="Lato.css"rel="stylesheet">
    <link href="main.css"rel="stylesheet">
    <style>
      *{margin:0;padding:0;box-sizing:border-box}body,html{width:100%;height:100%;background-color:#f4f4f9}main{display:flex}.left-section{width:50%;height:100vh;background:url(burjkhalifa.jpg) no-repeat center center;background-size:cover;background-color:#333}.right-section{width:50%;height:100vh;background-color:#fff;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px}h1{font-size:2em;margin-bottom:20px}.form-group{position:relative;margin:10px 0;width:100%}label{box-sizing:border-box;color:#5c6062;color-scheme:light;cursor:default;display:block;font-family:"ABC Diatype",sans-serif;font-kerning:normal;font-optical-sizing:auto;font-size:10.07px;font-synthesis:none;height:14px;left:16px;letter-spacing:.604px;line-height:14px;margin:0;padding:0;position:absolute;text-transform:uppercase;top:12px;width:auto;z-index:2;-webkit-font-smoothing:antialiased}.form-input,.input-with-chevron{appearance:auto;background-color:#f3f3f3;border:2px solid #ccc;border-bottom-color:#fcfcfc;border-bottom-left-radius:8px;border-bottom-right-radius:8px;border-bottom-style:solid;border-bottom-width:2px;border-left-color:#fcfcfc;border-left-style:solid;border-left-width:2px;border-right-color:#fcfcfc;border-right-style:solid;border-right-width:2px;border-top-color:#fcfcfc;border-top-left-radius:8px;border-top-right-radius:8px;border-top-style:solid;border-top-width:2px;border-image-outset:0;border-image-repeat:stretch;border-image-slice:100%;border-image-source:none;border-image-width:1;box-sizing:border-box;display:grid;height:62px;width:360px;color:#0e1525;font-family:"ABC Diatype",sans-serif;font-size:17.4px;font-weight:400;letter-spacing:.087px;line-height:24px;text-align:start;text-indent:0;text-rendering:auto;text-shadow:none;text-transform:none;cursor:text;margin:0;padding:26px 48px 8px 16px;padding-block-end:8px;padding-block-start:26px;padding-inline-end:48px;padding-inline-start:16px;overflow-clip-margin:0;overflow-x:clip;overflow-y:clip;z-index:1;-webkit-font-smoothing:antialiased;-webkit-rtl-ordering:logical;-webkit-border-image:none;font-feature-settings:normal;font-kerning:normal;font-optical-sizing:auto;font-size-adjust:none;font-stretch:100%;font-style:normal;font-synthesis-small-caps:none;font-synthesis-style:none;font-synthesis-weight:none;font-variant-alternates:normal;font-variant-caps:normal;font-variant-east-asian:normal;font-variant-ligatures:normal;font-variant-numeric:normal;font-variant-position:normal;font-variation-settings:normal;color-scheme:light}.input-with-chevron{cursor:pointer}.form-button{width:100%;padding:12px;background-color:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:16px;margin-top:20px}.dropdown-container{position:relative;width:360px}.dropdown-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:16px;color:#333;pointer-events:none}.dropdown-options{display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid #ccc;border-radius:8px;max-height:200px;overflow-y:auto;box-sizing:border-box;z-index:10}.dropdown-options.show{display:block}.dropdown-option{padding:10px;cursor:pointer;display:flex;align-items:center}.dropdown-option:hover{background-color:#f0f0f0}.dropdown-separator{padding:10px;text-align:center;color:#888}#country-input{padding-left:35px;background-repeat:no-repeat;background-position:left center;background-size:20px 15px;background-color:#f3f3f3;border:2px solid #ccc;border-radius:8px;height:62px;width:360px;color:#0e1525;font-family:"ABC Diatype",sans-serif;font-size:17.4px;line-height:24px;padding:26px 48px 8px 16px;text-align:start}.flag-icon{width:20px;height:15px;margin-right:10px;vertical-align:middle;margin-left:10px}.password-container{position:relative}.password-container input{width:100%;padding-right:40px}.password-eye-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px}.span-login-redirect{align-items:stretch;border-bottom-style:solid;border-bottom-width:0;border-left-style:solid;border-left-width:0;border-right-style:solid;border-right-width:0;border-top-style:solid;border-top-width:0;box-sizing:border-box;color:#07080a;color-scheme:light;display:block;flex-basis:auto;flex-direction:column;flex-shrink:0;font-family:"ABC Diatype",sans-serif;font-kerning:normal;font-optical-sizing:auto;font-size:12.08px;font-synthesis-small-caps:none;font-synthesis-style:none;font-synthesis-weight:none;font-weight:400;height:16px;letter-spacing:.242px;line-height:16px;margin-bottom:0;margin-left:0;margin-right:0;margin-top:20px;max-width:100%;min-height:0;min-width:0;outline-color:#07080a;outline-style:none;outline-width:0;overflow-wrap:break-word;overflow-x:hidden;overflow-y:hidden;padding-bottom:0;padding-left:0;padding-right:0;padding-top:0;text-align:center;text-overflow:ellipsis;-webkit-box-align:stretch;-webkit-font-smoothing:antialiased}.a-login-redirect{color:#3498db;cursor:pointer;font-family:"ABC Diatype",sans-serif;font-size:12.08px;font-weight:400;height:auto;letter-spacing:.242px;line-height:16px;text-align:center;width:auto}.a-login-redirect:hover{text-decoration:underline}.success-view{display:none;text-align:center;padding:40px;justify-content:center}.success-view i{font-size:80px;color:#28a745;justify-content:center}.success-view h2{color:#28a745;margin-bottom:15px}.success-view p{color:#666;margin-bottom:20px}.change-button{background-color:#28a745;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:16px}.change-button:hover{background-color:#218838}.spinner{display:inline-block;width:20px;height:20px;border:3px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s ease-in-out infinite;margin-right:10px}@keyframes spin{to{transform:rotate(360deg)}}.button-content{display:flex;align-items:center;justify-content:center}.error-text{color:#dc3545;font-size:14px;margin-top:5px;display:none;font-family:"ABC Diatype",sans-serif}.form-group{margin-bottom:20px}.error .error-text{display:block}.error input{border-color:#dc3545}.form-input.error {
      border-color: #dc3545;
      background-color: #fff;
      }
      .error-text {
      color: #dc3545;
      font-size: 14px;
      margin-top: 5px;
      display: none;
      font-family: "ABC Diatype", sans-serif;
      }
      .password-error-list {
      color: #dc3545;
      font-size: 14px;
      margin-top: 5px;
      padding-left: 20px;
      font-family: "ABC Diatype", sans-serif;
      }
    </style>
  </head>
  <body>
    <nav class="navbar" style="padding-bottom: 22px; color: white;">
      <div class="container">
      <a href="home.html"><img alt="Logo"class="logo"src="logo.png"></a>
      <ul class="nav-links">
        <li class="nav-item"><a class="nav-link"href="home.html"data-translate="home">Home</a></li>
        <li class="nav-item">
          <a class="nav-link"href="getting-started.html"data-translate="gettingStarted"aria-expanded="false"aria-haspopup="true">Getting Started <i class="fa-regular fa-chevron-down"></i></a>
          <div class="dropdown-content"><a class="dropdown-link"href="important-documents.html"data-translate="importantDocuments">Important Documents</a> <a class="dropdown-link"href="housing.html"data-translate="housing">Housing</a> <a class="dropdown-link"href="healthcare.html"data-translate="healthcare">Healthcare</a> <a class="dropdown-link"href="cost-of-living.html"data-translate="costOfLiving">Cost of Living</a></div>
        </li>
        <li class="nav-item">
          <a class="nav-link"href="living-in-the-uae.html"data-translate="livingInUAE"aria-expanded="false"aria-haspopup="true">Living in the UAE <i class="fa-regular fa-chevron-down"></i></a>
          <div class="dropdown-content"><a class="dropdown-link"href="culture-and-customs.html"data-translate="cultureAndCustoms">Culture and Customs</a> <a class="dropdown-link"href="transportation.html"data-translate="transportation">Transportation</a> <a class="dropdown-link"href="shopping.html"data-translate="shopping">Shopping</a></div>
        </li>
        <li class="nav-item">
          <a class="nav-link"href="education.html"data-translate="education"aria-expanded="false"aria-haspopup="true">Education <i class="fa-regular fa-chevron-down"></i></a>
          <div class="dropdown-content"><a class="dropdown-link"href="curriculum-guides.html"data-translate="curriculumGuides">Curriculum Guides</a> <a class="dropdown-link"href="school-listings.html"data-translate="schoolListings">School Listings</a></div>
        </li>
        <li class="nav-item">
          <a class="nav-link"href="working-in-the-uae.html"data-translate="workingInUAE"aria-expanded="false"aria-haspopup="true">Working in the UAE <i class="fa-regular fa-chevron-down"></i></a>
          <div class="dropdown-content"><a class="dropdown-link"href="labour-card.html"data-translate="labourCard">Labour Card</a> <a class="dropdown-link"href="job-market-insights.html"data-translate="jobMarketInsights">Job Market Insights</a> <a class="dropdown-link"href="labour-laws.html"data-translate="labourLaws">Labour Laws</a> <a class="dropdown-link"href="becoming-an-entrepreneur.html"data-translate="becomingEntrepreneur">Becoming an Entrepreneur</a> <a class="dropdown-link"href="networking-oppurtunities.html"data-translate="networkingOpportunities">Networking Opportunities</a></div>
        </li>
        <li class="nav-item">
          <a class="nav-link"href="community-and-support.html"data-translate="communitySupport"aria-expanded="false"aria-haspopup="true">Community & Support <i class="fa-regular fa-chevron-down"></i></a>
          <div class="dropdown-content"><a class="dropdown-link"href="faq.php"data-translate="faq">FAQ</a> <a class="dropdown-link"href="contact-us.php"data-translate="contactUs">Contact Us</a></div>
        </li>
      </ul>
      <div class="nav-right">
      <div class="search-container">
        <input class="search-input"name="q"placeholder="Search...">
        <div class="search-icons-container"><i class="fas close-icon fa-times"id="close-icon"></i> <button class="search-btn"type="button"id="search-btn"><i class="fas fa-search"></i></button></div>
      </div>
      <div class="lang-dropdown">
        <button class="lang-btn">
          <i class="fa-globe fal"style="color:#fff"></i>
          <div id="google_translate_element"></div>
        </button>
      </div>
    </nav>
    <main>
      <div class="left-section"></div>
      <div class="right-section">
        <h1>Sign Up</h1>
        <!--<div class="g-signin2" data-onsuccess="onSignIn"></div>-->
        <form id="signupForm"method="POST">
          <div class="form-group"><label for="email">Email</label> <input class="form-input"name="email"id="email"required type="email"></div>
          <div class="form-group"><label for="name">Name</label> <input class="form-input"name="name"id="name"required minlength="3"maxlength="15"></div>
          <div class="form-group"><label for="lastname">Last Name</label> <input class="form-input"name="lastname"id="lastname"required minlength="3"maxlength="30"></div>
          <div class="form-group">
            <label for="password">Password</label>
            <div class="password-container"><input class="form-input"name="password"id="password"required type="password"> <i class="fa-regular fa-eye-slash password-eye-icon"></i></div>
            <div class="error-text"id="password-error"></div>
          </div>
          <div class="form-group">
            <label for="reenter_password">Re-enter Password</label>
            <div class="password-container"><input class="form-input"name="reenter_password"id="reenter_password"required type="password"> <i class="fa-regular fa-eye-slash password-eye-icon"></i></div>
            <div class="error-text"id="reenter-password-error"></div>
          </div>
          <div class="form-group">
            <label for="country">Country</label>
            <div class="dropdown-container">
              <div class="input-with-chevron"id="country-input"style="padding-left:0"></div>
              <div class="dropdown-options"id="dropdown-options"></div>
              <i class="fa-regular fa-chevron-down dropdown-icon"id="chevron-icon"></i>
            </div>
          </div>
          <button class="form-button"type="submit">
            <div class="button-content"><span class="spinner"style="display:none"></span> <span class="button-text">Sign Up</span></div>
          </button>
        </form>
        <span class="span-login-redirect"style="--fontSize:var(--font-size-small);--lineHeight:var(--line-height-small)">Already have an account? <a class="a-login-redirect"onclick='window.location.href="login.php"'role="button">Log in</a></span>
        <div class="success-view">
          <i class="fas fa-check-circle"style="margin-bottom:20px"></i>
          <h2>Email Sent Successfully!</h2>
          <p>Please check your email to verify your account.</p>
          <p>If you don’t see the email in your inbox, please check your spam or junk folder. If it’s there, be sure to mark it as 'Not Spam' to avoid future issues.</p>
          <p>Verification email sent to <span id="sent-email"></span></p>
          <p>If you don’t see the email in your inbox, please check your spam or junk folder. If it’s there, be sure to mark it as "Not Spam" to avoid future issues.</p>
          <button class="change-button">Change Email</button> <a class="email-link"href="https://mail.google.com/mail/u/0/#inbox"target="_blank"><i class="fa-envelope far"></i> Open Email</a>
        </div>
      </div>
    </main>
    <!--<script src="https://apis.google.com/js/platform.js" async defer></script>-->
    <script defer>
      document.addEventListener('DOMContentLoaded', function() {
          const form = document.getElementById('signupForm');
          const rightSection = document.querySelector('.right-section');
          const spinner = document.querySelector('.spinner');
          const buttonText = document.querySelector('.button-text');
          const successView = document.querySelector('.success-view');

          // Store form data to preserve on email change
          let preservedFormData = {};

          form.addEventListener('submit', function(e) {
              e.preventDefault();

              // Clear previous errors
              document.querySelectorAll('.error-text').forEach(el => {
                  el.style.display = 'none';
                  el.textContent = '';
              });
              document.querySelectorAll('.form-input').forEach(el => {
                  el.classList.remove('error');
              });

              const email = document.getElementById('email');
              const name = document.getElementById('name');
              const lastname = document.getElementById('lastname');
              const password = document.getElementById('password');
              const reenterPassword = document.getElementById('reenter_password');
              const country = document.getElementById('country-input').dataset.flag;

              let hasError = false;

              // Validate email
              if (!email.value.trim()) {
                  showError(email, 'Email is required');
                  hasError = true;
              }

              // Validate name
              if (name.value.trim().length < 3 || name.value.trim().length > 15) {
                  showError(name, 'Name must be between 3 and 15 characters');
                  hasError = true;
              }

              // Validate lastname
              if (lastname.value.trim().length < 3 || lastname.value.trim().length > 30) {
                  showError(lastname, 'Last name must be between 3 and 30 characters');
                  hasError = true;
              }

              // Validate password match
              if (password.value !== reenterPassword.value) {
                  showError(reenterPassword, 'Passwords do not match');
                  hasError = true;
              }

              // Validate country selection
              if (!country) {
                  const countryInput = document.getElementById('country-input');
                  countryInput.classList.add('error');
                  hasError = true;
              }

              if (hasError) return;

              // Store form data
              preservedFormData = {
                  name: name.value.trim(),
                  lastname: lastname.value.trim(),
                  password: password.value,
                  reenterPassword: reenterPassword.value,
                  country: country
              };

              // Show loading state
              spinner.style.display = 'inline-block';
              buttonText.style.display = 'none';
              form.querySelector('button').disabled = true;

              // Submit form
              const formData = new FormData(form);
              formData.append('ajax', true);
              formData.append('country', country);

              fetch(form.action, {
                  method: 'POST',
                  body: formData
              })
              .then(response => response.json())
              .then(data => {
                  spinner.style.display = 'none';
                  buttonText.style.display = 'inline-block';
                  form.querySelector('button').disabled = false;

                  if (data.error) {
                      if (data.passwordErrors) {
                          showPasswordErrors(password, data.passwordErrors);
                      } else {
                          // Show general error message
                          const errorField = determineErrorField(data.message);
                          showError(document.getElementById(errorField), data.message);
                      }
                  } else {
                      rightSection.innerHTML = `
                          <div class="success-view" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; text-align: center;">
                              <i class="fas fa-check-circle" style="font-size: 80px; color: #4CAF50; margin-bottom: 20px;"></i>
                              <h2>Email Sent Successfully!</h2>
                              <p>Verification email sent to ${email.value}</p>
                              <p>Please check your email for verification.</p>
                              <div style="display: flex; gap: 10px; margin-top: 20px;">
                                  <button id="change-email-btn" class="change-button" style="background-color: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">
                                      Change Email
                                  </button>
                                  <a href="https://mail.google.com/mail/u/0/#inbox" target="_blank" class="email-link" style="background-color: #17a2b8; color: white; padding: 10px 20px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center;">
                                      Open Email
                                  </a>
                              </div>
                          </div>
                      `;

                      document.getElementById('change-email-btn').addEventListener('click', function() {
                          rightSection.innerHTML = `
                              <h1>Change Email</h1>
                              <form id="signupForm" method="POST">
                                  <div class="form-group">
                                      <label for="email">Email</label>
                                      <input type="email" id="email" name="email" class="form-input" required>
                                  </div>
                                  <div class="form-group">
                                      <label for="name">Name</label>
                                      <input type="text" id="name" name="name" class="form-input" required minlength="3" maxlength="15" value="${preservedFormData.name}">
                                  </div>
                                  <div class="form-group">
                                      <label for="lastname">Last Name</label>
                                      <input type="text" id="lastname" name="lastname" class="form-input" required minlength="3" maxlength="30" value="${preservedFormData.lastname}">
                                  </div>
                                  <div class="form-group">
                                      <label for="password">Password</label>
                                      <div class="password-container">
                                          <input type="password" id="password" name="password" class="form-input" required value="${preservedFormData.password}">
                                          <i class="fa-regular fa-eye-slash password-eye-icon"></i>
                                      </div>
                                      <div class="error-text" id="password-error"></div>
                                  </div>
                                  <div class="form-group">
                                      <label for="reenter_password">Re-enter Password</label>
                                      <div class="password-container">
                                          <input type="password" id="reenter_password" name="reenter_password" class="form-input" required value="${preservedFormData.reenterPassword}">
                                          <i class="fa-regular fa-eye-slash password-eye-icon"></i>
                                      </div>
                                      <div class="error-text" id="reenter-password-error"></div>
                                  </div>
                                  <div class="form-group">
                                      <label for="country">Country</label>
                                      <div class="dropdown-container">
                                          <div id="country-input" class="input-with-chevron" style="padding-left: 0px;"></div>
                                          <div id="dropdown-options" class="dropdown-options" value="${preservedFormData.country}"></div>
                                          <i id="chevron-icon" class="dropdown-icon fa-regular fa-chevron-down"></i>
                                      </div>
                                  </div>
                                  <button class="form-button" type="submit">
                                      <div class="button-content">
                                          <span class="spinner" style="display: none;"></span>
                                          <span class="button-text">Update Email</span>
                                      </div>
                                  </button>
                              </form>
                          `;

                          const countryInput = document.getElementById('country-input');
                          const dropdownOptions = document.getElementById('dropdown-options');
                          countryInput.dataset.flag = preservedFormData.country;

                          initCountryDropdown();
                          initPasswordToggle();
                      });
                  }
              })
              .catch(error => {
                console.error('Error:', error);
                spinner.style.display = 'none';
                buttonText.style.display = 'inline-block';
                form.querySelector('button').disabled = false;
                alert('An error occurred. Please try again.');
            });
        });

        function showError(inputElement, message) {
            inputElement.classList.add('error');
            const errorElement = inputElement.parentElement.querySelector('.error-text') || 
                               inputElement.parentElement.parentElement.querySelector('.error-text');
            if (errorElement) {
                errorElement.textContent = message;
                errorElement.style.display = 'block';
            }
        }

        function showPasswordErrors(passwordInput, errors) {
            passwordInput.classList.add('error');
            const errorDiv = passwordInput.parentElement.parentElement.querySelector('.error-text');
            const errorList = document.createElement('ul');
            errorList.className = 'password-error-list';
            errors.forEach(error => {
                const li = document.createElement('li');
                li.textContent = error;
                errorList.appendChild(li);
            });
            errorDiv.innerHTML = '';
            errorDiv.appendChild(errorList);
            errorDiv.style.display = 'block';
        }

        function determineErrorField(message) {
            if (message.toLowerCase().includes('email')) return 'email';
            if (message.toLowerCase().includes('name')) return 'name';
            if (message.toLowerCase().includes('password')) return 'password';
            return 'email'; // default to email field
        }
      })
    </script>

    <script type="module"id="add-country-script">
      fetch('country.json')
      .then(response => response.json())
      .then(data => {
        const countryInput = document.getElementById('country-input');
        const dropdownOptions = document.getElementById('dropdown-options');
        dropdownOptions.innerHTML = '';
        const specialCountries = ['DE', 'IN', 'PK', 'FR'];
      
        const createClickHandler = (code) => {
          return () => {
            countryInput.innerHTML = '';
            const container = document.createElement('div');
            container.style.display = 'flex';
            container.style.alignItems = 'center';
            container.style.paddingLeft = '4px';
      
            const flagImg = document.createElement('img');
            flagImg.classList.add('flag-icon');
            flagImg.src = `flags/${code.toLowerCase()}.svg`;
            flagImg.alt = data[code];
      
            const countryText = document.createTextNode(data[code]);
            const textSpan = document.createElement('span');
            textSpan.appendChild(countryText);
      
            if (code === "GS" || data[code] === "South Georgia and South Sandwich Islands") {
              container.style.fontSize = '14px';
            }
      
            container.appendChild(flagImg);
            container.appendChild(textSpan);
            countryInput.appendChild(container);
            countryInput.dataset.flag = code.toLowerCase();
            dropdownOptions.classList.remove('show');
          };
        };
      
        specialCountries.forEach(code => {
          const optionDiv = document.createElement('div');
          optionDiv.classList.add('dropdown-option');
          optionDiv.setAttribute('data-country-code', code);
          optionDiv.innerHTML = `<img class="flag-icon" src="flags/${code.toLowerCase()}.svg" alt="${data[code]}">${data[code]}`;
          optionDiv.onclick = createClickHandler(code);
          dropdownOptions.appendChild(optionDiv);
        });
      
        const separator = document.createElement('div');
        separator.classList.add('dropdown-separator');
        separator.innerText = '--------------';
        dropdownOptions.appendChild(separator);
      
        Object.keys(data).forEach(code => {
          const optionDiv = document.createElement('div');
          optionDiv.classList.add('dropdown-option');
          optionDiv.setAttribute('data-country-code', code);
          optionDiv.innerHTML = `<img class="flag-icon" src="flags/${code.toLowerCase()}.svg" alt="${data[code]}">${data[code]}`;
          optionDiv.onclick = createClickHandler(code);
          dropdownOptions.appendChild(optionDiv);
        });
      
        countryInput.onclick = (event) => {
          event.stopPropagation();
          dropdownOptions.classList.toggle('show');
        };
      
        document.addEventListener('click', (e) => {
          if (!dropdownOptions.contains(e.target) && !countryInput.contains(e.target)) {
            dropdownOptions.classList.remove('show');
          }
        });
      })
      .catch(error => console.error('Error loading country data:', error));
    </script>
    <script type="text/javascript"id="show-password-script">
      function togglePasswordVisibility(inputId, iconElement) {
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = iconElement;
      
        if (passwordInput.type === 'password') {
          passwordInput.type = 'text';
          eyeIcon.classList.remove('fa-eye-slash');
          eyeIcon.classList.add('fa-eye');
        } else {
          passwordInput.type = 'password';
          eyeIcon.classList.remove('fa-eye');
          eyeIcon.classList.add('fa-eye-slash');
        }
      }
      
      document.querySelectorAll('.password-eye-icon').forEach(icon => {
        icon.addEventListener('click', function () {
          const inputId = icon.previousElementSibling.id;
          togglePasswordVisibility(inputId, icon);
        });
      });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript"id="translating-script">
      function googleTranslateElementInit(){new google.translate.TranslateElement({pageLanguage:"en"},"google_translate_element")}
    </script>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
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
      });
    </script>
    <script type="text/javascript"src="translator.js"></script>
    <script>
      const observer = new MutationObserver(function(mutations) {
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
      });
    </script><script>document.addEventListener('DOMContentLoaded', function () {
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
      }
    </script>
  </body>
</html>