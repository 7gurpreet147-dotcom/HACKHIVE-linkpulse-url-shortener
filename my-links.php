
<?php
require_once __DIR__ . "/config/session.php";

if (
    !empty($_SESSION["logged_in"]) &&
    !empty($_SESSION["user_id"])
) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Links | LinkPulse</title>

    <link rel="stylesheet" href="public/css/dashboard.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .sidebar .nav-link {
            width: 100%;
        }

        .sidebar-bottom .nav-link {
            font-size: 0.95rem;
        }

        .guest-note {
            margin: 20px 10px;
            color: var(--muted);
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .mobile-nav {
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar .nav-link {
                width: auto;
            }

            .mobile-nav {
                display: inline-flex;
            }

            .desktop-nav {
                display: none !important;
            }
        }
    </style>
</head>

<body>
<div class="dashboard-layout">

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="index.php" class="brand">Link<span>Pulse</span></a>

        <p class="sidebar-label">WORKSPACE</p>

        <a href="#overview" class="nav-link active">
            <span>▦</span> Overview
        </a>

        <a href="#links-section" class="nav-link">
            <span>↗</span> My Links
        </a>

        <a href="#analytics-section" class="nav-link">
            <span>◷</span> Analytics
        </a>

        <p class="guest-note">
            Anonymous workspace<br>
            Your links are saved in this browser.
        </p>

        <div class="sidebar-bottom">
            <a href="index.php#shortener" class="nav-link">
                <span>＋</span> Create Short Link
            </a>

            <a href="login.php" class="nav-link">
                <span>⇥</span> Log In
            </a>

            <a href="register.php" class="nav-link">
                <span>♙</span> Sign Up
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <main class="main-content">

        <header class="topbar" id="overview">
            <div>
                <p class="eyebrow">ANONYMOUS WORKSPACE</p>
                <h1>Dashboard</h1>
                <p class="subtitle">
                    Monitor your links and understand their performance.
                </p>
            </div>

            <a href="index.php#shortener" class="create-btn">
                + Create Link
            </a>
        </header>

        <div id="dashboardMessage" role="status" aria-live="polite"></div>

        <!-- Statistics -->
        <section class="stats-grid">
            <article class="stat-card">
                <div class="stat-top">
                    <span>Total Links</span>
                    <span class="stat-icon purple">↗</span>
                </div>
                <h2 id="totalLinks">0</h2>
                <p>Links created in this browser</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Total Clicks</span>
                    <span class="stat-icon cyan">⌁</span>
                </div>
                <h2 id="totalClicks">0</h2>
                <p>Recorded visits</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Active Links</span>
                    <span class="stat-icon green">✓</span>
                </div>
                <h2 id="activeLinks">0</h2>
                <p>Available links</p>
            </article>

            <article class="stat-card">
                <div class="stat-top">
                    <span>Expired Links</span>
                    <span class="stat-icon orange">◷</span>
                </div>
                <h2 id="expiredLinks">0</h2>
                <p>Expired or click-limited links</p>
            </article>
        </section>

        <!-- Links table -->
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
                    aria-label="Search your links"
                >
            </div>

            <div style="overflow-x:auto">
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

        <!-- Click activity -->
        <section class="content-card" id="analytics-section">
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
            © <?php echo date("Y"); ?> LinkPulse.
            Every link tells a story.
        </footer>
    </main>
</div>

<script>
const totalLinksElement = document.getElementById("totalLinks");
const totalClicksElement = document.getElementById("totalClicks");
const activeLinksElement = document.getElementById("activeLinks");
const expiredLinksElement = document.getElementById("expiredLinks");
const linksTableBody = document.getElementById("linksTableBody");
const dashboardMessage = document.getElementById("dashboardMessage");
const searchInput = document.getElementById("searchLinks");

let allLinks = [];
let clickChart = null;

const baseUrl = new URL(".", window.location.href);

function getShortUrl(code) {
    return new URL(encodeURIComponent(code), baseUrl).href;
}

function isLinkActive(link) {
    const expired = link.expires_at &&
        new Date(link.expires_at.replace(" ", "T")) <= new Date();

    const capped = link.max_clicks !== null &&
        Number(link.click_count) >= Number(link.max_clicks);

    return link.status === "active" && !expired && !capped;
}

async function loadDashboard() {
    try {
        const response = await fetch(
            "api/anonymous-dashboard-data.php",
            { cache: "no-store", credentials: "same-origin" }
        );

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Could not load dashboard.");
        }

        totalLinksElement.textContent = data.overview.total_links;
        totalClicksElement.textContent = data.overview.total_clicks;
        activeLinksElement.textContent = data.overview.active_links;
        expiredLinksElement.textContent = data.overview.expired_links;

        allLinks = data.recent_links || [];
        renderLinks(allLinks);

        await loadClickChart();
    } catch (error) {
        dashboardMessage.textContent =
            error.message || "Unable to load dashboard data.";
    }
}

