<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | LinkPulse</title>

    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/auth.css">
    <script src="public/js/login.js" defer></script>
</head>

<body>
    <main class="auth-container">
        <section class="auth-card">

            <a href="index.php" class="auth-brand">
                LinkPulse
            </a>

            <h1>Welcome back</h1>

            <p class="auth-subtitle">
                Log in to manage your links and view your analytics.
            </p>

            <form id="loginForm">

                <div class="form-group">
                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="255"
                        autocomplete="email"
                        placeholder="you@example.com"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <p
                    id="formMessage"
                    class="form-message"
                    role="status"
                    aria-live="polite"
                ></p>

                <button type="submit" id="loginButton">
                    Log In
                </button>

            </form>

            <p class="auth-footer">
                Don't have an account?
                <a href="register.php">Create an account</a>
            </p>

        </section>
    </main>
</body>
</html>