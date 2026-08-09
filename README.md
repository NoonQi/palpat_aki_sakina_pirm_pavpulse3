# PIRM — Pothole & Infrastructure Reporting Map

A crowdsourced platform for reporting road damage, tracking fixes, and visualizing hotspots across the city. Citizens report issues with a photo and location; reports move through a public status pipeline (**Reported → Acknowledged → Fixed**) and can be upvoted to signal priority.

This repo currently contains a fully working **frontend prototype** with mock data. The backend/API is being built separately by a teammate — see [Backend Integration](#backend-integration-todo) below for exactly what changes once it's ready.

---

## Live Demo

Once deployed via GitHub Pages, the site is available at:
```
https://pallu04patil-blip.github.io/palpat_aki_sakina_pirm_pavpulse3/
```
`index.html` loads automatically at that root URL.

---

## File Structure

```
pirm-project/
├── index.html                     Homepage — pipeline overview, entry points, public report browser
├── userlogin.html                Citizen sign in
├── userregister.html             Citizen account creation
├── userdashboard.html  Citizen Portal — submit reports, browse, upvote
├── admindashboard.html  Admin Console — review, update status, analytics
├── pothole2.html                  Hotspot Map with clustering (teammate's module)
└─backend data                        Shared data — (local storage througb MySQL)
```

## Modules

### 1. Homepage (`index.html`)
The entry point and navigation hub. Shows the Reported → Acknowledged → Fixed pipeline visually, gives citizens two buttons (**Sign In** / **Register**), gives admins a separate **Admin Login** button, as well as shows Map  Clustering module. Also includes a **public "Browse Recent Reports" section** with search, status filter, severity filter, and sort — visible without logging in. Upvoting from this view prompts sign-in, since votes are tied to an account.

*Jira: part of SCRUM-32 scope*

### 2. Citizen Auth (`user-login.html`, `user-register.html`)
Mock authentication pages. Registration collects name/email/password and redirects to login with a success message. Login accepts any well-formed email + password (no real verification exists yet — see Backend Integration) and redirects into the dashboard with the entered name passed along, so the dashboard can display a personalized greeting.

*Jira: SCRUM-32*

### 3. Citizen Portal (`user-dashboard-bootstrap.html`)
Built with Bootstrap 5. Lets citizens:
- Submit a new report (title, description, severity, road type, photo upload with preview, "Use My Location" geotagging)
- Browse all reports with search, status filter, severity filter, and sort (Newest / Most Upvoted / Severity)
- Upvote reports (toggleable, one vote per report per session)
- See a personalized greeting and a live "Reports you've submitted this session" counter

*Jira: SCRUM-32*

### 4. Admin Console (`admindashboard.html`)
Lets admins:
- View/edit full report details (title, description, severity, road type) in a modal
- Update status, add internal admin notes, reject or delete a report
- Search, filter (status/severity), and sort (Newest / Most Upvoted / Severity)
- View analytics: reports by severity (bar), by status (doughnut), and a 7-day trend (line) — built with Chart.js

*Jira: SCRUM-27, subtasks SCRUM-29 (report list UI), SCRUM-30 (status controls), SCRUM-31 (search/filter)*

### 5. Hotspot Map (`pothole2.html`)
Geo-clustered map view showing where reports concentrate where users can see reported incidents and view details of the incident as well as upvote then and there in the module itself..

*Jira: SCRUM-16*

---

## Design System

All modules share a consistent visual language and CSS class prefix (`pirm-`) to avoid collisions if pages are ever combined:
- **Palette:** asphalt dark gray, safety-cone orange, route blue, fixed green, caution yellow — a civic/road-signage theme
- **Typography:** Oswald (display/headers), Inter (body)
- **Status colors:** Reported = yellow, Acknowledged = blue, Fixed = green, Rejected = red
- **Severity colors:** Low = gray, Medium = yellow, High = orange, Critical = red

---

## Running Locally

1. Clone or download the repo so all files sit in the same folder.
2. Open `index.html` directly in a browser, **or** for full functionality (especially geolocation, which most browsers block on `file://` URLs), serve it locally:
   - VS Code: install the **Live Server** extension, right-click `index.html` → "Open with Live Server"
   - Or run `python -m http.server` in the folder and visit `http://localhost:8000`

---

## Deploying (GitHub Pages)

1. Push all files to a GitHub repo.
2. Go to **Settings → Pages** → under **Source**, select **Deploy from a branch** → `main` branch, `/ (root)` folder → **Save**.
3. GitHub provides a live URL within a few minutes.

---

## Linking Commits to Jira

Every commit message should include the relevant issue key so it shows up in that issue's Development panel in Jira (requires the **GitHub for Jira** app connected to this repo):
```
SCRUM-29 build report list UI with status and priority tags
SCRUM-16 add map clustering module
```

---

## Backend Integration using MySQL

### 3. Make actions persist
Currently these all only update the in-memory array and are lost on refresh. Each needs a matching API call:
| Action | Where | Needs to call |
|---|---|---|
| Submit new report | Citizen Portal, Admin Console | `POST /api/reports` |
| Upvote / un-upvote | Homepage, Citizen Portal | `POST /api/reports/:id/upvote` |
| Update status | Admin Console | `PATCH /api/reports/:id` |
| Edit details / notes | Admin Console | `PATCH /api/reports/:id` |
| Reject / un-reject | Admin Console | `PATCH /api/reports/:id` |
| Delete | Admin Console | `DELETE /api/reports/:id` |

### 4. Real authentication
`user-login.html`, `user-register.html`, and the admin login currently accept any input (or a hardcoded demo password). These need to be pointed at real auth endpoints (e.g. `POST /api/auth/login`, `POST /api/auth/register`), and the app will need a way to persist the logged-in session (e.g. a token) across page loads and navigation — which the current no-storage prototype deliberately doesn't do.

### 5. Photo storage
Photos are currently stored as base64 data URLs in memory. A real backend will likely want file uploads sent to cloud storage (e.g. S3, Cloudinary) with the `photo` field becoming a URL instead of a data URL.

### 6. Live cross-page sync
Once reports live on a server instead of in each page's own memory, an upvote in the Citizen Portal will finally show up in the Admin Console and vice versa — solving the current limitation where each page only sees its own local copy.

None of these changes require restructuring the UI — they're all localized to the handful of places each page currently reads or writes the `reports` array.

---

## Known Limitations (Current Prototype)

- No data persists across page refreshes or between users/tabs
- Login accepts any credentials — not real authentication
- Photos are stored as in-browser data URLs, not uploaded anywhere
- Each page has its own copy of report data — changes don't sync across pages
- The Admin Console's 7-day trend chart uses static mock numbers, not real historical data
