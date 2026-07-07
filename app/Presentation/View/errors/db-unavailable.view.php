<?php

declare(strict_types=1);

/*
 * Standalone error page — intentionally uses no Translator or DB access,
 * because this page is shown precisely when the database is unavailable.
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Service Unavailable — Software Compass</title>
    <link rel="stylesheet" href="/assets/app.css">
</head>
<body class="auth-page">
<main class="auth-shell" aria-labelledby="error-title">
    <section class="auth-hero" aria-hidden="true">
        <div class="auth-brand">SC</div>
        <p class="auth-kicker">Software Compass</p>
    </section>

    <section class="panel auth-card" aria-labelledby="error-title">
        <p class="auth-kicker">503 Service Unavailable</p>
        <h1 id="error-title">Database unavailable</h1>
        <p class="muted">
            Software Compass cannot connect to its database right now.
            This is usually a temporary issue. Please try again in a moment.
        </p>
        <p class="muted">
            If the problem persists, contact your system administrator.
        </p>
    </section>
</main>
</body>
</html>
