# 💻 Front-End Development – Fortune Home

This document outlines the **Front-End development work** completed by **Harsh Adhiyol** for the [Fortune Home Project](https://github.com/rahucqu/fortune-homes).  
The frontend is built using **Inertia.js**, **React**, **TailwindCSS**, and **shadcn/ui**, integrated into the **Laravel 12** backend.

---

## 📂 Repo Structure (Frontend)

```bash
fortune-homes/
├── resources/
│   ├── js/
│   │   ├── Pages/        # Inertia React pages (Homepage, Search, Property Details, Admin)
│   │   ├── Components/   # Shared React components (Navbar, Footer, Filters, PropertyCard)
│   │   ├── Layouts/      # Layout wrappers for consistent UI
│   │   └── app.jsx       # React entry point
│   │
│   ├── css/
│   │   └── app.css       # TailwindCSS styles
│   │
│   └── views/            # Blade templates (minimal, used as Inertia entry points)
│
├── routes/
│   └── web.php           # Laravel routes powering Inertia React pages
│
├── tailwind.config.js    # TailwindCSS configuration
├── postcss.config.js     # PostCSS config
└── package.json          # Frontend dependencies
```

---

## 🛠 Tech Stack (Frontend)

- **Inertia.js** – connects Laravel routes to React pages.  
- **React 18+** – component-based UI.  
- **TailwindCSS** – utility-first responsive styling.  
- **shadcn/ui** – pre-styled UI components (buttons, forms, modals).  
- **Google Maps API** – embedded maps for property listings.  

---

## 🚀 Development Setup

### Install Dependencies
```bash
npm install
```

### Run Development Server
```bash
# Compile assets with Vite
npm run dev
```

### Build for Production
```bash
npm run build
```

---

## 📋 Front-End Tasks Completed

### React Pages
- `resources/js/Pages/Home.jsx` – homepage with featured listings.  
- `resources/js/Pages/Search.jsx` – property search & filtering.  
- `resources/js/Pages/PropertyDetails.jsx` – detailed property info with maps.  
- `resources/js/Pages/Admin/Dashboard.jsx` – admin panel for listings.  

### Shared Components
- `resources/js/Components/Navbar.jsx` – navigation bar.  
- `resources/js/Components/Footer.jsx` – footer section.  
- `resources/js/Components/PropertyCard.jsx` – reusable property card UI.  
- `resources/js/Components/Filters.jsx` – filtering sidebar and controls.  

### Other Completed Work
- Implemented **search & filtering** with React state + Inertia requests.  
- Integrated **Google Maps API** for property location display.  
- Ensured **mobile responsiveness** with Tailwind breakpoints.  
- Added **form validation UI** for inquiries and admin forms.  
- Optimized performance (lazy loading images, minified assets).  
- Conducted **cross-browser testing** (Chrome, Firefox, Safari, Edge).  

---

## 🔄 Collaboration Notes

- Worked closely with **Parthesh (UI/UX Designer)** to translate Figma mockups into code.  
- Integrated **Inertia.js routes** with backend APIs provided by **Rahul (Back-End Developer & Tester)**.  
- Code reviewed via GitHub Pull Requests on [fortune-homes](https://github.com/rahucqu/fortune-homes).  
- Coordinated with **Abhay (Project Manager)** for task tracking on ClickUp.  

---

## 📚 References

- [Laravel + Inertia Documentation](https://inertiajs.com/)  
- [TailwindCSS Docs](https://tailwindcss.com/docs)  
- [shadcn/ui Components](https://ui.shadcn.com/docs)  
- [Google Maps JavaScript API](https://developers.google.com/maps/documentation/javascript)  

---

## 👤 Developer

- **Name:** Harsh Adhiyol  
- **Role:** Front-End Developer  
- **Focus:** Building responsive React-based UI with Inertia and Tailwind.  

---
