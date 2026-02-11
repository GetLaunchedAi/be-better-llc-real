# Quick test checklist

## Cart
- Add to cart from:
  - Home
  - Any collection page (after the page loads, products are refreshed from `/products.json`)
  - Search
  - Product detail page (size/color variants update cart line item)
- Cart quantity +/- updates totals
- Remove item works

## Search
- Header search link focuses input on `/search/`
- Search results match `/products.json` (fetch uses `cache: "no-store"`)

## Collections
- Mobile: **Filters** button opens/closes drawer; tapping backdrop closes
- Clicking a filter chip updates the URL query (e.g. `?filter=sale`) and filters visible products
- **Clear** resets filters
- **Refresh products** refetches `/products.json` and re-renders cards

## PDP variants
- Size and color buttons are selectable
- Selected size/color is included on Add to cart (shown in cart)

## Admin
- Visit `/admin/`
- Login with `ADMIN_PASSWORD` (Cloudways → .htaccess SetEnv / Application settings)
- Logout works
- Add/edit/delete product and click **Save changes**
- Confirm `/products.json` updates immediately and changes appear on:
  - `/search/`
  - collection pages (via live refresh)
- Import/Export JSON works
- (Optional) Upload image saves to `/assets/uploads/` and returns a URL
