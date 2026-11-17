# Metis Admin Dashboard

A 30-page Bootstrap 5 admin dashboard. **Plain HTML + plain CSS + ES modules.**
No SCSS, no Vite, no bundler, no `npm install` needed.

There is **no backend**. All data (users, orders, products, messages, charts…) is
sample data written inside the JS modules, so every page works on its own.

## Run it

ES modules cannot load from `file://`, so serve the folder over http. Pick one:

```bash
node server.js          # or: npm start     -> http://localhost:3000
python3 -m http.server 3000                 # alternative
```

(`PORT=8080 node server.js` to change the port. VS Code "Live Server" also works.)
Refresh the browser after editing — nothing needs rebuilding.

## Folder layout

```
index.html, users.html, orders.html, …   the 30 pages (edit directly)
server.js                                 tiny static server, zero dependencies
manifest.json                             PWA manifest
css/
  fonts.css            Inter font (self-hosted)
  bootstrap-icons.css  all Bootstrap Icons (any `bi-*` class works)
  bootstrap.css        Bootstrap 5.3 + the Metis colour/size overrides (vendor — avoid editing)
  style.css            ALL Metis custom styles — edit this one. Sections are labelled
                       (layout/…, components/…, pages/…, themes/dark, themes/light)
  prism-tomorrow.css   code-highlight theme (elements pages only)
fonts/                 font files used by the CSS above
assets/                images, logo, favicon, app icons
js/
  main.js              entry for every admin page (picks the module by <body data-page="…">)
  auth.js              entry for login / register / error pages
  components/          one module per page: dashboard, users, orders, products, reports,
                       analytics, messages, calendar, files, settings, security, help,
                       forms, elements, auth
  utils/               theme (dark mode), notifications, search, icons, chart colours…
  vendor/              Bootstrap JS, Alpine.js, ApexCharts, SweetAlert2, Prism (pre-bundled)
```

## How a page is wired

Every page links the four CSS files and loads one script:

```html
<body data-page="users"> … </body>
<script type="module" src="./js/main.js"></script>
```

`main.js` reads `data-page` and loads `js/components/<page>.js`, which registers an
Alpine.js component (`x-data="userTable"` in the HTML, `Alpine.data('userTable', …)` in the
JS) holding that page's data and actions. (The dashboard is the exception: it uses a
`DashboardManager` class.) To change what a page does, edit its file in `js/components/`.

## Adding a new page

1. Copy an existing page, e.g. `users.html` → `reports-2.html`, and change `data-page`.
2. Create `js/components/<your-page>.js`, then in `js/main.js` add a `case` in
   `initPageComponents()` plus an `init…Page()` method that `import()`s it
   (copy `initUsersPage()` as a template).
3. Add a link to it in the sidebar of the pages that should show it.

## Connecting a real backend later

Each component creates its sample data in a method called from `init()`
(e.g. `loadSampleData()` in `js/components/users.js`). Replace that method's body with a
`fetch('/api/…')` call that fills the same arrays.

## License

MIT — see `LICENSE.md`. Based on the open-source Metis template.
