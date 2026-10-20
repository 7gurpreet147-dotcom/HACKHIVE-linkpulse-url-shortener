<?php

require_once __DIR__ . "/config/session.php";

// Redirect visitors who are not logged in.
if (
    empty($_SESSION["logged_in"]) ||
    empty($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | LinkPulse</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="public/css/dashboard.css">
</head>

<body>

<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="index.php" class="brand">
            Link<span>Pulse</span>
        </a>

        <p class="sidebar-label">WORKSPACE</p>

        <a href="dashboard.php" class="nav-link active">
            <span>▦</span> Overview
        </a>

        <a href="#links-section" class="nav-link">
            <span>↗</span> My Links
        </a>

        <a href="#analytics-section" class="nav-link">
            <span>◷</span> Analytics
        </a>

        <div class="sidebar-bottom">
            <a href="index.php" class="nav-link">
                <span>＋</span> Create Short Link
            </a>

            <button type="button" id="logoutButton" class="nav-link logout-link">
                <span>↪</span> Logout
            </button>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="eyebrow">WORKSPACE / OVERVIEW</p>
                <h1>Dashboard</h1>
                <p class="subtitle">
                    Monitor your links and understand their performance.
                </p>
            </div>

            <a href="index.php" class="create-btn">
                + Create Link
            </a>
        </header>

        <!-- Statistics -->
        <section class="stats-grid">

            <article class="stat-card">
                <div class="stat-top">
                    <span>Total Links</span>
                    <span class="stat-icon purple">↗</span>
                </div>
                <h2 id="totalLinks">—</h2>
                <p>Links created</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Total Clicks</span>
                    <span class="stat-icon cyan">⌁</span>
                </div>
                <h2 id="totalClicks">—</h2>
                <p>Recorded visits</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Active Links</span>
                    <span class="stat-icon green">✓</span>
                </div>
                <h2 id="activeLinks">—</h2>
                <p>Available links</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Expired Links</span>
                    <span class="stat-icon orange">◷</span>
                </div>
                <h2 id="expiredLinks">—</h2>
                <p>Past their expiry date</p>
            </article>

        </section>

        <!-- Links Table -->
        <section class="content-card" id="links-section">

            <div class="section-heading">
                <div>
                    <h2>Recent Links</h2>
                    <p>Your latest shortened URLs</p>
                </div>

                <input
                    type="search"
                    id="searchLinks"
                    class="search-input"
                    placeholder="Search links..."
                    aria-label="Search links"
                >
            </div>

            <div id="dashboardMessage" role="status"></div>

            <div class="table-responsive">
                <table class="links-table">
                    <thead>
                        <tr>
                            <th>SHORT LINK</th>
                            <th>DESTINATION</th>
                            <th>CLICKS</th>
                            <th>STATUS</th>
                            <th>CREATED</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>

                    <tbody id="linksTableBody">
                        <tr>
                            <td colspan="6" class="loading">
                                Loading your links...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- Analytics Preview -->
        <section class="content-card analytics-card" id="analytics-section">
            <div class="section-heading">
                <div>
                    <h2>Click Activity</h2>
                    <p>Recorded clicks over the last seven days</p>
                </div>
            </div>

            <div class="chart-container">
                <canvas id="clickChart"></canvas>
            </div>
        </section>

        <footer class="dashboard-footer">
            © <?php echo date("Y"); ?> LinkPulse · Every link tells a story.
        </footer>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="public/js/dashboard.js"></script>
<script src="public/js/logout.js"></script>

</body>
</html>