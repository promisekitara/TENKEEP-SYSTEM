<?php
session_start();
require_once '../config/db.php';
require_once 'functions.php';
require_once 'header.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>

<style>
    .terms-page {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        max-width: 90%;
        color: #333;
        line-height: 1.6;
    }

    .terms-page h2 {
        color: #007bff;
        text-align: center;
        margin-bottom: 25px;
    }

    .terms-page h3 {
        color: #28a745;
        margin-top: 20px;
        margin-bottom: 10px;
    }

    .terms-page p {
        margin-bottom: 15px;
    }

    .terms-page ul {
        padding-left: 20px;
        margin-bottom: 15px;
    }

    .terms-page li {
        margin-bottom: 8px;
    }

    .terms-page strong {
        font-weight: bold;
    }

    .terms-page a {
        color: #007bff;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .terms-page a:hover {
        text-decoration: underline;
        color: #0056b3;
    }
</style>

<div class="terms-page">
    <h2>Terms of Service for TenKeep</h2>

    <p><strong>Last Updated:</strong> May 16, 2025</p>

    <p>Welcome to TenKeep! These Terms of Service ("Terms") govern your use of the TenKeep website, applications, and services (collectively, the "Services"). By accessing or using our Services, you agree to be bound by these Terms.</p>

    <h3>1. Acceptance of Terms</h3>
    <p>By accessing or using the Services, you confirm that you can form a binding contract with TenKeep and that you agree to these Terms. If you do not agree to these Terms, you may not use the Services.</p>

    <h3>2. Description of Services</h3>
    <p>TenKeep provides a platform to facilitate the relationship between property owners/managers and tenants, including features for property listings (for owners), communication, complaint management, and payment tracking. The specific features available to you will depend on your user role (owner or tenant).</p>

    <h3>3. User Accounts</h3>
    <ul>
        <li><strong>Tenant Accounts:</strong> Tenant accounts are created and managed by property owners or their authorized representatives. As a tenant, your access to the Services is contingent upon your property owner creating an account for you.</li>
        <li><strong>Owner Accounts:</strong> Property owners are responsible for creating and managing their own accounts and any associated tenant accounts for their properties.</li>
        <li>You are responsible for maintaining the confidentiality of your account credentials. Owners are responsible for the actions of the tenant accounts they create.</li>
        <li>You agree to provide accurate, current, and complete information during any registration processes (whether as an owner or when an owner creates a tenant account) and to update such information to keep it accurate, current, and complete.</li>
        <li>TenKeep reserves the right to suspend or terminate any account if any information provided is found to be inaccurate, false, misleading, or if these Terms are violated.</li>
    </ul>

    <h3>4. User Conduct</h3>
    <p>You agree not to:</p>
    <ul>
        <li>Use the Services for any illegal purpose.</li>
        <li>Violate any applicable laws or regulations, including property and tenancy laws of Uganda.</li>
        <li>Infringe upon the intellectual property rights of TenKeep or any third party.</li>
        <li>Transmit any viruses, worms, or other malicious code.</li>
        <li>Attempt to gain unauthorized access to the Services or other users' accounts.</li>
        <li>Interfere with or disrupt the integrity or performance of the Services.</li>
        <li>Harass, threaten, or defame other users.</li>
        <li>Provide false or misleading information.</li>
    </ul>

    <h3>5. Limitation of Liability Regarding User Interactions</h3>
    <p>You acknowledge and agree that TenKeep provides a platform to facilitate interactions between property owners/managers and tenants. <strong>TenKeep is not responsible for any acts, omissions, or conduct of any user of the Services, including but not limited to any fraudulent, dishonest, or treacherous behavior.</strong> All interactions, agreements, and transactions between owners/managers and tenants are solely between those parties. TenKeep disclaims any liability arising from such interactions.</p>

    <h3>6. Content</h3>
    <ul>
        <li>Users may be able to submit, post, or display content through the Services ("User Content"). You retain ownership of your User Content.</li>
        <li>By submitting User Content, you grant TenKeep a non-exclusive, worldwide, royalty-free, sub-licensable, transferable license to use, reproduce, distribute, prepare derivative works of, display, and perform your User Content in connection with the Services and TenKeep’s (and its successors' and affiliates') business, including without limitation for promoting and redistributing part or all of the Services (and derivative works thereof) in any media formats and through any media channels.</li>
        <li>You represent and warrant that you have all the rights, power, and authority necessary to grant the rights granted herein to any User Content that you submit.</li>
        <li>TenKeep reserves the right to remove or refuse to distribute any User Content that violates these Terms or that TenKeep, in its sole discretion, deems objectionable.</li>
    </ul>

    <h3>7. Payments (If applicable)</h3>
    <p>If the Services include payment processing:</p>
    <ul>
        <li>You agree to pay the fees as outlined within the Services for any paid features or transactions.</li>
        <li>TenKeep may use third-party payment processors to facilitate payments. Your use of such services is subject to the third party's terms and conditions.</li>
        <li>You are responsible for ensuring that your payment information is accurate and up-to-date.</li>
    </ul>

    <h3>8. Intellectual Property</h3>
    <p>The Services and their original content (excluding User Content), features, and functionality are and will remain the exclusive property of TenKeep and its licensors. The Services are protected by copyright, trademark, and other laws. Our trademarks and trade dress may not be used in connection with any product or service without the prior written consent of TenKeep.</p>

    <h3>9. Termination</h3>
    <p>TenKeep may terminate or suspend your access to all or any part of the Services at any time, with or without cause, with or without notice, effective immediately. If you wish to terminate your account, you may simply discontinue using the Services. All provisions of the Terms which by their nature should survive termination shall survive termination, including, without limitation, ownership provisions, warranty disclaimers, indemnity, and limitations of liability.</p>

    <h3>10. Disclaimer of Warranties</h3>
    <p>THE SERVICES ARE PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. TENKEEP MAKES NO WARRANTIES, EXPRESSED OR IMPLIED, INCLUDING, BUT NOT LIMITED TO, WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, AND NON-INFRINGEMENT. TENKEEP DOES NOT WARRANT THAT THE SERVICES WILL BE UNINTERRUPTED OR ERROR-FREE.</p>

    <h3>11. Limitation of Liability</h3>
    <p>TO THE FULLEST EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL TENKEEP, ITS AFFILIATES, DIRECTORS, OFFICERS, EMPLOYEES, AGENTS, SUPPLIERS, OR LICENSORS BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES (INCLUDING, WITHOUT LIMITATION, DAMAGES FOR LOSS OF PROFITS, DATA, USE, GOODWILL, OR OTHER INTANGIBLE LOSSES) ARISING OUT OF OR RELATING TO YOUR ACCESS TO OR USE OF, OR YOUR INABILITY TO ACCESS OR USE, THE SERVICES.</p>

    <h3>12. Indemnification</h3>
    <p>You agree to indemnify, defend, and hold harmless TenKeep and its affiliates, directors, officers, employees, agents, suppliers, and licensors from and against any and all claims, liabilities, damages, losses, costs, expenses, or fees (including reasonable attorneys' fees) arising out of or relating to your use of the Services, your User Content, or your breach of these Terms.</p>

    <h3>13. Governing Law</h3>
    <p>These Terms shall be governed by and construed in accordance with the laws of Uganda, without regard to its conflict of law provisions.</p>

    <h3>14. Changes to These Terms</h3>
    <p>TenKeep reserves the right to modify or replace these Terms at any time. If a revision is material, we will provide at least 30 days' notice prior to any new terms taking effect. What constitutes a material change will be determined at our sole discretion. By continuing to access or use our Services after those revisions become effective, you agree to be bound by the revised terms. If you do not agree to the new terms, please stop using the Services.</p>

    <h3>15. Contact Us</h3>
    <p>If you have any questions about these Terms, please contact us at: <a href="mailto:kitarapromise34@Tenkeep.com">kitarapromise34@Tenkeep.com</a></p>
</div>

<?php
require_once 'footer.php';
?>