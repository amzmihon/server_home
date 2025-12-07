# BAR & Associates Backend - Quick Reference

## 🚀 Quick Start (5 minutes)

```bash
# 1. Setup
cd backend
cp .env.example .env
composer install
php artisan key:generate

# 2. Configure database in .env
DB_DATABASE=bar_hosting
DB_USERNAME=root

# 3. Create database & run migrations
php artisan migrate
php artisan db:seed --class=HostingSeeder

# 4. Create admin user
php artisan tinker
>>> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('pass'), 'is_admin' => true])

# 5. Run server
php artisan serve
# Access at http://localhost:8000
```

## 📁 Key Files

| File | Purpose |
|------|---------|
| `app/Models/Package.php` | Hosting packages with pricing calculation |
| `app/Models/Feature.php` | Customizable features (number, boolean, dropdown, text) |
| `app/Models/CustomizationLimit.php` | Per-user feature limits |
| `app/Models/Order.php` | Customer orders with customization data |
| `app/Models/Payment.php` | Payment transactions (Stripe, bKash, etc.) |
| `app/Http/Controllers/Admin/PackageController.php` | Package CRUD |
| `app/Http/Controllers/Client/CheckoutController.php` | Order & pricing |
| `app/Http/Controllers/Client/PaymentController.php` | Payment processing |
| `database/seeders/HostingSeeder.php` | Sample data |
| `public/js/hosting-customizer.js` | Frontend integration |
| `routes/api.php` | API endpoints |
| `routes/web.php` | Admin routes |

## 🔑 Core Concepts

### Creating a Package
```php
$package = Package::create([
    'category_id' => 1,
    'name' => 'Solo',
    'slug' => 'solo',
    'base_price' => 99,
    'billing_cycle' => 'monthly',
]);

// Add features
$package->features()->attach([
    1 => ['value' => '10', 'price_modifier' => 0],
    2 => ['value' => '100', 'price_modifier' => 0.5],
]);
```

### Calculating Price
```php
$featureValues = [1 => 20, 2 => 150]; // Feature IDs => values
$totalPrice = $package->calculatePrice($featureValues);
```

### Setting User Limits
```php
CustomizationLimit::create([
    'user_id' => 5,
    'feature_id' => 3,
    'max_value' => 100,
    'is_enforced' => true,
]);

// Check if user can customize
$limit = CustomizationLimit::where('user_id', 5)->where('feature_id', 3)->first();
$limit->canAddMore(50); // Returns boolean
```

### Creating Orders
```php
$order = Order::create([
    'user_id' => 5,
    'package_id' => 1,
    'order_number' => Order::generateOrderNumber(),
    'customization_data' => [1 => 50, 2 => true],
    'subtotal' => 150,
    'tax_amount' => 22.50,
    'total_amount' => 172.50,
    'billing_cycle' => 'monthly',
]);
```

### Processing Payments
```php
// Create payment record
$payment = Payment::create([
    'order_id' => $order->id,
    'user_id' => $user->id,
    'gateway' => 'stripe',
    'transaction_id' => 'ch_1234...',
    'amount' => 172.50,
    'status' => 'captured',
    'completed_at' => now(),
]);

// Generate invoice
$invoice = Invoice::create([
    'order_id' => $order->id,
    'invoice_number' => Invoice::generateInvoiceNumber(),
    'invoice_date' => today(),
    'due_date' => today()->addDays(30),
    'subtotal' => 150,
    'tax_amount' => 22.50,
    'total_amount' => 172.50,
]);
```

## 🔌 API Examples

### List Packages
```bash
curl -H "Authorization: Bearer TOKEN" \
  http://localhost:8000/api/packages
```

### Customize Package
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "customization": [
      {"feature_id": 1, "value": 50},
      {"feature_id": 2, "value": true}
    ],
    "billing_cycle": "monthly"
  }' \
  http://localhost:8000/api/packages/1/customize
```

### Checkout
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "package_id": 1,
    "customization": [{"feature_id": 1, "value": 50}],
    "billing_cycle": "monthly"
  }' \
  http://localhost:8000/api/checkout/process
```

### Calculate Price
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "package_id": 1,
    "customization": [
      {"feature_id": 1, "value": 50},
      {"feature_id": 2, "value": 100}
    ]
  }' \
  http://localhost:8000/api/checkout/calculate-price
```

### Create Admin Package
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "category_id": 1,
    "name": "Pro Plan",
    "slug": "pro-plan",
    "base_price": 399,
    "billing_cycle": "monthly"
  }' \
  http://localhost:8000/admin/packages
```

## 🔐 Authentication

### Get API Token (Sanctum)
```bash
curl -X POST http://localhost:8000/login \
  -d "email=user@example.com&password=password"
```

### Use Token
```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## 🗄️ Database Queries

### Get Package with Features
```php
$package = Package::with('features', 'category')->find(1);
$features = $package->features; // Includes pivot data
```

### Get User Orders
```php
$orders = Order::where('user_id', 5)
  ->with('package', 'payment')
  ->get();
```

### Get Successful Payments
```php
$payments = Payment::where('status', 'captured')
  ->whereMonth('created_at', now()->month)
  ->get();
```

### Audit Log
```php
$logs = AuditLog::where('action', 'UPDATE')
  ->where('entity_type', 'Package')
  ->get();
```

## 📊 Admin Dashboard Stats

```php
// Revenue
$todayRevenue = Payment::where('status', 'captured')
  ->whereDate('created_at', today())->sum('amount');

// Orders
$pendingOrders = Order::where('status', 'pending')->count();

// Payments by gateway
$stripe = Payment::byGateway('stripe')->count();
$bkash = Payment::byGateway('bkash')->count();
```

## ⚡ Common Tasks

### Add New Feature Type
1. Add to `Feature::validTypes()` in model
2. Update feature validation in controller
3. Update frontend to handle new type

### Add Payment Gateway
1. Create gateway class in `app/Services/PaymentGateways/`
2. Add to `Payment::GATEWAY_*` constants
3. Implement webhook handler in `PaymentController`

### Set Customization Limits
```php
// For single user
CustomizationLimit::updateOrCreate(
    ['user_id' => 5, 'feature_id' => 3],
    ['max_value' => 100, 'is_enforced' => true]
);

// For bulk users
$customizer->bulkSet(['user_ids' => [1,2,3]], ['limits' => [...]]);
```

## 🔗 Related Files
- Frontend: `../hosting-customizer.js`
- Frontend: `../index.html` and service pages
- Original instructions: `../.github/copilot-instructions.md`

---

For full documentation, see `README.md` and `IMPLEMENTATION_GUIDE.md`
