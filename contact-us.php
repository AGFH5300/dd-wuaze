<?php
// Initialize variables
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'vendor/autoload.php';

$successMessage = '';
$errorMessage = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($type) || empty($message)) {
        $errorMessage = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } else {
        try {
            // Create new PHPMailer instance for admin notification
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'diatech.ecotechsolutions@gmail.com';
            $mail->Password = 'qmofwmptlnfimurv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Admin notification email
            $mail->setFrom('diatech.ecotechsolutions@gmail.com', 'Website Feedback');
            $mail->addAddress('diatech.ecotechsolutions@gmail.com');
            $mail->isHTML(true);
            $mail->Subject = 'Website Feedback from ' . $name;
            $mail->Body = "Name: $name<br>Email: $email<br>Type: $type<br>Message: $message";
            $mail->AltBody = "Name: $name\nEmail: $email\nType: $type\nMessage: $message";
            
            // Send admin notification
            $mail->send();

            // Create new PHPMailer instance for user confirmation
            $confirmationMail = new PHPMailer(true);
            $confirmationMail->isSMTP();
            $confirmationMail->Host = 'smtp.gmail.com';
            $confirmationMail->SMTPAuth = true;
            $confirmationMail->Username = 'diatech.ecotechsolutions@gmail.com';
            $confirmationMail->Password = 'qmofwmptlnfimurv';
            $confirmationMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $confirmationMail->Port = 587;
            
            // User confirmation email
            $confirmationMail->setFrom('diatech.ecotechsolutions@gmail.com', 'Website Feedback');
            $confirmationMail->addAddress($email);
            $confirmationMail->isHTML(true);
            $confirmationMail->Subject = 'Thank You for Your Feedback';
            $confirmationMail->Body = "
            <div style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
                <div style='max-width: 600px; margin: auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);'>
                    <h2 style='color: #333;'>Dear $name,</h2>
                    <p style='color: #555;'>Thank you for your feedback! We have received your message and thank you for bringing this to our attention. We're reviewing your report and will work on a resolution.</p>
                    <p style='color: #555;'>Best regards,<br>Developer Team</p>
                </div>
            </div>";
            $confirmationMail->AltBody = "Dear $name,\nThank you for your feedback! We have received your message and thank you for bringing this to our attention. We're reviewing your report and will work on a resolution.\nBest regards,\nDeveloper Team";
            
            // Send user confirmation
            $confirmationMail->send();
            
            $successMessage = 'Feedback sent successfully!';
            $_POST = array(); // Clear form after successful submission
        } catch (Exception $e) {
            $errorMessage = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <link href="/favicon/favicon.ico" rel="icon" type="image/x-icon">
    <link href="/favicon/favicon-16x16.png" rel="icon" sizes="16x16" type="image/png">
    <link href="/favicon/favicon-32x32.png" rel="icon" sizes="32x32" type="image/png">
    <link href="/favicon/apple-touch-icon.png" rel="apple-touch-icon" sizes="180x180">
    <link href="/favicon/android-chrome-192x192.png" rel="icon" sizes="192x192">
    <link href="/favicon/android-chrome-512x512.png" rel="icon" sizes="512x512">
    <link href="/favicon/site.webmanifest" rel="manifest">
    <link href="all.css" rel="stylesheet">
    <link href="Lato.css" rel="stylesheet">
    <link href="main.css" rel="stylesheet">
    <style>
        /* Your existing CSS styles */
        .form-container.right-section * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .message-container {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            text-align: center;
        }

        .success-message {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
            color: #28a745;
        }

        .error-message {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
            color: #dc3545;
        }

        .form-group.error input,
        .form-group.error textarea {
            border-color: #dc3545;
        }

        .error-text {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
    <style>.form-container.right-section *{margin:0;padding:0;box-sizing:border-box}.form-container.right-section,.form-container.right-section html{background-color:#f4f4f9}.form-container.right-section{width:100%;height:100vh;background-color:#fff;display:flex;flex-direction:column;justify-content:center;align-items:center;padding:40px}.form-container.right-section h1{font-size:2em;margin-bottom:20px}.form-container.right-section .form-group{position:relative;margin:10px 0;width:100%}.form-container.right-section label{box-sizing:border-box;color:#5c6062;cursor:default;display:block;font-family:"ABC Diatype",sans-serif;font-size:10.07px;height:14px;left:16px;letter-spacing:.604px;line-height:14px;margin:0;padding:0;position:absolute;text-transform:uppercase;top:12px;width:auto;z-index:2;-webkit-font-smoothing:antialiased}.form-container.right-section .input-with-chevron,.form-container.right-section .select-dropdown,.form-container.right-section input,.form-container.right-section select{appearance:auto;background-color:#f3f3f3;border:2px solid #f3f3f3;border-bottom-color:#f3f3f3;border-radius:8px;height:62px;width:360px;color:#0e1525;font-family:"ABC Diatype",sans-serif;font-size:17.4px;font-weight:400;letter-spacing:.087px;line-height:24px;text-align:start;padding:26px 48px 8px 16px;cursor:text;margin:0;-webkit-font-smoothing:antialiased}.form-container.right-section .input-with-chevron{cursor:pointer}.form-container.right-section button{width:100%;padding:12px;background-color:#3498db;color:#fff;border:none;border-radius:4px;cursor:pointer;font-size:16px;margin-top:20px}.form-container.right-section .dropdown-container{position:relative;width:360px}.form-container.right-section .dropdown-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);font-size:16px;color:#333;pointer-events:none}.form-container.right-section .dropdown-options{display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border:1px solid;border-radius:8px;max-height:200px;overflow-y:auto;box-sizing:border-box;z-index:10}.form-container.right-section .dropdown-options.show{display:block}.form-container.right-section .dropdown-option{padding:10px;cursor:pointer;display:flex;align-items:center}.form-container.right-section .dropdown-option:hover{background-color:#f0f0f0}.form-container.right-section .dropdown-separator{padding:10px;text-align:center;color:#888}.form-container.right-section #country-input{padding-left:35px;background-repeat:no-repeat;background-position:left center;background-size:20px 15px;background-color:#f3f3f3;border:2px solid;border-radius:8px;height:62px;width:360px;color:#0e1525;font-family:"ABC Diatype",sans-serif;font-size:17.4px;line-height:24px;padding:26px 48px 8px 16px;text-align:start}.form-container.right-section .flag-icon{width:20px;height:15px;margin-right:10px;vertical-align:middle;margin-left:10px}.form-container.right-section .password-container{position:relative}.form-container.right-section .password-container input{width:100%;padding-right:40px}.form-container.right-section .password-eye-icon{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;font-size:18px}.form-container.right-section .span-login-redirect{display:block;font-family:"ABC Diatype",sans-serif;font-size:12.08px;font-weight:400;letter-spacing:.242px;line-height:16px;text-align:center;margin-top:20px;color:#07080a}.form-container.right-section .a-login-redirect{color:#3498db;cursor:pointer;font-family:"ABC Diatype",sans-serif;font-size:12.08px;font-weight:400;line-height:16px;text-align:center}.form-container.right-section .a-login-redirect:hover{text-decoration:underline}.form-container.right-section .success-view{display:none;text-align:center;padding:40px}.form-container.right-section .success-view i{color:#28a745;font-size:48px;margin-bottom:20px}.form-container.right-section .success-view h2{color:#28a745;margin-bottom:15px}.form-container.right-section .success-view p{color:#666;margin-bottom:25px}.form-container.right-section .change-button{background-color:#28a745;color:#fff;padding:10px 20px;border:none;border-radius:4px;cursor:pointer;font-size:16px}.form-container.right-section .change-button:hover{background-color:#218838}.form-container.right-section .spinner{display:inline-block;width:20px;height:20px;border:3px solid rgba(255,255,255,.3);border-radius:50%;border-top-color:#fff;animation:spin 1s ease-in-out infinite;margin-right:10px}.form-container.right-section .error-text{color:#dc3545;font-size:14px;margin-top:5px;display:none}.form-container.right-section .form-group{margin-bottom:20px}.form-container.right-section .error .error-text{display:block}.form-container.right-section .error input{border-color:#dc3545}textarea{resize:none;overflow-y:auto;min-height:62px;max-height:200px;border:2px solid #f3f3f3;background-color:#f3f3f3;border-radius:4px;color:#000}</style>
</head>
<body>
<nav class="navbar"><div class="container"><a href="home.html"><img src="logo.png" alt="Logo" class="logo"></a><ul class="nav-links"><li class="nav-item"><a href="home.html" class="nav-link" data-translate="home">Home</a></li><li class="nav-item"><a href="getting-started.html" class="nav-link" data-translate="gettingStarted" aria-haspopup="true" aria-expanded="false">Getting Started <i class="fa-regular fa-chevron-down"></i></a><div class="dropdown-content"><a href="important-documents.html" class="dropdown-link" data-translate="importantDocuments">Important Documents</a> <a href="housing.html" class="dropdown-link" data-translate="housing">Housing</a> <a href="healthcare.html" class="dropdown-link" data-translate="healthcare">Healthcare</a> <a href="cost-of-living.html" class="dropdown-link" data-translate="costOfLiving">Cost of Living</a></div></li><li class="nav-item"><a href="living-in-the-uae.html" class="nav-link" data-translate="livingInUAE" aria-haspopup="true" aria-expanded="false">Living in the UAE <i class="fa-regular fa-chevron-down"></i></a><div class="dropdown-content"><a href="culture-and-customs.html" class="dropdown-link" data-translate="cultureAndCustoms">Culture and Customs</a> <a href="transportation.html" class="dropdown-link" data-translate="transportation">Transportation</a> <a href="shopping.html" class="dropdown-link" data-translate="shopping">Shopping</a></div></li><li class="nav-item"><a href="education.html" class="nav-link" data-translate="education" aria-haspopup="true" aria-expanded="false">Education <i class="fa-regular fa-chevron-down"></i></a><div class="dropdown-content"><a href="curriculum-guides.html" class="dropdown-link" data-translate="curriculumGuides">Curriculum Guides</a> <a href="school-listings.html" class="dropdown-link" data-translate="schoolListings">School Listings</a></div></li><li class="nav-item"><a href="working-in-the-uae.html" class="nav-link" data-translate="workingInUAE" aria-haspopup="true" aria-expanded="false">Working in the UAE <i class="fa-regular fa-chevron-down"></i></a><div class="dropdown-content"><a href="labour-card.html" class="dropdown-link" data-translate="labourCard">Labour Card</a> <a href="job-market-insights.html" class="dropdown-link" data-translate="jobMarketInsights">Job Market Insights</a> <a href="labour-laws.html" class="dropdown-link" data-translate="labourLaws">Labour Laws</a> <a href="becoming-an-entrepreneur.html" class="dropdown-link" data-translate="becomingEntrepreneur">Becoming an Entrepreneur</a> <a href="networking-oppurtunities.html" class="dropdown-link" data-translate="networkingOpportunities">Networking Opportunities</a></div></li><li class="nav-item"><a href="community-and-support.html" class="nav-link" data-translate="communitySupport" aria-haspopup="true" aria-expanded="false">Community & Support <i class="fa-regular fa-chevron-down"></i></a><div class="dropdown-content"><a href="faq.php" class="dropdown-link" data-translate="faq">FAQ</a> <a href="contact-us.php" class="dropdown-link" data-translate="contactUs">Contact Us</a></div></li></ul><div class="nav-right"><div class="search-container"><input type="text" name="q" class="search-input" placeholder="Search..."><div class="search-icons-container"><i class="fas fa-times close-icon" id="close-icon"></i> <button type="button" class="search-btn" id="search-btn"><i class="fas fa-search"></i></button></div></div><div class="lang-dropdown"><button class="lang-btn"><i class="fal fa-globe" style="color:#fff"></i><div id="google_translate_element"></div></button></div><a href="sign-up.php" class="sign-up-btn" style="text-decoration:none" data-translate="signUp"><i class="fa-solid fa-user" style="margin-right:7px"></i> <span class="sign-up-text">Sign Up</span> <span><i class="fas fa-arrow-up-right sign-up-icon" style="color:#fff"></i></span></a></div></div></nav>
    <div class="form-container right-section">
        <h1>Contact Us</h1>
        
        <?php if(!empty($successMessage)): ?>
            <div class="message-container success-message">
                <?php echo htmlspecialchars($successMessage); ?>
            </div>
        <?php endif; ?>

        <?php if(!empty($errorMessage)): ?>
            <div class="message-container error-message">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form id="contactForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" name="name" id="name" required 
                    value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="type">Type</label>
                <select name="type" id="type" required class="select-dropdown">
                    <option value="">Select inquiry type</option>
                    <option value="General">General Inquiry</option>
                    <option value="Support">Technical Support</option>
                    <option value="Feedback">Feedback</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required style="padding:26px 48px 8px 16px"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            </div>

            <button type="submit" id="submitBtn">
                <div class="button-content">
                    <span class="spinner" style="display:none"></span>
                    <span class="button-text">Send Message</span>
                </div>
            </button>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contactForm');
        const submitBtn = document.getElementById('submitBtn');
        const spinner = submitBtn.querySelector('.spinner');
        const buttonText = submitBtn.querySelector('.button-text');

        // Form validation and submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Reset previous error states
            document.querySelectorAll('.form-group').forEach(group => {
                group.classList.remove('error');
                const errorText = group.querySelector('.error-text');
                if (errorText) errorText.remove();
            });

            // Validate form
            let isValid = true;
            const fields = {
                name: 'Name is required',
                email: 'Valid email is required',
                type: 'Please select an inquiry type',
                message: 'Message is required'
            };

            for (const [fieldId, errorMessage] of Object.entries(fields)) {
                const field = document.getElementById(fieldId);
                const fieldGroup = field.closest('.form-group');

                if (!field.value.trim()) {
                    isValid = false;
                    fieldGroup.classList.add('error');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-text';
                    errorDiv.textContent = errorMessage;
                    fieldGroup.appendChild(errorDiv);
                }

                // Additional email validation
                if (fieldId === 'email' && field.value.trim()) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(field.value.trim())) {
                        isValid = false;
                        fieldGroup.classList.add('error');
                        const errorDiv = document.createElement('div');
                        errorDiv.className = 'error-text';
                        errorDiv.textContent = 'Please enter a valid email address';
                        fieldGroup.appendChild(errorDiv);
                    }
                }
            }

            if (isValid) {
                // Show loading state
                spinner.style.display = 'inline-block';
                buttonText.textContent = 'Sending...';
                submitBtn.disabled = true;

                // Submit the form
                form.submit();
            }
        });

        // Real-time validation
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const fieldGroup = this.closest('.form-group');
                const errorText = fieldGroup.querySelector('.error-text');
                if (this.value.trim()) {
                    fieldGroup.classList.remove('error');
                    if (errorText) errorText.remove();
                }
            });
        });
    });
    </script>
</body>
</html>