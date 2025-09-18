# 🏠 Fortune Home – Real Estate Website

Fortune Home is a modern real estate web application that allows users to browse, search, and inquire about residential and commercial properties.  
Built with **Laravel 12**, **Inertia.js**, **React**, **TailwindCSS**, and **shadcn/ui**, the project combines powerful backend features with a clean, responsive, and accessible frontend.

---

## ✨ Features

- 🔍 **Property Listings** – Display residential and commercial properties with price, type, location, and amenities.  
- 🛠 **Advanced Search & Filters** – Filter by location, price range, property type, bedrooms, bathrooms, and amenities.  
- 🏡 **Property Details** – Photo gallery, detailed description, Google Maps integration, and agent contact form.  
- 📋 **Admin Panel** – Manage property listings, edit details, and handle inquiries.  
- 📱 **Responsive Design** – Optimized for desktop, tablet, and mobile using Tailwind and shadcn/ui.  
- 🔒 **Secure Forms** – Inquiry forms with spam protection and validation.  
- 🌐 **Future Enhancements** – User accounts, saved properties, alerts, and messaging with agents.  

---

## 🛠 Tech Stack

- **Backend:** [Laravel 12](https://laravel.com/)  
- **Frontend Integration:** [Inertia.js](https://inertiajs.com/)  
- **UI Framework:** [React](https://react.dev/)  
- **Styling:** [TailwindCSS](https://tailwindcss.com/) + [shadcn/ui](https://ui.shadcn.com/)  
- **Database:** MySQL 
- **Maps:** Google Maps API  
- **Authentication:** Laravel Jetstream 

---

## 🚀 Installation

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL or MariaDB
- Git

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/yourusername/fortune-home.git
cd fortune-home

# 2. Install backend dependencies
composer install

# 3. Install frontend dependencies
npm install && npm run dev

# 4. Copy environment file and configure database
cp .env.example .env
php artisan key:generate

# 5. Run migrations and seed data
php artisan migrate --seed

# 6. Start Laravel server
php artisan serve
