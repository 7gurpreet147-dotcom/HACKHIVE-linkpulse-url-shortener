
/* =========================================================
   LINKPULSE — INTRO LOADER
   Counts from 0% to 100%, then reveals the homepage.
   ========================================================= */

(function initIntroLoader() {
    const loader = document.getElementById("introLoader");

    if (!loader) {
        document.body.classList.add("intro-revealed");
        return;
    }

    const percentage = document.getElementById("introPercentage");
    const progress = document.getElementById("introProgress");

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

    const sessionKey = "linkpulseIntroShown";

    let alreadyShown = false;

    try {
        alreadyShown = sessionStorage.getItem(sessionKey) === "1";
    } catch (error) {
        // Continue without session storage if unavailable.
    }

    function finishIntro() {
        loader.classList.add("is-opening");

        document.body.classList.remove("intro-pending");
        document.body.classList.add("intro-revealed");

        window.setTimeout(() => {
            loader.classList.add("is-hidden");
            loader.hidden = true;
        }, reducedMotion ? 0 : 950);
    }

    if (alreadyShown || reducedMotion) {
        loader.classList.add("is-hidden");
        loader.hidden = true;
        document.body.classList.add("intro-revealed");
        return;
    }

    document.body.classList.add("intro-pending");

    const duration = 1550;
    const startTime = performance.now();

    function animateCounter(now) {
        const linearProgress = Math.min(
            1,
            (now - startTime) / duration
        );

        const easedProgress =
            1 - Math.pow(1 - linearProgress, 2.2);

        const current = Math.min(
            100,
            Math.round(easedProgress * 100)
        );

        if (percentage) {
            percentage.textContent = `${current}%`;
        }

        if (progress) {
            progress.style.width = `${current}%`;
        }

        if (linearProgress < 1) {
            requestAnimationFrame(animateCounter);
        } else {
            try {
                sessionStorage.setItem(sessionKey, "1");
            } catch (error) {
                // The animation can still finish normally.
            }

            window.setTimeout(finishIntro, 220);
        }
    }

    requestAnimationFrame(animateCounter);
})();


/* =========================================================
   LINKPULSE — INTERACTIVE PARTICLE BACKGROUND
   Responds to pointer movement without blocking page controls.
   ========================================================= */

