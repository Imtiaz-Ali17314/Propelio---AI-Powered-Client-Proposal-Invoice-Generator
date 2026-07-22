<div align="center">

<img src="https://img.shields.io/badge/Propelio-AI%20Powered-7C3AED?style=for-the-badge&logo=sparkles&logoColor=white" alt="Propelio" />

# ✨ Propelio — AI-Powered Client Proposal & Invoice Generator

**The complete agency management platform. Generate professional proposals with AI, manage clients, create invoices, and track payments — all in one sleek dashboard.**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Vue.js](https://img.shields.io/badge/Vue.js-3.x-42B883?style=flat-square&logo=vue.js&logoColor=white)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Groq AI](https://img.shields.io/badge/Groq%20AI-LLaMA%203-F55036?style=flat-square&logo=groq&logoColor=white)](https://groq.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=flat-square)](https://opensource.org/licenses/MIT)

[🚀 Features](#-features) · [📸 Screenshots](#-screenshots) · [🛠️ Tech Stack](#️-tech-stack) · [⚡ Quick Start](#-quick-start) · [📡 API Reference](#-api-reference) · [🤝 Contributing](#-contributing)

</div>

---

## 🎯 Overview

**Propelio** is a full-stack SaaS-style application built for freelancers and digital agencies. It eliminates the manual effort of writing project proposals by using **Groq AI (LLaMA 3)** to ultra-fastly and intelligently generate project scope, timelines, and cost breakdowns from a simple client brief.

Once a proposal is approved, converting it into a professional invoice — complete with line items, payment tracking, and PDF export — takes just a few clicks.

> 💡 **Built with:** Laravel 12 (REST API) + Vue 3 (SPA) + Groq AI (LLaMA 3 Engine)

---

## ✨ Features

### 🤖 AI-Powered Proposal Engine (Powered by Groq AI)
- **5-Step Wizard**: Brief → Scope → Timeline → Cost Breakdown → Final Review
- **Groq AI (LLaMA 3)** generates high-precision project scope (deliverables + out-of-scope items)
- Automated timeline breakdown and realistic cost estimates in USD
- **Regenerate** any AI section instantly with one click
- Fully editable AI outputs — add, remove, or modify any field
- *(Note: `GeminiService.php` is also preserved in codebase for future multi-provider extensions)*

### 📋 Proposal Management
- Create, edit, send, and track proposals
- Status lifecycle: `Draft → Sent → Accepted / Rejected`
- Convert accepted proposals directly into invoices
- Rich proposal history per client

### 🧾 Invoice Management
- Auto-populate invoice from proposal data
- Add/remove line items with real-time subtotal calculation
- Status tracking: `Unpaid → Partially Paid → Paid → Overdue`
- **Download PDF** — server-side rendered with DomPDF
- **Record Payments** with date and notes
- Cancel / Void invoices

### 👥 Client Management
- Full client directory with company, email, and contact details
- Per-client proposal and invoice history
- Quick-access counters on client list view

### 📊 Analytics Dashboard
- **Total Revenue** from recorded payments
- **Pending Amount** outstanding across all invoices
- **6-Month Revenue Trend** bar chart (Chart.js)
- **Recent Activity** feed — proposals and invoices at a glance
- Proposal status breakdown (Accepted, Sent, Draft)

### 🔐 Authentication & Security
- Laravel Sanctum cookie-based SPA authentication
- Route-level authorization with Laravel Policies
- CORS configured for SPA + API separation

---

## 📸 Screenshots

### 🏠 Dashboard
> Real-time agency overview with revenue metrics, cash flow trends, and recent activity.

![Dashboard](./screenshots/dashboard.jpg)

---

### 👥 Clients Directory
> Manage all client accounts with proposal and invoice counts at a glance.

![Clients Directory](./screenshots/clients.jpg)

---

### 🧠 AI Proposal Wizard — Scope Generation
> Describe your project. Groq AI generates the scope, deliverables, timeline, and cost breakdown.

![AI Proposal Wizard](./screenshots/proposal-wizard.jpg)

---

### 🧾 Invoice Detail View
> Full invoice breakdown with payment history tracking and PDF download.

![Invoice View](./screenshots/invoice.jpg)

---

## 🛠️ Tech Stack

| Layer | Technology | Purpose |
|---|---|---|
| **Backend** | Laravel 12 | REST API, Business Logic, Auth |
| **Frontend** | Vue 3 + Vite | Single Page Application (SPA) |
| **State Management** | Pinia | Reactive global state |
| **Routing** | Vue Router 4 | Client-side routing |
| **Styling** | Tailwind CSS 4 | Utility-first UI styling |
| **Charts** | Chart.js | Revenue trend visualization |
| **Primary AI Engine** | Groq AI (LLaMA 3) | High-speed AI proposal generation |
| **Future AI Service** | Google Gemini (Service File) | Retained for future expansion |
| **PDF Generation** | barryvdh/laravel-dompdf | Server-side PDF rendering |
| **Authentication** | Laravel Sanctum | SPA session-based auth |
| **Database** | MySQL | Relational data storage |
| **HTTP Client** | Axios | API communication |

---

## 📁 Project Structure

```
propelio/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── ClientController.php
│   │   │       ├── DashboardController.php
│   │   │       ├── InvoiceController.php
│   │   │       ├── PaymentController.php
│   │   │       └── ProposalController.php  ← Injects GroqService for AI logic
│   │   └── Requests/
│   ├── Models/
│   │   ├── Client.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── Payment.php
│   │   ├── Proposal.php
│   │   └── User.php
│   ├── Policies/
│   └── Services/
│       ├── GroqService.php         ← Active Groq AI (LLaMA 3) service engine
│       ├── GeminiService.php       ← Retained for future expansion
│       ├── InvoicePdfService.php   ← PDF invoice generation
│       └── ProposalPdfService.php  ← PDF proposal generation
│
├── resources/js/
│   ├── views/
│   │   ├── Dashboard.vue
│   │   ├── auth/
│   │   ├── clients/
│   │   ├── proposals/
│   │   │   ├── ProposalList.vue
│   │   │   └── ProposalWizard.vue  ← 5-step AI wizard
│   │   └── invoices/
│   │       ├── InvoiceList.vue
│   │       ├── InvoiceForm.vue
│   │       └── InvoiceShow.vue
│   ├── components/
│   │   ├── layout/
│   │   ├── dashboard/
│   │   ├── proposals/
│   │   ├── invoices/
│   │   └── ui/
│   ├── stores/          ← Pinia stores
│   ├── composables/     ← Vue composables
│   ├── router/          ← Vue Router config
│   └── api/             ← Axios API layer
│
├── database/
│   └── migrations/      ← 12 migration files
│
└── screenshots/         ← App preview images
```

---

## ⚡ Quick Start

### Prerequisites

Make sure you have the following installed:

- **PHP** `>= 8.2`
- **Composer** `>= 2.x`
- **Node.js** `>= 18.x` & **npm**
- **MySQL** `>= 8.0`

### 1. Clone the Repository

```bash
git clone https://github.com/Imtiaz-Ali17314/Propelio---AI-Powered-Client-Proposal-Invoice-Generator.git
cd Propelio---AI-Powered-Client-Proposal-Invoice-Generator
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install

# Node dependencies
npm install
```

### 3. Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

Now open `.env` and configure the following:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=propelio
DB_USERNAME=root
DB_PASSWORD=your_password

# AI Services (Groq AI powers the application)
GROQ_API_KEY=your_groq_api_key_here
GEMINI_API_KEY=optional_future_key_here

# App URL
APP_URL=http://localhost:8000
```

> 🔑 **Get your Free API Key:**
> - **Groq API Key** → [Groq Console](https://console.groq.com/) (Generous free tier with ultra-fast LLaMA 3 inference)

### 4. Database Setup

```bash
# Create MySQL database first
mysql -u root -p -e "CREATE DATABASE propelio;"

# Run migrations
php artisan migrate
```

### 5. Run the Application

```bash
# Run everything concurrently (recommended)
composer dev
```

This starts:
- `php artisan serve` → Laravel API on `http://localhost:8000`
- `npm run dev` → Vite dev server on `http://localhost:5173`
- `php artisan queue:listen` → Background job worker
- `php artisan pail` → Log viewer

> 🌐 Open your browser at **`http://localhost:8000`**

---

### ⚡ One-Command Setup (Alternative)

```bash
composer setup
```

This will automatically: install dependencies → generate app key → run migrations → build frontend assets.

---

## 📡 API Reference

All API endpoints are prefixed with `/api` and protected via **Laravel Sanctum** (except auth routes).

### 🔐 Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/auth/register` | Register new user |
| `POST` | `/api/auth/login` | Login & get session cookie |
| `POST` | `/api/auth/logout` | Logout & invalidate session |
| `GET` | `/api/auth/user` | Get authenticated user |

### 👥 Clients

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/clients` | List all clients |
| `POST` | `/api/clients` | Create new client |
| `GET` | `/api/clients/{id}` | Get client details |
| `PUT` | `/api/clients/{id}` | Update client |
| `DELETE` | `/api/clients/{id}` | Delete client |

### 📋 Proposals

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/proposals` | List all proposals |
| `POST` | `/api/proposals` | Create new proposal |
| `GET` | `/api/proposals/{id}` | Get proposal detail |
| `PUT` | `/api/proposals/{id}` | Update proposal |
| `DELETE` | `/api/proposals/{id}` | Delete proposal |
| `POST` | `/api/proposals/{id}/generate-scope` | 🤖 Groq AI: Generate scope |
| `POST` | `/api/proposals/{id}/generate-timeline` | 🤖 Groq AI: Generate timeline |
| `POST` | `/api/proposals/{id}/generate-cost` | 🤖 Groq AI: Generate cost breakdown |
| `POST` | `/api/proposals/{id}/send` | Mark proposal as sent |
| `GET` | `/api/proposals/{id}/pdf` | Download proposal PDF |

### 🧾 Invoices

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/invoices` | List all invoices |
| `POST` | `/api/invoices` | Create new invoice |
| `GET` | `/api/invoices/{id}` | Get invoice detail |
| `PUT` | `/api/invoices/{id}` | Update invoice |
| `DELETE` | `/api/invoices/{id}` | Delete invoice |
| `POST` | `/api/invoices/{id}/void` | Void/cancel invoice |
| `GET` | `/api/invoices/{id}/pdf` | Download invoice PDF |

### 💳 Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/invoices/{id}/payments` | Record a payment |
| `DELETE` | `/api/invoices/{invoice}/payments/{payment}` | Delete a payment |

### 📊 Dashboard

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/dashboard` | Get all dashboard metrics |

---

## 🗄️ Database Schema

```
users
  └── clients (user_id)
        ├── proposals (client_id, user_id)
        └── invoices (client_id, user_id)
              ├── invoice_items (invoice_id)
              └── payments (invoice_id)
```

**Key Models:**

| Model | Key Fields |
|-------|-----------|
| `User` | name, email, password |
| `Client` | name, company, email, phone |
| `Proposal` | client_id, title, brief, status, scope, timeline, cost_breakdown |
| `Invoice` | client_id, invoice_number, status, due_date, tax_percent |
| `InvoiceItem` | invoice_id, description, qty, rate, amount |
| `Payment` | invoice_id, amount, payment_date, notes |

---

## 🤖 AI Integration Details

Propelio uses **Groq AI (LLaMA 3)** as its core engine for lightning-fast proposal generation:

### Groq AI Service Engine (`GroqService.php`)
- **Scope Generation**: Extracts project overview, deliverables list, and out-of-scope items from the client brief.
- **Timeline Generation**: Computes total duration and detailed phase breakdown based on brief and generated scope.
- **Cost Breakdown**: Generates realistic USD pricing items, subtotal, and total amount.

```php
// app/Http/Controllers/Api/ProposalController.php
$this->ai->generateScope($proposal->brief);
$this->ai->generateTimeline($proposal->brief, $proposal->scope);
$this->ai->generateCostBreakdown($proposal->brief, $proposal->scope, $proposal->timeline);
```

### Prompt Engineering
All Groq AI requests return **strict JSON format** enforced through system prompt definitions and server-side JSON decoding with error handling.

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────┐
│                   Browser (SPA)                  │
│         Vue 3 + Pinia + Vue Router               │
│              Tailwind CSS 4                      │
└──────────────────┬──────────────────────────────┘
                   │ Axios HTTP (Cookie Auth)
┌──────────────────▼──────────────────────────────┐
│              Laravel 12 REST API                  │
│    Sanctum Auth  │  Policies  │  Controllers     │
│              Service Layer                        │
│   GroqService (Active)  │  PdfService            │
└──────┬───────────────────┬──────────────────────┘
       │                   │
┌──────▼──┐          ┌─────▼──────┐
│  MySQL  │          │  Groq API  │
│   DB    │          │  (LLaMA 3) │
└─────────┘          └────────────┘
```

---

## ⚙️ Configuration

### Queue (for background jobs)

```bash
# Ensure queue is running for any background processing
php artisan queue:work
```

### Session & CORS

The `.env` is pre-configured for local development. For production, update:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

---

## 🧪 Running Tests

```bash
# Run all tests
composer test

# Or directly with PHPUnit
php artisan test
```

---

## 🚀 Deployment Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure production database credentials
- [ ] Add real `GROQ_API_KEY`
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm run build`
- [ ] Run `php artisan config:cache && php artisan route:cache`
- [ ] Set up a queue worker (Supervisor recommended)
- [ ] Configure web server (Nginx/Apache) to point to `/public`

---

## 🤝 Contributing

Contributions are welcome! Here's how to get started:

1. **Fork** the repository
2. **Create** a feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** your changes: `git commit -m 'feat: add amazing feature'`
4. **Push** to the branch: `git push origin feature/amazing-feature`
5. **Open** a Pull Request

Please follow [Conventional Commits](https://www.conventionalcommits.org/) for commit messages.

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

<div align="center">

**Imtiaz Ali**

[![GitHub](https://img.shields.io/badge/GitHub-Imtiaz--Ali17314-181717?style=flat-square&logo=github)](https://github.com/Imtiaz-Ali17314)

*Built with ❤️ using Laravel, Vue 3, and Groq AI*

</div>

---

<div align="center">

⭐ **If you found this project useful, please give it a star!** ⭐

</div>


