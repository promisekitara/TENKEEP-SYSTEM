<?php
require_once '../includes/header.php';
?>

<style>
    .contact-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 600px;
        color: #333;
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
        background-color: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .contact-info p {
        margin-bottom: 10px;
        line-height: 1.6;
    }

    .contact-info strong {
        font-weight: bold;
        color: #555;
    }

    .contact-form {
        background-color: #fff;
        padding: 20px;
        border-radius: 6px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .contact-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: bold;
        color: #555;
    }

    .contact-form input[type="text"],
    .contact-form input[type="email"],
    .contact-form textarea {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        font-size: 1em;
    }

    .contact-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    .contact-form button {
        background-color: #28a745;
        color: white;
        padding: 10px 15px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 1em;
        transition: background-color 0.3s ease;
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
        <p>If you have any questions, feedback, or need assistance, please feel free to reach out to us.</p>
        <p><strong>Email:</strong> <a href="mailto:kitarapromise34@Tenkeep.com">kitarapromise34@Tenkeep.com</a></p>
        <p><strong>Phone:</strong> [Your Phone Number Here (Optional)]</p>
        <p><strong>Address:</strong> [Your Physical Address Here (Optional)]</p>
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
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Send Message</button>
            </form>
    </div>
</div>

<?php
require_once '../includes/footer.php';
?>