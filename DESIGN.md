# Design System: Tableau de Bord - Dark Minimalist
**Project ID:** 2806678971745658571

## 1. Visual Theme & Atmosphere
The dashboard employs a **Deep Dark Minimalist** aesthetic. It's incredibly airy while maintaining a high data density. The interface relies almost exclusively on flat, solid surfaces with barely-there borders and subtle colored illumination to guide the eye. It completely drops the heavy metallic or glassmorphism look in favor of an ultra-clean, utilitarian, yet highly elegant dark mode experience that feels like a professional engineering tool.

## 2. Color Palette & Roles
* **Pitch Canvas** (`#111111`): The base background color of the main application area. Absorbs light entirely.
* **Deep Panel Black** (`#0a0a0a`): Used for the sidebar to create physical separation without using vertical lines.
* **Elevated Charcoal** (`#1a1a1a`): Used for all metric cards and data containers to slightly lift them from the pitch canvas.
* **Action Azure** (`#2563eb`): Primary interactive color for the main call-to-action ("Nouveau produit").
* **Warning Tangerine** (`#f97316` / `orange-500`): Used for Pending statuses.
* **Success Emerald** (`#10b981` / `emerald-500`): Used for successful metrics (revenue growth, delivered statuses).
* **Transit Purple** (`#a855f7` / `purple-500`): Used specifically for in-transit/shipped states.
* **Subtle Frost** (`rgba(255,255,255,0.05)`): Used extensively for borders (`border-white/5`) and active states (`bg-white/5`), acting as a very subtle highlighter rather than a solid box.

## 3. Typography Rules
* **Family:** Primary use of the `Inter` font family.
* **Headers:** Strong, crisp, and utilizing `font-bold` to distinguish from data.
* **Data Values:** Numbers in cards use `text-2xl` or `text-3xl` with `font-bold` for rapid scanning.
* **Metadata/Labels:** Very small (`text-[10px]`), heavily tracked (`tracking-widest`), and fully capitalized (`uppercase`) to create a structured, tactical feel without stealing attention from the actual data.

## 4. Component Stylings
* **Buttons:** Gently rounded (`rounded-lg`), bold font, highly visible due to the stark background contrast. Often includes a very subtle colored shadow (`shadow-blue-900/20`).
* **Cards/Containers:** Moderate corner roundness (`rounded-2xl`), utilizing `border-white/5` for definition rather than shadows, ensuring a perfectly flat aesthetic.
* **Icons:** Grouped inside softly illuminated squares (`p-3 bg-color-500/10 rounded-xl`) allowing the vibrant accent color to tint the background subtly.
* **Lists:** Items separated by `border-b border-white/5`, no internal padding bounds, stretching edge-to-edge within their parent containers.

## 5. Layout Principles
* **Whitespace:** Generous external margins but tightly clustered related data elements.
* **Grid Alignment:** A strict 4-column metric grid that flows into a 2/3 (primary lists) and 1/3 (secondary alerts) proportional split for content.
