# Phase 1 — Eleventy + Nunjucks Scaffold

This project is a minimal Eleventy (11ty) starter using **Nunjucks** templates, a shared base layout, and a mobile-first CSS token foundation.

## Requirements
- Node.js **18+** (Eleventy v3 requirement)

## Structure

```
src/
  _includes/
    layouts/base.njk
    partials/header.njk
    partials/footer.njk
  assets/
    css/main.css
    js/main.js
  index.njk
.eleventy.js
package.json
README.md
```

## Commands

### Install
```bash
npm install
```

### Dev server
```bash
npm run dev
```

### Production build
```bash
npm run build
```

## Notes
- Output is generated to `/_site`.
- `src/assets` is copied through to `_site/assets`.
- No CSS/JS frameworks; just modern CSS (grid/flex, `clamp()`, CSS variables).
