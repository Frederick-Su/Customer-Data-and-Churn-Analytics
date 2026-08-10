# Customer Data & Churn Analytics Dashboard

An enterprise web application built with **Laravel** and **Python** designed to analyze operational Excel datasets and generate multi-region customer insights, tenure breakdowns, and churn trend visualizations.

---

## Features

- **Excel Data Processing:** Drag-and-drop or upload `.xlsx` and `.xls` files directly through the dashboard.
- **Python Analytics Pipeline:** Custom data engine using `pandas`, `seaborn`, and `matplotlib` to parse metrics and render visual reports.
- **Interactive Visual Dashboard:**
  - **Mosaic Distribution Plot:** Visual demographic segment distribution.
  - **Tenure & Loyalty Analysis:** Regional tenure breakdown paired with site-level customer summaries.
  - **Active & Churn Trends:** Tracking active customer counts and monthly churn percentages.
  - **Site Weekly Churn:** Granular 6-month historical churn trends per site.
- **Dark Mode Support:** Full Tailwind dark theme with system preference auto-detection (`prefers-color-scheme`) and `localStorage` persistence.
- **Interactive Lightbox:** Fullscreen image modal for close-up review of generated charts.

---

## Tech Stack

- **Backend:** PHP 8.x / [Laravel 10.x](https://laravel.com)
- **Data & Analytics Engine:** Python 3.10+ (`pandas`, `matplotlib`, `seaborn`, `openpyxl`)
- **Frontend:** [Tailwind CSS v3](https://tailwindcss.com), [Alpine.js v3](https://alpinejs.dev)
- **Database / Cache:** SQLite / MySQL (as configured in `.env`)

---

## Getting Started

### Prerequisites

Ensure you have the following installed on your machine:
- **PHP** >= 8.1
- **Composer**
- **Node.js** & **NPM**
- **Python** 3.10+ & `pip`

---

### Installation & Setup

#### 1. Clone the Repository
```bash
git clone [https://github.com/Frederick-Su/Customer-Data-and-Churn-Analytics.git](https://github.com/Frederick-Su/Customer-Data-and-Churn-Analytics.git)
cd Customer-Data-and-Churn-Analytics

1. composer install
2. copy .env.example .env
3. php artisan key:generate
4. php artisan migrate
5. php artisan storage:link
6. php artisan serve