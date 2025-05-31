<?php
require_once '../includes/header.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        color: #333;
    }

    .contact-page {
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 800px; /* Increased max-width for better layout */
        background-color: #fff;
    }

    .contact-page h2 {
        color: #007bff;
        text-align: center;
        margin-bottom: 25px;
        font-size: 2.2em;
    }

    .contact-info {
        margin-bottom: 20px;
        padding: 15px;
        background-color: #f0f8ff; /* Light blue background for info section */
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        display: flex; /* Use flexbox for layout */
        align-items: center; /* Vertically align items */
        gap: 20px; /* Space between image and text */
    }

    .contact-info p {
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .contact-info strong {
        font-weight: bold;
        color: #555;
    }

    .developer-profile-img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #007bff; /* Border around image */
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .developer-details {
        flex-grow: 1; /* Allow details to take remaining space */
    }

    .contact-form {
        background-color: #fff;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }

    .contact-form h3 {
        color: #28a745;
        margin-bottom: 15px;
        text-align: center;
    }

    .contact-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #333;
    }

    .contact-form input[type="text"],
    .contact-form input[type="email"],
    .contact-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box; /* Include padding and border in the element's total width and height */
        font-size: 1em;
    }

    .contact-form textarea {
        resize: vertical; /* Allow vertical resizing only */
    }

    .contact-form button {
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1.1em;
        transition: background-color 0.3s ease;
        width: 100%;
    }

    .contact-form button:hover {
        background-color: #218838;
    }

    .contact-form .error-message {
        color: #dc3545;
        margin-top: 5px;
        font-size: 0.9em;
    }

    .contact-form .success-message {
        color: #198754;
        margin-top: 5px;
        font-size: 0.9em;
    }
</style>

<div class="contact-page">
    <h2>Contact Us</h2>

    <div class="contact-info">
        <div class="developer-details">
            <p>If you have any questions, feedback, or need assistance, please feel free to reach out to us.</p>
            <p><strong>Developer:</strong> Promise Kitara</p>
            <p><strong>Institution:</strong> Gulu University</p>
            <p><strong>Email:</strong> <a href="mailto:kitarapromise34@gmail.com">kitarapromise34@gmail.com</a></p>
            <p><strong>Phone:</strong> +256 781 259 927</p>
        </div>
    </div>

    <div class="contact-form">
        <h3>Send us a message</h3>
        <form action="#" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject">

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="6" required></textarea>

            <button type="submit">Send Message</button>
        </form>
    </div>
    <p style="text-align: center; margin-top: 20px;"><a href="terms_of_service.php">View our Terms of Service</a></p>
</div>

<?php
require_once '../includes/footer.php';
?>
