const registerForm = document.getElementById("registerForm");
const registerButton = document.getElementById("registerButton");
const formMessage = document.getElementById("formMessage");

registerForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    formMessage.textContent = "";
    formMessage.className = "form-message";

    const name = document.getElementById("name").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword =
        document.getElementById("confirmPassword").value;

    // Check that both passwords match.
    if (password !== confirmPassword) {
        showMessage("Passwords do not match.", "error");
        return;
    }

    // Prevent repeated submissions.
    registerButton.disabled = true;
    registerButton.textContent = "Creating account...";

    try {
        const response = await fetch("api/register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                name,
                email,
                password,
                confirmPassword
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(
                data.message || "Unable to create your account."
            );
        }

        showMessage(
            "Account created successfully! Redirecting to login...",
            "success"
        );

        registerForm.reset();

        // Login will be implemented in the next stage.
        setTimeout(() => {
            window.location.href = "login.php";
        }, 1800);

    } catch (error) {
        showMessage(
            error.message || "Something went wrong. Please try again.",
            "error"
        );

    } finally {
        registerButton.disabled = false;
        registerButton.textContent = "Create Account";
    }
});

function showMessage(message, type) {
    formMessage.textContent = message;
    formMessage.className = `form-message ${type}`;
}