(function initHeroBackground() {
    const hero = document.getElementById("homeHero");
    const canvas = document.getElementById("heroParticleCanvas");
    const glow = document.getElementById("heroCursorGlow");

    if (!hero || !canvas) {
        return;
    }

    const context = canvas.getContext("2d");

    if (!context) {
        return;
    }

    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)"
    ).matches;

    const isSmallScreen = window.matchMedia(
        "(max-width: 767px)"
    ).matches;

    const particleCount = isSmallScreen ? 30 : 58;
    const connectionDistance = isSmallScreen ? 95 : 125;

    const pointer = {
        x: -1000,
        y: -1000,
        active: false
    };

    let particles = [];
    let width = 0;
    let height = 0;
    let animationFrame = null;

    function resizeCanvas() {
        const rect = hero.getBoundingClientRect();
        const ratio = Math.min(window.devicePixelRatio || 1, 2);

        width = rect.width;
        height = rect.height;

        canvas.width = Math.floor(width * ratio);
        canvas.height = Math.floor(height * ratio);

        canvas.style.width = `${width}px`;
        canvas.style.height = `${height}px`;

        context.setTransform(ratio, 0, 0, ratio, 0, 0);

        particles = Array.from(
            { length: particleCount },
            () => ({
                x: Math.random() * width,
                y: Math.random() * height,
                vx: (Math.random() - 0.5) * 0.24,
                vy: (Math.random() - 0.5) * 0.24,
                radius: Math.random() * 1.7 + 0.6
            })
        );

        drawFrame();
    }

    function drawFrame() {
        context.clearRect(0, 0, width, height);

        particles.forEach((particle) => {
            if (!reducedMotion) {
                particle.x += particle.vx;
                particle.y += particle.vy;

                if (particle.x < 0 || particle.x > width) {
                    particle.vx *= -1;
                }

                if (particle.y < 0 || particle.y > height) {
                    particle.vy *= -1;
                }
            }

            if (pointer.active && !reducedMotion) {
                const dx = particle.x - pointer.x;
                const dy = particle.y - pointer.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance > 0 && distance < 115) {
                    const force = (115 - distance) / 115;

                    particle.x +=
                        (dx / distance) * force * 1.15;

                    particle.y +=
                        (dy / distance) * force * 1.15;
                }
            }
        });

        // Draw connecting lines between nearby particles.
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const a = particles[i];
                const b = particles[j];

                const dx = a.x - b.x;
                const dy = a.y - b.y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < connectionDistance) {
                    const alpha =
                        (1 - distance / connectionDistance) * 0.19;

                    context.strokeStyle =
                        `rgba(124, 92, 255, ${alpha})`;

                    context.lineWidth = 0.7;
                    context.beginPath();
                    context.moveTo(a.x, a.y);
                    context.lineTo(b.x, b.y);
                    context.stroke();
                }
            }
        }

        // Draw the particles.
        particles.forEach((particle) => {
            context.beginPath();

            context.arc(
                particle.x,
                particle.y,
                particle.radius,
                0,
                Math.PI * 2
            );

            context.fillStyle = "rgba(91, 224, 211, 0.55)";
            context.fill();
        });

        if (!reducedMotion) {
            animationFrame = requestAnimationFrame(drawFrame);
        }
    }

    hero.addEventListener("pointermove", (event) => {
        const rect = hero.getBoundingClientRect();

        pointer.x = event.clientX - rect.left;
        pointer.y = event.clientY - rect.top;
        pointer.active = true;

        hero.classList.add("pointer-active");

        if (glow) {
            hero.style.setProperty(
                "--pointer-x",
                `${pointer.x}px`
            );

            hero.style.setProperty(
                "--pointer-y",
                `${pointer.y}px`
            );
        }

        if (reducedMotion) {
            drawFrame();
        }
    });

    hero.addEventListener("pointerleave", () => {
        pointer.active = false;
        hero.classList.remove("pointer-active");
    });

    window.addEventListener("resize", () => {
        if (animationFrame !== null) {
            cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }

        resizeCanvas();
    }, { passive: true });

    resizeCanvas();
})();


/* =========================================================
   LINKPULSE — EXISTING URL SHORTENER
   ========================================================= */

const form = document.getElementById("shortenerForm");
const urlInput = document.getElementById("originalUrl");
const shortenBtn = document.getElementById("shortenBtn");

const message = document.getElementById("message");
const resultContainer = document.getElementById("resultContainer");
const shortUrlInput = document.getElementById("shortUrl");

const copyBtn = document.getElementById("copyBtn");
const openShortUrl = document.getElementById("openShortUrl");

// Usage indicator elements.
const usagePanel = document.getElementById("usagePanel");
const dailyUsageText = document.getElementById("dailyUsageText");
const dailyUsageBar = document.getElementById("dailyUsageBar");

const ipUsageSection = document.getElementById("ipUsageSection");
const ipUsageText = document.getElementById("ipUsageText");
const ipUsageBar = document.getElementById("ipUsageBar");

const registrationOffer = document.getElementById("registrationOffer");


/* =========================================================
   USAGE INFORMATION
   ========================================================= */

