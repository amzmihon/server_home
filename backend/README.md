# BAR & Associates - Hosting Control Panel Backend

A comprehensive Laravel-based backend system for managing hosting packages, features, pricing, payments, and customer customization limits.

## 🎯 Features

### Package Management
- Create, read, update, delete hosting packages
- Organize packages by categories (Shared Hosting, VPS, Cloud Storage, etc.)
- Mark packages as popular, active, or inactive
- Set up and manage billing cycles (monthly, annually, biennial)
- Bulk operations (activate, deactivate, delete)

### Feature Configuration
- Define customizable features with multiple types:
  - **Number**: For numeric values (storage, bandwidth, etc.) with min/max constraints
  - **Boolean**: For toggle features (SSL, Backup, DDoS Protection)
  - **Dropdown**: For predefined options (RAM sizes, CPU cores, etc.)
  - **Text**: For text-based configurations
- Set feature pricing modifiers
- Create default values for packages
- Mark features as customizable or fixed

### Dynamic Pricing System
- Base package pricing
- Per-feature price modifiers
- Setup fees
- Tax calculations
- Real-time price calculation based on customization
- Discount percentage support

### Customization Limits
- Set per-user resource limits for specific features
- Enforce or disable limit constraints
- Track current usage against limits
- Support for bulk limit assignment

### Payment Processing
- **Stripe** integration for credit/debit cards
- **bKash** integration for mobile payments
- **Nagad** support (scaffolded)
- **Bank Transfer** manual payment recording
- Payment status tracking (pending, authorized, captured, refunded, failed)
- Transaction history and audit trail

### Order Management
- Create orders with customization data
- Generate invoices automatically
- Track order status
- Support for custom fields per order
- Order number generation with date prefix

### Security Features
- **Authentication**: Laravel Sanctum for API + session authentication
- **Authorization**: Role-based access control (RBAC) with admin middleware
- **2FA**: Two-factor authentication support for admins
- **CSRF Protection**: Token validation on all POST/PUT/DELETE requests
- **HTTPS Enforcement**: Required in production for payment routes
- **Audit Logging**: Complete audit trail of admin actions
- **Input Validation**: Comprehensive validation on all endpoints
- **Soft Deletes**: Data preservation with soft delete capability

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 5.7+ or MariaDB
- Node.js (optional, for frontend build)

### Step 1: Clone and Setup
```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
```

### Step 2: Configure Database
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bar_hosting
DB_USERNAME=root
DB_PASSWORD=
```

### Step 3: Run Migrations
```bash
php artisan migrate
php artisan db:seed --class=HostingSeeder
```

### Step 4: Configure Payment Gateways
Add to `.env`:
```
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...
BKASH_APP_KEY=...
BKASH_APP_SECRET=...
```

### Step 5: Start Development Server
```bash
php artisan serve
```

Access at `http://localhost:8000`

## Docker (local)

Run the backend with Docker instead of installing PHP/MySQL locally:

1. From the repo root, copy env defaults: `cp backend/.env.docker.example backend/.env`, then set a real `APP_KEY` (generate with `docker compose run --rm app php artisan key:generate --show` and paste it into `backend/.env`).
2. Build and start the stack: `docker compose up --build -d`.
3. Run migrations and seed sample data: `docker compose run --rm app php artisan migrate --seed`.
4. App is available at `http://localhost:8080`. MySQL is exposed on `localhost:3307` using `bar_user/bar_pass` (database `bar_hosting` by default).
5. Stop services with `docker compose down` (add `-v` to also remove the MySQL volume).

If you change JS/CSS assets, rebuild the app image: `docker compose build app`.

## 🗂️ Project Structure

```
backend/
├── app/
│   ├── Models/
│   │   ├── User.php                 # User model with admin flag
│   │   ├── Category.php             # Service categories
│   │   ├── Feature.php              # Customizable features
│   │   ├── Package.php              # Hosting packages
│   │   ├── Order.php                # Customer orders
│   │   ├── Payment.php              # Payment transactions
│   │   ├── Invoice.php              # Generated invoices
│   │   ├── CustomizationLimit.php   # User feature limits
│   │   └── AuditLog.php             # Admin action logs
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/               # Admin CRUD controllers
│   │   │   └── Client/              # Client API controllers
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php
│   │       └── CheckPaymentSecurity.php
├── database/
│   ├── migrations/                  # 9 database schemas
│   └── seeders/
│       └── HostingSeeder.php        # Sample data
├── routes/
│   ├── api.php                      # API endpoints
│   └── web.php                      # Web routes
├── resources/views/                 # Blade templates
├── public/js/
│   └── hosting-customizer.js        # Frontend integration library
├── .env.example                     # Environment template
└── composer.json                    # PHP dependencies
```

## 🔌 API Endpoints

### Admin Endpoints (Protected)

