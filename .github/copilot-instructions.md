# BAR & Associates - Hosting & VPS Platform

## Project Overview
A multi-page hosting services website displaying pricing for Shared Hosting, VPS, Cloud Storage, Corporate Mail, Domains/SSL, and Datacenter facilities. Built with vanilla HTML/CSS/JavaScript using Tailwind CSS and a component-based architecture with partial loading.

## Architecture

### Key Components

**Frontend Structure:**
- **Index & Service Pages** (`index.html`, `shared-host.html`, `vps.html`, etc.): Static HTML with Tailwind styling
- **Partials** (`partials/nav.html`, `partials/footer.html`): Reusable fragments loaded dynamically into `<div id="site-nav">` and `<div id="site-footer">`
- **Core Scripts**: 
  - `js/components.js`: Fragment loading, navigation highlighting, mobile menu toggling
  - `js/basket.js`: Shopping cart system with localStorage persistence

### Data Flow Pattern
1. **Page Load** → `document.addEventListener('DOMContentLoaded')` triggers fragment loading
2. **Fragment Loading** → `loadFragment(targetId, url, callback)` fetches HTML partials and injects into DOM
3. **Navigation Setup** → `initNavigation()` runs post-load to set active states and mobile menu handlers
4. **Basket Integration** → `UniversalBasket` class auto-initializes, watches for clickable pricing cards

### Regional Pricing System
- Pages use **region buttons** (`showRegion('bd'|'uae'|'eu'|'us'|'asia'|'africa')`) to toggle visibility of pricing sections
- Each region has currency formatting (৳, AED, €, $)
- Region state persists visually but pricing content is pre-rendered in HTML (not fetched)
- Example: `shared-host.html` lines 40-50 shows region button implementation

## Development Patterns

### Navigation Highlighting
Used in `components.js` via data attributes:
- **Desktop**: `[data-nav-link]`, `[data-service-link]` with active state: `text-blue-600 font-semibold border-b-2 border-blue-600`
- **Mobile**: `[data-mobile-nav-link]`, `[data-mobile-service-link]` with active state: `bg-blue-50 text-blue-700`
- Page context set via `body` attributes: `data-page="services"`, `data-service="shared-host"`
- Always run `highlightNavigation()` after nav loads to activate correct links

### Basket/Shopping Cart
- **Initialization**: Automatically creates `<button id="basket-btn">` in nav DOM on first load
- **Item Format**: `{ id, name, type, region, price }` (stored in `localStorage.hostingBasket`)
- **Clickable Cards**: Pages must call `basket.makeClickable(element, itemData)` to enable add/remove
- **Visual Feedback**: In-basket items show green highlight (`box-shadow: 0 0 15px rgba(34, 197, 94, 0.5)`, `border-color: #22c55e`)
- **Basket UI**: Modal overlay with remove buttons and "Proceed to Payment" button that alerts item summary

### Styling & Dependencies
- **Tailwind CSS**: CDN-based (`https://cdn.tailwindcss.com`)
- **Fonts**: Inter via Google Fonts (weights: 400, 500, 600, 700)
- **Hero Buttons**: Custom `.hero-service` classes with gradient backgrounds (`#2563EB` to `#1D4ED8`) and ripple animation
- **Color Scheme**: 
  - Primary blue: `#2563EB` (hover: `#1D4ED8`)
  - Accent green: `#22c55e`
  - Text: `#1F2937` (gray-900)

## Essential Files Reference

| File | Purpose |
|------|---------|
| `index.html` | Homepage with hero section and service overview |
| `shared-host.html` | Shared hosting pricing with region switcher |
| `vps.html` | VPS hosting pricing (KVM, NVMe, DDoS protection messaging) |
| `js/components.js` | Fragment loading + navigation setup (60 LOC) |
| `js/basket.js` | Shopping cart system with localStorage (153 LOC) |
| `partials/nav.html` | Sticky navigation with services dropdown and mobile menu |
| `partials/footer.html` | Simple copyright footer |
| `comprehensive-pricing-features.md` | Full pricing reference documentation |

## Common Tasks

**Add a New Service Page:**
1. Create `service-name.html` with `<div id="site-nav">` and `<div id="site-footer">`
2. Add script: `<script src="js/components.js"></script>` and `<script src="js/basket.js"></script>`
3. Set `body` attributes: `data-page="services"` and `data-service="service-name"`
4. Add navigation link in `partials/nav.html` with `data-service-link="service-name"`
5. Include pricing cards with `data-item-id` for basket interactivity

**Update Navigation:**
- Edit `partials/nav.html` and add matching `[data-nav-link]` / `[data-service-link]` attributes on page bodies
- Mobile menu structure mirrors desktop structure (class names prefixed with mobile equivalents)

**Modify Pricing Display:**
- Region buttons use `onclick="showRegion('regionCode')"` to toggle section visibility
- Define CSS class `.region-bd`, `.region-uae` etc. for show/hide logic (typically implemented inline per page)

## Important Gotchas

- **Fragment loading requires proper IDs**: `<div id="site-nav">` must exist for `loadFragment()` to work
- **Callback execution order**: `initNavigation()` must run AFTER nav fragment loads; check `loadFragment()` callback flow
- **Basket persistence**: Always access via `basket.items` or methods; localStorage key is hardcoded as `'hostingBasket'`
- **Mobile-first regions**: Region buttons and content sections must have consistent class naming (`region-btn`, region state classes)
- **Tailwind CDN-only**: No build process; changes to CSS require either inline `<style>` blocks or existing Tailwind classes
