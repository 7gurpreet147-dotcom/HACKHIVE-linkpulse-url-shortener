
<?php
require_once __DIR__ . "/config/session.php";

$isLoggedIn = (
    !empty($_SESSION["logged_in"]) &&
    !empty($_SESSION["user_id"])
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LinkPulse | Smart URL Shortener</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="public/css/style.css">
</head>

<body>

    <!-- First-visit loading intro -->
    <div class="intro-loader" id="introLoader" aria-hidden="true">
        <div class="intro-loader__content">
            <div class="intro-loader__brand">Link<span>Pulse</span></div>
            <div class="intro-loader__label">Connecting every link</div>
            <div class="intro-loader__percentage" id="introPercentage">0<span>%</span></div>
            <div class="intro-loader__track"><div class="intro-loader__progress" id="introProgress"></div></div>
            <div class="intro-loader__footnote">A smarter way to share your story</div>
        </div>
        <div class="intro-shutter intro-shutter--top"></div>
        <div class="intro-shutter intro-shutter--bottom"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">

            <a class="navbar-brand fw-bold" href="index.php">
                Link<span>Pulse</span>
            </a>

            <div class="ms-auto d-flex align-items-center gap-2">

                <?php if ($isLoggedIn): ?>

                    <a href="dashboard.php"
                       class="btn btn-outline-light btn-sm px-3">
                        Dashboard
                    </a>

                    <button
                        type="button"
                        id="homeLogoutButton"
                        class="btn btn-primary btn-sm px-3"
                    >
                        Logout
                    </button>

                <?php else: ?>

                    <a href="my-links.php"
                       class="btn btn-outline-light btn-sm px-3">
                        My Links
                    </a>

                    <a href="login.php"
                       class="btn btn-outline-light btn-sm px-3">
                        Log In
                    </a>

                    <a href="register.php"
                       class="btn btn-primary btn-sm px-3">
                        Sign Up
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="homeHero">
        <canvas class="hero-particle-canvas" id="heroParticleCanvas" aria-hidden="true"></canvas>
        <div class="hero-cursor-glow" id="heroCursorGlow" aria-hidden="true"></div>
        <div class="container text-center hero-content">

            <div class="hero-badge">
                ✦ Smart Link Management
            </div>

            <h1 class="hero-title">
                Every Link<br>
                <span>Tells a Story.</span>
            </h1>

            <p class="hero-description">
                Shorten links, understand your audience, and measure
                engagement — all from one powerful platform.
            </p>

            <!-- URL Shortener -->
            <div class="shortener-card mx-auto" id="shortener">

                <form id="shortenerForm">
                    <label for="originalUrl" class="form-label">
                        Paste your long URL
                    </label>

                    <div class="input-group">
                        <input
                            type="url"
                            class="form-control"
                            id="originalUrl"
                            placeholder="https://example.com/your-long-url"
                            required
                        >

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="shortenBtn"
                        >
                            Shorten URL
                        </button>
                    </div>

                    <div class="form-text">
                        Create a shorter link in seconds.
                    </div>
                </form>

                <!-- API messages -->
                <div
                    id="message"
                    class="mt-3"
                    role="status"
                    aria-live="polite"
                ></div>

                <!-- Generated URL -->
                <div
                    id="resultContainer"
                    class="result-container mt-4"
                    hidden
                >
                    <label for="shortUrl" class="form-label">
                        Your shortened link
                    </label>

                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            id="shortUrl"
                            readonly
                        >

                        <button
                            type="button"
                            class="btn btn-success"
                            id="copyBtn"
                        >
                            Copy
                        </button>
                    </div>

                    <a
                        href="#"
                        id="openShortUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="open-link"
                    >
                        Open short link ↗
                    </a>
                </div>

                <!-- Live Usage Indicators -->
                <div
                    id="usagePanel"
                    class="mt-4 text-start"
                    aria-live="polite"
                    hidden
                >
                    <hr>

                    <!-- Daily allowance: all visitors -->
                    <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                        <strong>Daily link allowance</strong>

                        <span
                            id="dailyUsageText"
                            class="small text-nowrap"
                        >
                            Loading...
                        </span>
                    </div>

                    <div
                        class="progress mb-3"
                        role="progressbar"
                        aria-label="Daily link usage"
                    >
                        <div
                            id="dailyUsageBar"
                            class="progress-bar"
                            style="width: 0%"
                        ></div>
                    </div>

                    <!-- IP allowance: anonymous visitors only -->
                    <div id="ipUsageSection">

                        <div class="d-flex justify-content-between align-items-center mb-2 gap-2">
                            <strong>IP request allowance</strong>

                            <span
                                id="ipUsageText"
                                class="small text-nowrap"
                            >
                                Loading...
                            </span>
                        </div>

                        <div
                            class="progress mb-3"
                            role="progressbar"
                            aria-label="IP request usage"
                        >
                            <div
                                id="ipUsageBar"
                                class="progress-bar"
                                style="width: 0%"
                            ></div>
                        </div>

                        <p class="small text-muted mb-3">
                            The IP request allowance is shared by
                            anonymous requests from the same detected
                            IP address and uses a rolling one-hour window.
                        </p>

                    </div>

                    <!-- Registration offer: anonymous visitors only -->
                    <div
                        id="registrationOffer"
                        class="alert alert-info mb-0"
                        hidden
                    >
                        <strong>Want more links?</strong>

                        Sign up and unlock up to 100 links per day.

                        <a href="register.php" class="alert-link">
                            Create your free account
                        </a>.
                    </div>

                </div>
            </div>

            <!-- Feature Highlights -->
            <div class="feature-row">

                <div class="feature-item">
                    <span class="feature-icon">↗</span>
                    <span>Simple URL Shortening</span>
                </div>

                <div class="feature-item">
                    <span class="feature-icon">◷</span>
                    <span>Click Analytics</span>
                </div>

                <div class="feature-item">
                    <span class="feature-icon">⌁</span>
                    <span>Smart Link Management</span>
                </div>

            </div>

        </div>
    </section>


    <!-- Team section -->
    <section class="team-section" id="team">
        <div class="container">

            <div class="team-heading text-center">
                <span class="team-eyebrow">THE PEOPLE BEHIND THE PULSE</span>
                <h2>Meet the <span>Team.</span></h2>
                <p>
                    Different strengths. One shared vision.
                    Building smarter connections with LinkPulse.
                </p>
            </div>

            <div class="team-grid">

                <!-- 01: Gurpreet Singh -->
                <article class="team-card">
                    <div class="team-card__topline">
                        <span>01</span>
                        <span class="team-card__dot"></span>
                    </div>

                    <div class="team-avatar">
                        <img
                            src="public/images/team/gurpreet.jpeg"
                            alt="Gurpreet Singh, Project Lead and System Architect"
                            loading="lazy"
                        >
                    </div>

                    <div class="team-card__role">
                        PROJECT LEAD &amp; SYSTEM ARCHITECT
                    </div>

                    <h3>Gurpreet Singh</h3>
                    <p class="team-card__degree">
                        BCA · AI &amp; Data Science
                    </p>
                    <p class="team-card__detail">
                        Guiding the vision, system design and overall
                        project direction.
                    </p>
                </article>

                <!-- 02: Gaurav Lohan -->
                <article class="team-card">
                    <div class="team-card__topline">
                        <span>02</span>
                        <span class="team-card__dot"></span>
                    </div>

                    <div class="team-avatar team-avatar--teal">
                        <img
                            src="public/images/team/gaurav.png"
                            alt="Gaurav Lohan, Frontend Developer"
                            loading="lazy"
                        >
                    </div>

                    <div class="team-card__role">
                        FRONTEND DEVELOPER
                    </div>

                    <h3>Gaurav Lohan</h3>
                    <p class="team-card__degree">
                        BCA · AI &amp; Data Science
                    </p>
                    <p class="team-card__detail">
                        Turning ideas into a clear, responsive and
                        engaging user experience.
                    </p>
                </article>

                <!-- 03: Bhumi Maurya -->
                <article class="team-card">
                    <div class="team-card__topline">
                        <span>03</span>
                        <span class="team-card__dot"></span>
                    </div>

                    <div class="team-avatar team-avatar--pink">
                        <img
                            src="public/images/team/bhumi.png"
                            alt="Bhumi Maurya, Backend Developer"
                            loading="lazy"
                        >
                    </div>

                    <div class="team-card__role">
                        BACKEND DEVELOPER
                    </div>

                    <h3>Bhumi Maurya</h3>
                    <p class="team-card__degree">
                        BTech · AI &amp; Machine Learning
                    </p>
                    <p class="team-card__detail">
                        Building the server-side logic that powers
                        the LinkPulse experience.
                    </p>
                </article>

                <!-- 04: Nandni -->
                <article class="team-card">
                    <div class="team-card__topline">
                        <span>04</span>
                        <span class="team-card__dot"></span>
                    </div>

                    <div class="team-avatar team-avatar--gold">
                        <img
                            src="public/images/team/nandni.png"
                            alt="Nandni, Database and Analytics Engineer"
                            loading="lazy"
                        >
                    </div>

                    <div class="team-card__role">
                        DATABASE &amp; ANALYTICS ENGINEER
                    </div>

                    <h3>Nandni</h3>
                    <p class="team-card__degree">
                        BTech · AI &amp; Data Science
                    </p>
                    <p class="team-card__detail">
                        Organising data and turning link activity
                        into useful insights.
                    </p>
                </article>

                <!-- 05: Soniya -->
                <article class="team-card">
                    <div class="team-card__topline">
                        <span>05</span>
                        <span class="team-card__dot"></span>
                    </div>

                    <div class="team-avatar team-avatar--blue">
                        <img
                            src="public/images/team/soniya.png"
                            alt="Soniya, Security and Deployment"
                            loading="lazy"
                        >
                    </div>

                    <div class="team-card__role">
                        SECURITY &amp; DEPLOYMENT
                    </div>

                    <h3>Soniya</h3>
                    <p class="team-card__degree">
                        BTech · Computer Science &amp; Engineering
                    </p>
                    <p class="team-card__detail">
                        Helping keep the project reliable, secure
                        and ready to run.
                    </p>
                </article>

            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="site-footer">
        <div class="container text-center">
            <p>
                © <?php echo date("Y"); ?> LinkPulse.
                Every link tells a story.
            </p>
        </div>
    </footer>

    <script src="public/js/main.js"></script>

    <!-- Homepage Logout -->
    <?php if ($isLoggedIn): ?>
    <script>
        const homeLogoutButton =
            document.getElementById("homeLogoutButton");

        homeLogoutButton.addEventListener("click", async () => {
            homeLogoutButton.disabled = true;
            homeLogoutButton.textContent = "Logging out...";

            try {
                const response = await fetch("api/logout.php", {
                    method: "POST",
                    credentials: "same-origin"
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || "Unable to log out."
                    );
                }

                window.location.href = "index.php";

            } catch (error) {
                alert(error.message || "Logout failed.");

                homeLogoutButton.disabled = false;
                homeLogoutButton.textContent = "Logout";
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>
