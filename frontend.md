# 💻 Front-End Development – Fortune Home

This document outlines the **Front-End development work** completed by **Harsh Adhiyol** for the [Fortune Home Project](https://github.com/rahucqu/fortune-homes).  
The frontend is built using **Inertia.js**, **React**, **TailwindCSS**, and **shadcn/ui**, integrated into the **Laravel 12** backend.

---

## 📂 Repo Structure (Frontend)

```bash
fortune-homes/
├── resources/
│ ├── js/
│ │ ├── Pages/ # Inertia React pages (Homepage, Search, Property Details, Admin)
│ │ ├── Components/ # Shared React components (Navbar, Footer, Filters, PropertyCard)
│ │ ├── Layouts/ # Layout wrappers for consistent UI
│ │ └── app.jsx # React entry point
│ │
│ ├── css/
│ │ └── app.css # TailwindCSS styles
│ │
│ └── views/ # Blade templates (minimal, used as Inertia entry points)
│
├── routes/
│ └── web.php # Laravel routes powering Inertia React pages
│
├── tailwind.config.js # TailwindCSS configuration
├── postcss.config.js # PostCSS config
└── package.json # Frontend dependencies

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
# Install frontend packages
npm install
Run Development Server
bash
Copy code
# Compile assets with Vite
npm run dev
Build for Production
bash
Copy code
npm run build

📋 Front-End Tasks Completed
Converted UI/UX mockups into responsive React pages:

resources/js/Pages/Home.jsx

resources/js/Pages/Search.jsx

resources/js/Pages/PropertyDetails.jsx

resources/js/Pages/Admin/Dashboard.jsx

Built shared components:

resources/js/Components/Navbar.jsx

resources/js/Components/Footer.jsx

resources/js/Components/PropertyCard.jsx

resources/js/Components/Filters.jsx

Implemented search & filtering with React state + Inertia requests.

Integrated Google Maps API for property location display.

Ensured mobile responsiveness with Tailwind breakpoints.

Added form validation UI for inquiries and admin forms.

Optimized performance (lazy loading images, minified assets).

Conducted cross-browser testing (Chrome, Firefox, Safari, Edge).

🔄 Collaboration Notes
Worked closely with Parthesh (UI/UX) to translate Figma mockups into code.

Integrated Inertia.js routes with backend APIs provided by Rahul.

Code reviewed via GitHub Pull Requests on fortune-homes.

Coordinated with Abhay (Project Manager) for task tracking on ClickUp.

📚 References
Laravel + Inertia Documentation

TailwindCSS Docs

shadcn/ui Components

Google Maps JavaScript API

👤 Developer
Name: Harsh Adhiyol

Role: Front-End Developer

Focus: Building responsive React-based UI with Inertia and Tailwind.

