</main>
  <footer>
            <div class="footer-content">
                <p>&copy; <?php echo date("Y"); ?> TenKeep. All rights reserved.</p>
                <nav class="footer-nav">
                    <a href="#">Privacy Policy</a>
                    <a href="../terms_of_service.php">Terms of Service</a>
                    <a href="../profile.php">Contact Us</a>
                </nav>
            </div>
        </footer>
    </div>
</body>
</html>

<style>
    footer {
        background-color: #333;
        color: #f8f9fa;
        padding: 20px 0;
        text-align: center;
        border-top: 1px solid #555;
        position: relative;}

    .footer-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 20px;
    }

    footer p {
        margin: 0;
        font-size: 0.9em;
    }

    .footer-nav a {
        color: #f8f9fa;
        text-decoration: none;
        margin-left: 15px;
        font-size: 0.9em;
        transition: color 0.3s ease;
    }

    .footer-nav a:hover {
        color: #ddd;
    }

    .footer-nav a:first-child {
        margin-left: 0;
    }
</style>