function renderLinks(links) {
    linksTableBody.replaceChildren();

    if (links.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 6;
        cell.className = "empty-state";
        cell.textContent = "No links found. Create a short link to get started.";

        row.appendChild(cell);
        linksTableBody.appendChild(row);
        return;
    }

    for (const link of links) {
        const row = document.createElement("tr");

        const shortCell = document.createElement("td");
        const shortAnchor = document.createElement("a");
        shortAnchor.className = "short-link";
        shortAnchor.href = getShortUrl(link.short_code);
        shortAnchor.target = "_blank";
        shortAnchor.rel = "noopener noreferrer";
        shortAnchor.textContent = link.short_code;
        shortCell.appendChild(shortAnchor);

        const destinationCell = document.createElement("td");
        const destination = document.createElement("span");
        destination.className = "destination";
        destination.textContent = link.original_url;
        destination.title = link.original_url;
        destinationCell.appendChild(destination);

        const clicksCell = document.createElement("td");
        clicksCell.textContent = link.click_count;

        const statusCell = document.createElement("td");
        const badge = document.createElement("span");
        const active = isLinkActive(link);

        badge.className = `status-badge${active ? "" : " inactive"}`;
        badge.textContent = active ? "Active" :
            (link.status === "disabled" ? "Disabled" : "Unavailable");

        statusCell.appendChild(badge);

        const createdCell = document.createElement("td");
        const createdDate = new Date(link.created_at.replace(" ", "T"));
        createdCell.textContent = Number.isNaN(createdDate.getTime())
            ? link.created_at
            : createdDate.toLocaleDateString();

        const actionsCell = document.createElement("td");

        const copyButton = document.createElement("button");
        copyButton.type = "button";
        copyButton.className = "action-btn";
        copyButton.textContent = "Copy";

        copyButton.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(getShortUrl(link.short_code));
                copyButton.textContent = "Copied!";
                setTimeout(() => copyButton.textContent = "Copy", 1200);
            } catch {
                dashboardMessage.textContent =
                    "Clipboard access failed. Copy the URL manually.";
            }
        });

        const openButton = document.createElement("button");
        openButton.type = "button";
        openButton.className = "action-btn";
        openButton.textContent = "Open";

        openButton.addEventListener("click", () => {
            window.open(
                getShortUrl(link.short_code),
                "_blank",
                "noopener,noreferrer"
            );
        });

        const statusButton = document.createElement("button");
        statusButton.type = "button";
        statusButton.className = "action-btn";
        statusButton.textContent = link.status === "active" ? "Disable" : "Enable";

        statusButton.addEventListener("click", async () => {
            const newStatus = link.status === "active" ? "disabled" : "active";

            if (!window.confirm(`Are you sure you want to ${newStatus === "disabled" ? "disable" : "enable"} this link?`)) {
                return;
            }

            statusButton.disabled = true;
            statusButton.textContent = "Updating...";

            try {
                const response = await fetch(
                    "api/anonymous-update-link-status.php",
                    {
                        method: "POST",
                        credentials: "same-origin",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({
                            id: link.id,
                            status: newStatus
                        })
                    }
                );

                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "Unable to update link status.");
                }

                dashboardMessage.textContent = result.message;
                await loadDashboard();
            } catch (error) {
                dashboardMessage.textContent =
                    error.message || "Something went wrong.";
                statusButton.disabled = false;
                statusButton.textContent = link.status === "active" ? "Disable" : "Enable";
            }
        });

        actionsCell.append(copyButton, openButton, statusButton);
        row.append(shortCell, destinationCell, clicksCell, statusCell, createdCell, actionsCell);
        linksTableBody.appendChild(row);
    }
}

searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim().toLowerCase();

    renderLinks(allLinks.filter(link =>
        link.short_code.toLowerCase().includes(query) ||
        link.original_url.toLowerCase().includes(query)
    ));
});

async function loadClickChart() {
    const response = await fetch(
        "api/anonymous-click-activity.php",
        { cache: "no-store", credentials: "same-origin" }
    );

    const data = await response.json();

    if (!response.ok || !data.success) {
        throw new Error(data.message || "Could not load click activity.");
    }

    if (clickChart) {
        clickChart.destroy();
    }

    clickChart = new Chart(document.getElementById("clickChart"), {
        type: "line",
        data: {
            labels: data.labels,
            datasets: [{
                label: "Clicks",
                data: data.values,
                borderColor: "#7c5cff",
                backgroundColor: "rgba(124, 92, 255, 0.14)",
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: "#c0c8da" }
                }
            },
            scales: {
                x: {
                    ticks: { color: "#98a4bc" },
                    grid: { color: "rgba(152, 164, 188, 0.08)" }
                },
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0, color: "#98a4bc" },
                    grid: { color: "rgba(152, 164, 188, 0.08)" }
                }
            }
        }
    });
}

loadDashboard();
</script>
</body>
</html>