async function refreshUsage() {
    if (!usagePanel) {
        return;
    }

    try {
        const response = await fetch("api/usage-status.php", {
            method: "GET",
            cache: "no-store",
            credentials: "same-origin"
        });

        if (!response.ok) {
            throw new Error("Unable to load usage information.");
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(
                data.message || "Usage information unavailable."
            );
        }

        // Daily quota for both anonymous and registered users.
        const daily = data.daily;

        if (daily && dailyUsageText) {
            dailyUsageText.textContent =
                `${daily.used} / ${daily.limit} used · ` +
                `${daily.remaining} remaining`;
        }

        if (daily) {
            updateProgressBar(
                dailyUsageBar,
                daily.used,
                daily.limit
            );
        }

        // IP allowance applies only to anonymous visitors.
        if (data.user_type === "anonymous" && data.ip) {
            if (ipUsageSection) {
                ipUsageSection.hidden = false;
            }

            if (ipUsageText) {
                ipUsageText.textContent =
                    `${data.ip.used} / ${data.ip.limit} used · ` +
                    `${data.ip.remaining} remaining`;
            }

            updateProgressBar(
                ipUsageBar,
                data.ip.used,
                data.ip.limit
            );
        } else if (ipUsageSection) {
            ipUsageSection.hidden = true;
        }

        // Registration offer is only for anonymous visitors.
        if (registrationOffer) {
            registrationOffer.hidden =
                data.registration_offer !== true;
        }

        usagePanel.hidden = false;

    } catch (error) {
        console.error("Usage status error:", error);
        usagePanel.hidden = true;
    }
}


function updateProgressBar(bar, used, limit) {
    if (!bar) {
        return;
    }

    const percentage = limit > 0
        ? Math.min(100, (used / limit) * 100)
        : 0;

    bar.style.width = `${percentage}%`;

    bar.setAttribute("aria-valuenow", String(used));
    bar.setAttribute("aria-valuemin", "0");
    bar.setAttribute("aria-valuemax", String(limit));

    bar.classList.toggle("bg-danger", used >= limit);
    bar.classList.toggle("bg-success", used < limit);
}


// Fetch usage information on page load.
refreshUsage();


/* =========================================================
   CREATE A SHORT URL
   ========================================================= */

if (form && urlInput && shortenBtn) {
    form.addEventListener("submit", async (event) => {
        event.preventDefault();

        const url = urlInput.value.trim();

        if (message) {
            message.textContent = "";
            message.className = "mt-3";
        }

        if (resultContainer) {
            resultContainer.hidden = true;
        }

        let parsedUrl;

        try {
            parsedUrl = new URL(url);
        } catch {
            showMessage("Please enter a valid URL.", "danger");
            return;
        }

        if (!["http:", "https:"].includes(parsedUrl.protocol)) {
            showMessage(
                "Only HTTP and HTTPS URLs are allowed.",
                "danger"
            );
            return;
        }

        shortenBtn.disabled = true;
        shortenBtn.textContent = "Creating...";

        try {
            const response = await fetch("api/create-link.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                credentials: "same-origin",
                body: JSON.stringify({ url })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(
                    data.message ||
                    "Unable to create the short URL."
                );
            }

            if (shortUrlInput) {
                shortUrlInput.value = data.short_url;
            }

            if (openShortUrl) {
                openShortUrl.href = data.short_url;
            }

            if (resultContainer) {
                resultContainer.hidden = false;
            }

            showMessage("Your short URL is ready!", "success");

        } catch (error) {
            showMessage(
                error.message ||
                "Something went wrong. Please try again.",
                "danger"
            );
        } finally {
            shortenBtn.disabled = false;
            shortenBtn.textContent = "Shorten URL";

            // Refresh usage after successes and failures.
            await refreshUsage();
        }
    });
}


/* =========================================================
   COPY SHORT URL
   ========================================================= */

if (copyBtn && shortUrlInput) {
    copyBtn.addEventListener("click", async () => {
        if (!shortUrlInput.value) {
            showMessage("Create a short URL first.", "warning");
            return;
        }

        try {
            await navigator.clipboard.writeText(
                shortUrlInput.value
            );

            copyBtn.textContent = "Copied!";

            window.setTimeout(() => {
                copyBtn.textContent = "Copy";
            }, 1500);

        } catch (error) {
            shortUrlInput.select();

            const copied = document.execCommand("copy");

            showMessage(
                copied
                    ? "Short URL copied."
                    : "Please select and copy the URL manually.",
                copied ? "success" : "warning"
            );
        }
    });
}


/* =========================================================
   DISPLAY API AND VALIDATION MESSAGES
   ========================================================= */

function showMessage(text, type) {
    if (!message) {
        return;
    }

    message.textContent = text;
    message.className = `mt-3 alert alert-${type}`;
}
