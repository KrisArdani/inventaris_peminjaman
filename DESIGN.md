---
name: Sistem Inventaris dan Peminjaman Barang
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#3c4a42'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#6c7a71'
  outline-variant: '#bbcabf'
  surface-tint: '#006c49'
  primary: '#006c49'
  on-primary: '#ffffff'
  primary-container: '#10b981'
  on-primary-container: '#00422b'
  inverse-primary: '#4edea3'
  secondary: '#2b6954'
  on-secondary: '#ffffff'
  secondary-container: '#adedd3'
  on-secondary-container: '#306d58'
  tertiary: '#a43a3a'
  on-tertiary: '#ffffff'
  tertiary-container: '#fc7c78'
  on-tertiary-container: '#711419'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#6ffbbe'
  primary-fixed-dim: '#4edea3'
  on-primary-fixed: '#002113'
  on-primary-fixed-variant: '#005236'
  secondary-fixed: '#b0f0d6'
  secondary-fixed-dim: '#95d3ba'
  on-secondary-fixed: '#002117'
  on-secondary-fixed-variant: '#0b513d'
  tertiary-fixed: '#ffdad7'
  tertiary-fixed-dim: '#ffb3af'
  on-tertiary-fixed: '#410005'
  on-tertiary-fixed-variant: '#842225'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  h1:
    fontFamily: Manrope
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
    letterSpacing: -0.02em
  h2:
    fontFamily: Manrope
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.01em
  h3:
    fontFamily: Manrope
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-base:
    fontFamily: Manrope
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Manrope
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-caps:
    fontFamily: Manrope
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  table-header:
    fontFamily: Manrope
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max: 1280px
  sidebar-width: 260px
  gutter: 1.5rem
  margin-page: 2rem
  stack-sm: 0.5rem
  stack-md: 1rem
  stack-lg: 2rem
---

## Brand & Style
The brand personality focuses on **transparency, efficiency, and reliability**. This design system is built for a high-utility environment where users need to manage physical assets and track movement with zero friction. 

The visual style is **Modern Minimalist**. It leverages the clean, utility-first aesthetics often found in Tailwind CSS frameworks. By prioritizing heavy whitespace and high-contrast typography over decorative elements, the system ensures that complex data tables and inventory lists remain legible. The emotional response should be one of "controlled organization"—a digital reflection of a well-ordered warehouse or library.

## Colors
The color palette is anchored by **Emerald Green**, symbolizing growth, health, and a "go" status—ideal for inventory systems where items are moving or available. 

- **Primary:** Used for the main brand touchpoints, primary actions, and active navigation states.
- **Secondary:** A deep forest green reserved for heavy text headings or subtle background accents in dark mode elements.
- **Neutrals:** A range of Cool Grays (Slate) provides the structural framework, keeping the interface feeling lightweight and airy.
- **Backgrounds:** The primary background uses a very light gray (`#f8fafc`) to allow white cards to "pop" via elevation.

## Typography
This design system utilizes **Manrope** for its balanced, modern, and highly legible characteristics. It performs exceptionally well in data-heavy environments.

Headlines use a tighter letter-spacing and heavier weights to create a strong hierarchy. Body text is optimized for readability with a generous line height. For the different roles (Anggota, Admin, Kepala), consistent typography ensures that regardless of permission level, the information density remains comfortable and accessible.

## Layout & Spacing
The layout follows a **Fluid Grid** model with a persistent left-hand sidebar for navigation. 

- **Sidebar:** Fixed width on desktop, collapsing to icons or a hamburger menu on mobile.
- **Content Area:** Uses a max-width container to prevent line lengths from becoming too long on ultra-wide monitors.
- **Grid:** A standard 12-column system is used for dashboard layouts. Stats cards typically span 3 columns (4 per row), while data tables occupy the full 12 columns.
- **Rhythm:** Spacing follows a strict 4px/8px scale to maintain visual harmony and "Tailwind-like" proportions.

## Elevation & Depth
Depth is communicated through **Tonal Layers** and **Soft Ambient Shadows**. 

1.  **Level 0 (Background):** The base layer is `#f8fafc`. 
2.  **Level 1 (Cards/Surfaces):** Pure white `#ffffff` cards sit on the background with a very soft, diffused shadow (`box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1)`).
3.  **Level 2 (Modals/Dropdowns):** Higher elevation with a larger shadow spread to indicate temporary focus and interaction.

Borders are used sparingly—primarily a subtle 1px border (`#e2e8f0`) to define table rows and input fields, rather than heavy outlines.

## Shapes
The shape language is **friendly and modern**. All interactive elements and containers utilize a generous corner radius to soften the technical nature of an inventory system.

- **Standard Elements:** Buttons, inputs, and small cards use `0.5rem` (8px).
- **Large Containers:** Main content cards and modals use `1rem` (16px) for a more distinct, "app-like" feel.
- **Status Pills:** Badges for "Available" or "Borrowed" status should use full `rounded-full` (pill) shapes to distinguish them from actionable buttons.

## Components

### Sidebar Navigation
The sidebar uses a dark or very light high-contrast treatment. Active links are highlighted with an Emerald Green left-border accent and a subtle background tint (`#10b981` at 10% opacity). Icons are essential for quick scanning by Admin and Kepala roles.

### Stats Cards
Used for high-level summaries (e.g., "Total Items," "Pending Loans"). These feature a large numerical value, a descriptive label, and an icon with a tinted background matching the status color.

### Data Tables
Tables are the heart of the system.
- **Header:** Light gray background with uppercase, bold labels.
- **Rows:** Subtle hover state change (`#f1f5f9`).
- **Actions:** Grouped at the end of the row using ghost buttons or simple icon triggers.

### Forms & Inputs
Inputs should have a clear focus state using an Emerald Green ring. Labels are placed above the field in `body-sm` weight. Validation errors must be clearly marked in `status.danger` red.

### Chips & Badges
Small status indicators for lending states. 
- *Peminjaman Aktif:* Emerald background with white text.
- *Terlambat:* Danger red background.
- *Menunggu Persetujuan:* Warning amber background.