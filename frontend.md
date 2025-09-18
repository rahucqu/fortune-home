# 💻 Front-End Development – Fortune Home

This document outlines the **Front-End development work** completed by **Harsh** for the [Fortune Home Project](https://github.com/rahucqu/fortune-homes).  
The frontend is built using **Inertia.js**, **React**, **TailwindCSS**, and **shadcn/ui**, integrated into the **Laravel 12** backend.

---

## 📂 Repo Structure (Frontend Related)

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