**Packages**
```
POST   /admin/packages              Create package
GET    /admin/packages              List packages
GET    /admin/packages/{id}         View package
PUT    /admin/packages/{id}         Update package
DELETE /admin/packages/{id}         Delete package
POST   /admin/packages/bulk-action  Bulk operations
```

**Features**
```
POST   /admin/features              Create feature
GET    /admin/features              List features
GET    /admin/features/{id}         View feature
PUT    /admin/features/{id}         Update feature
DELETE /admin/features/{id}         Delete feature
```

**Orders**
```
GET    /admin/orders                List orders
GET    /admin/orders/{id}           View order
POST   /admin/orders/{id}/cancel    Cancel order
POST   /admin/orders/{id}/generate-invoice  Generate invoice
```

**Payments**
```
GET    /admin/payments              List payments
POST   /admin/payments              Record manual payment
GET    /admin/payments/{id}         View payment
POST   /admin/payments/{id}/refund  Process refund
GET    /admin/payments-dashboard    Payment dashboard
```

**Customization Limits**
```
GET    /admin/users/{id}/customization-limits        View limits
POST   /admin/users/{id}/customization-limits        Set limit
DELETE /admin/users/{id}/customization-limits/{id}   Remove limit
POST   /admin/customization-limits/bulk-set          Bulk set
```

### Client API Endpoints (Protected)

```
GET    /api/packages                                List packages
GET    /api/packages/{id}                           Get details
POST   /api/packages/{id}/customize                 Customize
POST   /api/packages/{id}/validate-customization    Validate
POST   /api/checkout/process                        Create order
POST   /api/checkout/calculate-price                Calculate price
POST   /api/payment/{order}/stripe                  Stripe payment
POST   /api/payment/{order}/bkash                   bKash payment
GET    /api/payment/{order}                         Payment page
```

## 💻 Frontend Integration

### JavaScript Library
Include `hosting-customizer.js` in your frontend:

```html
<script src="/js/hosting-customizer.js"></script>
```

### Usage Example
```javascript
// Initialize customizer
const customizer = new HostingPackageCustomizer({
    apiBase: '/api',
    token: document.querySelector('meta[name="auth-token"]').content
});

// Load and customize package
await customizer.loadPackage(1);
customizer.setCustomization(5, 50);      // Feature 5 = 50GB
customizer.setCustomization(6, true);    // Feature 6 = enabled

// Calculate and checkout
const pricing = await customizer.calculatePrice();
const order = await customizer.checkout('monthly', {
    company_name: 'My Company'
});

// Process payment
const processor = new PaymentProcessor({
    stripePublicKey: 'pk_test_...'
});
await processor.processStripePayment(order.order_id, paymentMethodId);
```

## 🔐 Security Best Practices

1. **Always use HTTPS** in production
2. **Environment Variables**: Store secrets in `.env`, never commit
3. **2FA Admin**: Enable for all admin users
4. **API Keys**: Rotate payment gateway keys regularly
5. **Audit Logs**: Review regularly for suspicious activities
6. **Rate Limiting**: Implement on payment endpoints
7. **Database Backups**: Regular automated backups
8. **Update Dependencies**: Keep Laravel and packages updated

## 🗄️ Database Schema

### Categories
- Service categories (Shared Hosting, VPS, Cloud Storage, etc.)
- Organized with display order
- Soft deletable

### Features
- Customizable features for packages
- Type: number, boolean, dropdown, text
- Min/max values, default values
- Base price (not used in price calculation)
- Customizable flag

### Packages
- Hosting plans with base pricing
- Billing cycles: monthly, annually, biennial
- Setup fees, discount percentages
- Popular flag for featured display

### PackageFeatures
- Junction table linking packages to features
- Price modifiers per feature
- Default values per package

### Orders
- Customer orders with full order data
- Customization data stored as JSON
- Subtotal, tax, total calculations
- Custom fields support

### Payments
- Payment transactions with gateway info
- Multiple gateway support
- Status tracking (pending, captured, refunded, failed)
- Gateway response data stored

### Invoices
- Automatically generated from orders
- Invoice number, date, due date
- Items stored as JSON
- Status tracking

### CustomizationLimits
- Per-user feature limits
- Enforced or optional
- Current usage tracking
- Capacity remaining calculation

### AuditLogs
- All admin actions logged
- Before/after changes captured
- IP address and user agent tracked
- Complete audit trail for compliance

## 🚀 Deployment

### Production Checklist
```
[ ] Configure HTTPS certificate
[ ] Set APP_DEBUG=false in .env
[ ] Use strong database password
[ ] Configure email gateway (for notifications)
[ ] Set up automated backups
[ ] Configure payment gateway production keys
[ ] Enable 2FA for all admins
[ ] Set up monitoring and alerts
[ ] Configure rate limiting
[ ] Review and lock down .env permissions
```

## 📝 License

This project is proprietary software of BAR & Associates.

## 🤝 Support

For issues or questions, contact: support@barassociates.com
