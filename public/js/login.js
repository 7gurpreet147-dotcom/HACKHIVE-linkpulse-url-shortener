const loginForm = document.getElementById("loginForm");
const loginButton = document.getElementById("loginButton");
const formMessage = document.getElementById("formMessage");

loginForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    formMessage.textContent = "";
    formMessage.className = "form-message";

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    loginButton.disabled = true;
    loginButton.textContent = "Logging in...";

    try {
        const response = await fetch("api/login.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            credentials: "same-origin",
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.message || "Unable to log in."
            );
        }

        formMessage.textContent = "Login successful! Redirecting...";
        formMessage.className = "form-message success";

        loginForm.reset();

        // Temporary destination until we protect the dashboard.
        setTimeout(() => {
            window.location.href = "dashboard.php";
        }, 800);

    } catch (error) {
        formMessage.textContent =
            error.message || "Something went wrong. Please try again.";

        formMessage.className = "form-message error";

    } finally {
        loginButton.disabled = false;
        loginButton.textContent = "Log In";
    }
});