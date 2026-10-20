# LinkPulse — URL Shortener & Analytics Platform

> **Hackathon submission:** Round 2  
> **Live demo:** http://linkpulse.gt.tc *(update after deployment and verify that it opens)*  
> **Repository name:** `teamname-linkpulse` *(replace `teamname` with your actual team name)*

## Problem Statement

Long URLs are difficult to share, manage, and track. LinkPulse provides a central place to create shorter links and review link activity.

## Solution

LinkPulse is a PHP and MySQL web application for creating short URLs, redirecting visitors, tracking clicks, and managing links through a web interface.

## Features

Verify each feature against the final deployed build before submission:

- Create short links from valid HTTP/HTTPS URLs.
- Redirect visitors from a short URL to its destination.
- Track clicks and show link activity in the dashboard.
- Register, log in, and log out.
- Enable or disable links.
- Separate anonymous and registered-user link views.
- Enforce daily link-creation limits and anonymous IP-based request limits.
- Display usage information in the homepage interface.

### Current limitations / future improvements

Only list these as future work if they are not implemented in the final build:

- QR-code generation and downloads.
- Password-protected short links.
- Campaign/folder organization and advanced analytics.
- Additional bot filtering and administrative tools.

## Tech Stack

- **Frontend:** HTML, CSS, JavaScript, Bootstrap 5
- **Backend:** PHP
- **Database:** MySQL
- **Local development:** XAMPP (Apache, PHP, MySQL/MariaDB)
- **Hosting:** InfinityFree *(if the final deployment remains on this host)*

## Team

Update names and roles if they change before submission.

| Team member | Role | Programme |
|---|---|---|
| Gurpreet Singh | Project Lead & System Architect | BCA — AI & Data Science |
| Gaurav Lohan | Frontend Developer | BCA — AI & Data Science |
| Bhumi Maurya | Backend Developer | BTech — AI & Machine Learning |
| Nandni | Database & Analytics Engineer | BTech — AI & Data Science |
| Soniya | Security & Deployment | BTech — Computer Science & Engineering |

## Local Setup Instructions

1. Install [XAMPP](https://www.apachefriends.org/).
2. Clone or download this repository into XAMPP's `htdocs` directory, for example `C:\xampp\htdocs\LinkPulse`.
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.
4. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
5. Create a database named `linkpulse`.
6. Import the project's database schema/export. **Do not commit a dump containing real users, passwords, or private data.** If no schema/export is included in the repository, obtain the schema from the project owner before proceeding.
7. Copy `config/db.example.php` to `config/db.php`.
8. Update `config/db.php` with your local database credentials. For a default XAMPP setup, the host is usually `localhost`, the username is usually `root`, and the password is often blank unless you configured one.
9. Open `http://localhost/LinkPulse/` in your browser.

## Deployment Notes

1. Create a MySQL database in your hosting control panel.
2. Import the project's database schema/data into the hosted database.
3. Configure `config/db.php` with the host-provided database hostname, database name, username, password, and port.
4. Upload the PHP files and folders to the domain's web root (`htdocs` on the current InfinityFree setup).
5. Verify that the homepage, registration, login, link creation, redirect, click tracking, enable/disable controls, and usage quotas work on the live domain.
6. Enable HTTPS if the host supports it and update the Live Demo URL above to the verified HTTPS address.

**Never publish database passwords, session secrets, production credentials, or real user data.**

## Screenshots

Add real screenshots of the final working application to `screenshots/`, then uncomment or update these examples:

<!--
### Homepage
![LinkPulse homepage](screenshots/homepage.png)

### Dashboard and analytics
![LinkPulse dashboard](screenshots/dashboard.png)
-->

## GitHub Submission Checklist

- [ ] Repository is public.
- [ ] Repository name follows `teamname-projectname`.
- [ ] `README.md` includes the problem, solution, features, tech stack, setup instructions, live link, and screenshots.
- [ ] `.gitignore` is committed.
- [ ] No credentials or private user data are committed.
- [ ] Every team member makes commits from their own GitHub account.
- [ ] Commit messages describe the change (for example, `feat: add click tracking`).
- [ ] Live link opens from a device other than the developer's local machine.
- [ ] Final submission is sent through the organisers' Google Form before the deadline.

## License

Add a license only if your team has chosen one and has permission to publish the code under it.
