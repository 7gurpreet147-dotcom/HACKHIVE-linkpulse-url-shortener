<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account | LinkPulse</title>

    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/auth.css">

    <script src="public/js/register.js" defer></script>
</head>
<body>
    <main class="auth-container">
        <section class="auth-card">
            <a href="index.php" class="auth-brand">LinkPulse</a>

            <h1>Create your account</h1>

            <p class="auth-subtitle">
                Join LinkPulse to manage your links and analytics.
            </p>

            <form id="registerForm">
                <div class="form-group">
                    <label for="name">Full name</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        maxlength="100"
                        autocomplete="name"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        maxlength="255"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >

                    <small>Use at least 8 characters.</small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm password</label>

                    <input
                        type="password"
                        id="confirmPassword"
                        name="confirmPassword"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <p
                    id="formMessage"
                    class="form-message"
                    role="status"
                    aria-live="polite"
                ></p>

                <button type="submit" id="registerButton">
                    Create Account
                </button>
            </form>

            <p class="auth-footer">
                Already have an account?
                <a href="login.php">Log in</a>
            </p>
        </section>
    </main>
</body>
</html>