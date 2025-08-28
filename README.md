# Najib ERP

<div align="center">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 11">
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue 3">
  <img src="https://img.shields.io/badge/PostgreSQL-15-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Inertia.js-purple?style=for-the-badge&logo=inertia&logoColor=white" alt="Inertia.js">
  <img src="https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
</div>

<div align="center">
  <h3>🏪 Enterprise ERP Solution for Retail & Telecom Distribution</h3>
  <p>A comprehensive business management system with POS, inventory management, Telebirr integration, and multi-branch operations.</p>
</div>

---

## ✨ Features

### 🔐 **Security & Access Control**
- **Role-Based Access Control (RBAC)** with granular permissions
- **Branch-scoped authorization** for multi-location operations
- **Audit logging** for all system activities
- **Laravel Sanctum** authentication for API security

### 💰 **Point of Sale (POS)**
- Fast checkout with barcode scanning
- Receipt generation and printing
- Multiple payment methods
- Sales reporting and analytics

### 📦 **Inventory Management**
- Real-time stock tracking across branches
- Automated reorder alerts
- Stock transfers between locations
- Opening balance and stock adjustments

### 📱 **Telebirr Integration**
- Agent management system
- Issue, Loan, Repay, and Top-up operations
- Multi-channel support (CBE, EBIRR, COOPAY, etc.)
- Double-entry accounting integration

### 💻 **Terminal Management**
- Cash drawer operations
- Shift management (open/close)
- Cash variance tracking
- Multi-terminal support per branch

### 📊 **Financial Management**
- Double-entry general ledger
- Automated journal entries
- Financial reporting
- Channel balance tracking

### 📈 **Real-time Dashboard**
- Live sales metrics
- Inventory alerts
- Telebirr transaction monitoring
- Activity feeds with WebSocket updates

---

## 🛠️ Tech Stack

| Component | Technology |
|-----------|------------|
| **Backend** | Laravel 11, PHP 8.2+ |
| **Frontend** | Inertia.js + Vue 3 + Tailwind CSS |
| **Database** | PostgreSQL 15 |
| **Cache/Queue** | Redis + Laravel Horizon |
| **Real-time** | Laravel WebSockets / Pusher |
| **Testing** | Pest (PHP) + Playwright (E2E) |
| **Deployment** | Docker Compose |

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL 15+
- Redis
- Docker & Docker Compose (optional)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/najib-erp.git
   cd najib-erp
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Update your `.env` file with database credentials:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=najib_erp
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seed data**
   ```bash
   php artisan migrate --seed
   php artisan rbac:rebuild
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   # or for development
   npm run dev
   ```

7. **Start the application**
   ```bash
   php artisan serve
   php artisan queue:work
   php artisan websockets:serve
   ```

### 🐳 Docker Setup

For a complete production-like environment:

```bash
docker-compose up -d
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan rbac:rebuild
```

---

## 📚 Documentation

### System Architecture

The application follows a modular architecture with clear separation of concerns:

- **Controllers**: Handle HTTP requests and responses
- **Services**: Business logic and transaction management
- **Repositories**: Data access layer
- **Policies**: Authorization logic
- **Events**: Real-time updates and notifications

### Key Modules

#### Authentication & RBAC
- Multi-branch user management
- Dynamic role assignments
- Capability-based permissions
- Cached policy resolution

#### Inventory System
- Product catalog management
- Multi-location stock tracking
- Automated stock movements
- Transfer workflows

#### Financial System
- Double-entry bookkeeping
- Automated GL posting
- Multi-channel reconciliation
- Idempotent transactions

---

## 🧪 Testing

### Run PHP Tests
```bash
# Unit and Feature tests
./vendor/bin/pest

# With coverage
./vendor/bin/pest --coverage
```

### Run E2E Tests
```bash
# Install Playwright
npx playwright install

# Run E2E tests
npx playwright test
```

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up Redis for caching and queues
- [ ] Configure WebSocket server
- [ ] Set up SSL certificates
- [ ] Configure backup strategy
- [ ] Set up monitoring and logging

### Docker Production

```bash
# Build and deploy
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose exec app php artisan migrate --force

# Optimize for production
docker-compose exec app php artisan optimize
```

---

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Workflow

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🆘 Support

If you encounter any issues or have questions:

- 📧 Email: support@najiberp.com
- 🐛 Issues: [GitHub Issues](https://github.com/yourusername/najib-erp/issues)
- 📖 Documentation: [Wiki](https://github.com/yourusername/najib-erp/wiki)

---

## 🙏 Acknowledgments

- Laravel community for the amazing framework
- Vue.js team for the reactive frontend framework
- Tailwind CSS for the utility-first CSS framework
- All contributors who help make this project better

---

<div align="center">
  <p>Made with ❤️ for retail and telecom businesses</p>
  <p>© 2024 Najib ERP. All rights reserved.</p>
</div>