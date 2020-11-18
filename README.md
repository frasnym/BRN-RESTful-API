# Community Member API - Lumen PHP Framework

Laravel Lumen is a stunningly fast PHP micro-framework for building web applications with expressive, elegant syntax. We believe development must be an enjoyable, creative experience to be truly fulfilling. Lumen attempts to take the pain out of development by easing common tasks used in the majority of web projects, such as routing, database abstraction, queueing, and caching.

## Features

<ol>
    <li>Register Member</li>
    <li>Login Member. Return "api_token" used for accessing API</li>
    <li>Logout Member. Delete "api_token" for corresponding member</li>
    <li>Middleware Auth. Checking api_token before accessing API</li>
    <li>Middleware Account Status. Checking account status before accessing API</li>
    <li>Request Email Verification. Save token which expired in specific time and make queue for email gateway</li>
    <li>Change Email Address. Change email address if registered email is mistyped</li>
    <li>Verify Email Address. Check suitability between email address and token key</li>
    <li>Send a HTML Email with <b>illuminate/mail</b> package</li>
    <li>Request Phone Verification. Save code which expired in specific time and make queue for sms gateway</li>
    <li>Send a SMS with <b><a href="http://dutasms.com/">DutaSMS</a></b></li>
    <li>Verify Phone Number. Check suitability between phone number and token key</li>
    <li>Change Phone Number. Change phone number if registered number is mistyped</li>
    <li>Payment Method List. Get supported payment method</li>
    <li>Request Payment using Payment Gateway <b><a href="https://www.xendit.co/en/">Xendit</a></b>. Support: Invoice</li>
    <li>Registration Request Payment</li>
    <li>Microservices to check any data expired (key, payment)</li>
    <li>Handle callback from payment gateway</li>
</ol>

## Tools Used

| Name | Version |
| ------ | ------ |
| [Composer](https://getcomposer.org/) | 2.0.6 |
| [PHP](https://www.php.net/) | 7.4.12 |
| [Lumen](https://lumen.laravel.com/) | 8.2.0 |
| [PostgreSQL](https://www.postgresql.org/) | 13.0 |

### Installing
<ol>
    <li>
        Install required package
        <br>
        <pre><code>composer install</code></pre>
    </li>
    <li>
        Set <code>.env</code> file. If you don't have it, you can copy from <code>.env.example</code>
    </li>
    <li>
        Migrate to database
        <br>
        <pre><code>php artisan migrate</code></pre>
    </li>
</ol>

### Deploy to cPanel
<ol>
    <li>
        Upload to your directory
    </li>
    <li>
        Rename <code>server.php</code> in your Laravel root folder to <code>index.php</code>
    </li>
    <li>
        Copy the <code>.htaccess</code> file from /public directory to your Laravel root folder.
    </li>
</ol>
