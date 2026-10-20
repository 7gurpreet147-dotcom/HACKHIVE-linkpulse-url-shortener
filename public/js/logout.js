
const logoutButton = document.getElementById("logoutButton");

logoutButton.addEventListener("click", async () => {
    logoutButton.disabled = true;
    logoutButton.textContent = "Logging out...";

    try {
        const response = await fetch("api/logout.php", {
            method: "POST",
            credentials: "same-origin"
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || "Unable to log out.");
        }

        window.location.replace("login.php");

    } catch (error) {
        alert(error.message || "Logout failed. Please try again.");

        logoutButton.disabled = false;
        logoutButton.textContent = "↪ Logout";
    }
});
