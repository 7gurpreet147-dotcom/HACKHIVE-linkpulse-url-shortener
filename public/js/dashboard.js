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

async function loadDashboard() {
    try {
        const response = await fetch("api/dashboard-data.php", {
            cache: "no-store"
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Could not load dashboard.");
        }

        totalLinksElement.textContent = data.overview.total_links;
        totalClicksElement.textContent = data.overview.total_clicks;
        activeLinksElement.textContent = data.overview.active_links;
        expiredLinksElement.textContent = data.overview.expired_links;

        allLinks = data.recent_links;

        renderLinks(allLinks);
        await loadClickChart();

    } catch (error) {
        dashboardMessage.textContent =
            error.message || "Unable to load dashboard data.";

        linksTableBody.replaceChildren();

        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 6;
        cell.className = "empty-state";
        cell.textContent = "Dashboard data could not be loaded.";

        row.appendChild(cell);
        linksTableBody.appendChild(row);
    }
}

function renderLinks(links) {
    linksTableBody.replaceChildren();

    if (links.length === 0) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");

        cell.colSpan = 6;
        cell.className = "empty-state";
        cell.textContent = "No links found.";

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

        const expired =
            link.expires_at &&
            new Date(link.expires_at.replace(" ", "T")) <= new Date();

        const capped =
            link.max_clicks !== null &&
            Number(link.click_count) >= Number(link.max_clicks);

        const active = link.status === "active" && !expired && !capped;

        badge.className = `status-badge${active ? "" : " inactive"}`;
        badge.textContent = active ? "Active" : "Unavailable";

        statusCell.appendChild(badge);

        const createdCell = document.createElement("td");
        const createdDate = new Date(
            link.created_at.replace(" ", "T")
        );

        createdCell.textContent = Number.isNaN(createdDate.getTime())
            ? link.created_at
            : createdDate.toLocaleDateString();

        const actionsCell = document.createElement("td");

        // Copy button
        const copyButton = document.createElement("button");
        copyButton.type = "button";
        copyButton.className = "action-btn";
        copyButton.textContent = "Copy";

        copyButton.addEventListener("click", async () => {
            try {
                await navigator.clipboard.writeText(
                    getShortUrl(link.short_code)
                );

                copyButton.textContent = "Copied!";

                setTimeout(() => {
                    copyButton.textContent = "Copy";
                }, 1200);

            } catch {
                dashboardMessage.textContent =
                    "Clipboard access failed. Copy the URL manually.";
            }
        });

        // Open button
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

        // Enable / Disable button
        const statusButton = document.createElement("button");
        statusButton.type = "button";
        statusButton.className = "action-btn";

        statusButton.textContent = link.status === "active"
            ? "Disable"
            : "Enable";

        statusButton.addEventListener("click", async () => {
            const currentlyActive = link.status === "active";

            const newStatus = currentlyActive
                ? "disabled"
                : "active";

            const confirmed = window.confirm(
                `Are you sure you want to ${
                    currentlyActive ? "disable" : "enable"
                } this link?`
            );

            if (!confirmed) {
                return;
            }

            statusButton.disabled = true;
            statusButton.textContent = "Updating...";

            try {
                const response = await fetch(
                    "api/update-link-status.php",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            id: link.id,
                            status: newStatus
                        })
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || "Unable to update link status."
                    );
                }

                dashboardMessage.textContent = data.message;

                await loadDashboard();

            } catch (error) {
                dashboardMessage.textContent =
                    error.message || "Something went wrong.";

                statusButton.disabled = false;
                statusButton.textContent = currentlyActive
                    ? "Disable"
                    : "Enable";
            }
        });

        actionsCell.append(
            copyButton,
            openButton,
            statusButton
        );

        row.append(
            shortCell,
            destinationCell,
            clicksCell,
            statusCell,
            createdCell,
            actionsCell
        );

        linksTableBody.appendChild(row);
    }
}

searchInput.addEventListener("input", () => {
    const query = searchInput.value.trim().toLowerCase();

    const filteredLinks = allLinks.filter(link =>
        link.short_code.toLowerCase().includes(query) ||
        link.original_url.toLowerCase().includes(query)
    );

    renderLinks(filteredLinks);
});

async function loadClickChart() {
    const response = await fetch("api/click-activity.php", {
        cache: "no-store"
    });

    const data = await response.json();

    if (!response.ok || !data.success) {
        throw new Error(data.message || "Could not load click activity.");
    }

    const canvas = document.getElementById("clickChart");

    if (clickChart) {
        clickChart.destroy();
    }

    clickChart = new Chart(canvas, {
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
                    labels: {
                        color: "#c0c8da"
                    }
                }
            },

            scales: {
                x: {
                    ticks: {
                        color: "#98a4bc"
                    },
                    grid: {
                        color: "rgba(152, 164, 188, 0.08)"
                    }
                },

                y: {
                    beginAtZero: true,

                    ticks: {
                        precision: 0,
                        color: "#98a4bc"
                    },

                    grid: {
                        color: "rgba(152, 164, 188, 0.08)"
                    }
                }
            }
        }
    });
}

loadDashboard();