<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php'; // Ensure this is also included if you use redirect() elsewhere in index.php

// If the user is logged in, redirect them to their respective dashboard
if (is_logged_in()) {
    if (get_user_role() === 'owner') {
        redirect('owner/dashboard.php');
    } elseif (get_user_role() === 'tenant') {
        redirect('tenant/dashboard.php');
    }
    // No else needed here, as the script will exit after redirection
}
// If the user is NOT logged in, display the landing page content below
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to TenKeep</title>
    <link rel="stylesheet" href="assets/css/style.css"> <style>
        /* Specific styles for the index page to ensure layout */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa; /* Light background from your style.css */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #343a40;
        }

        .main-content {
            flex: 1; /* Allows content to grow and push footer down */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            text-align: center;
        }

        .welcome-section {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
            max-width: 700px;
            width: 100%;
        }

        .welcome-section h1 {
            color: #007bff;
            font-size: 3em;
            margin-bottom: 20px;
        }

        .welcome-section p {
            font-size: 1.1em;
            line-height: 1.8;
            color: #555;
            margin-bottom: 25px;
        }

        .login-button {
            background-color: #28a745; /* Green button from your styles */
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            text-decoration: none; /* For the anchor tag */
            display: inline-block;
            margin-top: 20px;
        }

        .login-button:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }

        .contact-info-section {
            background-color: #f0f8ff; /* Light blue background */
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 30px;
            max-width: 700px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
        }

        .contact-info-section h2 {
            color: #007bff;
            font-size: 2em;
            margin-bottom: 15px;
        }

        .contact-info-section img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .contact-details p {
            margin: 8px 0;
            font-size: 1.05em;
            color: #444;
        }

        .contact-details strong {
            color: #333;
        }

        .contact-details a {
            color: #007bff;
            text-decoration: none;
        }

        .contact-details a:hover {
            text-decoration: underline;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .welcome-section, .contact-info-section {
                padding: 30px;
            }
            .welcome-section h1 {
                font-size: 2.5em;
            }
            .contact-info-section h2 {
                font-size: 1.8em;
            }
            .login-button {
                padding: 12px 25px;
                font-size: 1.1em;
            }
        }

        @media (max-width: 480px) {
            .welcome-section, .contact-info-section {
                padding: 20px;
            }
            .welcome-section h1 {
                font-size: 2em;
            }
            .contact-info-section h2 {
                font-size: 1.6em;
            }
            .login-button {
                padding: 10px 20px;
                font-size: 1em;
            }
            .contact-info-section img {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="welcome-section">
            <h1>Welcome to TenKeep!</h1>
            <p>Your ultimate solution for seamless property and tenant management. Whether you're a property owner looking to streamline your operations or a tenant seeking a hassle-free living experience, TenKeep has you covered.</p>
            <p>Manage properties, track payments, and handle complaints.</p>
            <a href="auth/login.php" class="login-button">Login to Your Account</a>
        </div>

        <div class="contact-info-section">
            <h2>Contact the Developer</h2>
            <?php
            // Check if roomie.jpg exists for the developer profile picture
            $developer_image_path = 'assets/images/ProfilePic.png'; // Assuming roomie.jpg is in assets/images
            $developer_image_exists = file_exists($developer_image_path);
            ?>
            <?php if ($developer_image_exists): ?>
                <img src="<?php echo $developer_image_path; ?>" alt="Developer Profile Picture">
            <?php else: ?>
                <img src="https://placehold.co/150x150/cccccc/333333?text=No+Image" alt="Placeholder Image">
            <?php endif; ?>
            <div class="contact-details">
                <p><strong>Developer:</strong> Promise Kitara</p>
                <p><strong>Institution:</strong> Gulu University</p>
                 <p><strong>Course:</strong>Bach Information and Communications Technology</p>

                <p><strong>Email:</strong> <a href="mailto:kitarapromise34@gmail.com">kitarapromise34@gmail.com</a></p>
                <p><strong>Phone:</strong> +256 781 259 927</p>
            </div>
        </div>
    </div>
    <?php // require_once 'includes/footer.php'; ?>
</body>
</html>
