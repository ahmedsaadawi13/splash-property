# SplashProperty - Multi-tenant Real Estate Management SaaS

A complete, production-ready multi-tenant SaaS platform for real estate unit and contract management built with pure PHP and MySQL.

## Features

### Core Functionality
- **Multi-tenancy**: Single database, fully isolated tenant data
- **Unit Management**: Properties, apartments, villas, offices, commercial spaces
- **Customer/Lead Management**: Complete CRM for real estate clients
- **Contract Management**: Sale and rental contracts with full lifecycle
- **Payment Schedules**: Installment tracking and payment collection
- **Booking System**: Unit reservations with expiration handling
- **Visit Scheduling**: Property viewing appointments
- **Customer Portal**: Self-service portal for customers
- **REST API**: Public API for integrations
- **Subscription & Billing**: Plan-based quotas and usage tracking
- **Role-Based Access Control**: 8 user roles with granular permissions
- **Activity Logging**: Complete audit trail
- **Reports & Dashboards**: Role-specific analytics

### Technical Features
- Pure PHP 7.0+ compatible (no frameworks)
- Custom lightweight MVC architecture
- PDO with prepared statements (SQL injection protection)
- CSRF token protection
- Password hashing with bcrypt
- Brute-force login protection
- Secure file upload handling
- Multi-currency support
- Tenant isolation enforcement
- RESTful API with API key authentication

## Tech Stack

- **Backend**: PHP 7.0 - 8.x
- **Database**: MySQL 5.7+ / MariaDB 10.2+
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Web Server**: Apache 2.4+ (with mod_rewrite) or Nginx 1.18+

## Requirements

- PHP >= 7.0
- MySQL >= 5.7 or MariaDB >= 10.2
- Apache with mod_rewrite or Nginx
- PHP Extensions:
  - pdo_mysql
  - mbstring
  - openssl
  - json
  - fileinfo

## Installation

### 1. Clone Repository

```bash
git clone https://github.com/ahmedsaadawi13/SplashProperty.git
cd SplashProperty
```

### 2. Configure Environment

```bash
cp .env.example .env
```

Edit `.env` with your settings:
```
DB_HOST=localhost
DB_DATABASE=splashproperty
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Create Database

```bash
mysql -u root -p
```

```sql
CREATE DATABASE splashproperty CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Import Database Schema

```bash
mysql -u root -p splashproperty < database.sql
```

### 5. Set Permissions

```bash
chmod -R 755 storage/
chmod -R 755 storage/uploads/
chmod -R 755 storage/logs/
```

### 6. Configure Web Server

#### Apache

Create virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName splashproperty.local
    DocumentRoot /path/to/SplashProperty/public

    <Directory /path/to/SplashProperty/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/splashproperty_error.log
    CustomLog ${APACHE_LOG_DIR}/splashproperty_access.log combined
</VirtualHost>
```

Enable site and restart:
```bash
sudo a2ensite splashproperty
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx

```nginx
server {
    listen 80;
    server_name splashproperty.local;
    root /path/to/SplashProperty/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Restart Nginx:
```bash
sudo systemctl restart nginx
```

### 7. Add to Hosts File

```bash
sudo nano /etc/hosts
```

Add:
```
127.0.0.1 splashproperty.local
```

### 8. Access Application

Navigate to: `http://splashproperty.local`

## Default Credentials

### Platform Administrator
- **Email**: platform@splashproperty.com
- **Password**: Admin@123
- **Role**: Full platform access

### Tenant Administrator
- **Email**: admin@demo.com
- **Password**: Admin@123
- **Tenant**: Demo Real Estate
- **Role**: Full tenant access

### Sales Agent
- **Email**: agent@demo.com
- **Password**: Agent@123
- **Tenant**: Demo Real Estate
- **Role**: Sales operations

## User Roles

| Role | Permissions |
|------|------------|
| **platform_admin** | Manage all tenants, subscriptions, system settings |
| **tenant_admin** | Full control within tenant scope |
| **sales_manager** | Manage units, customers, deals, sales team |
| **sales_agent** | Handle assigned customers, visits, bookings |
| **contract_manager** | Create and manage contracts |
| **accountant** | Payments, invoices, financial reports |
| **customer_support** | Customer assistance, basic updates |
| **viewer** | Read-only access |

## Subscription Plans

The system includes 3 pre-configured plans:

### Starter Plan ($99/month)
- 50 units
- 100 customers
- 20 active contracts
- 3 users
- 1GB storage
- Basic features

### Professional Plan ($299/month)
- 200 units
- 500 customers
- 100 active contracts
- 10 users
- 5GB storage
- Customer portal, API access, advanced reports

### Enterprise Plan ($699/month)
- 1,000 units
- 5,000 customers
- 1,000 active contracts
- 50 users
- 20GB storage
- All features

## REST API Documentation

### Authentication

All API requests require an API key in the header:

```
X-API-KEY: your_api_key_here
```

Get API key from: Tenant Settings → API Keys

### Endpoints

#### Create Customer

**POST** `/api/customers/create`

Request:
```json
{
    "first_name": "Ahmed",
    "last_name": "Ali",
    "email": "ahmed@example.com",
    "phone": "+9665xxxxxxx",
    "type": "lead",
    "preferred_city": "Riyadh",
    "preferred_unit_type": "apartment",
    "preferred_budget_min": 300000,
    "preferred_budget_max": 700000,
    "source": "website"
}
```

Response:
```json
{
    "status": "success",
    "customer_id": 123,
    "customer_code": "CUST-000123"
}
```

#### List Available Units

**GET** `/api/units/available`

Query Parameters:
- `project_id` (optional)
- `unit_type` (optional)
- `price_min` (optional)
- `price_max` (optional)
- `bedrooms_min` (optional)

Response:
```json
{
    "status": "success",
    "count": 15,
    "units": [
        {
            "id": 1,
            "unit_code": "ANT-101",
            "unit_type": "apartment",
            "bedrooms": 2,
            "area_size": "120.00",
            "sale_price": "450000.00",
            "currency": "SAR",
            "status": "available"
        }
    ]
}
```

#### Create Booking

**POST** `/api/bookings/create`

Request:
```json
{
    "customer_id": 10,
    "unit_id": 55,
    "booking_date": "2025-01-15",
    "reserved_until_date": "2025-01-22",
    "reservation_amount": 10000,
    "currency": "SAR"
}
```

Response:
```json
{
    "status": "success",
    "booking_id": 45,
    "booking_code": "BKG-000045",
    "unit_status": "reserved"
}
```

#### Add Payment

**POST** `/api/payments/add`

Request:
```json
{
    "contract_id": 20,
    "amount_paid": 50000,
    "payment_date": "2025-01-20",
    "method": "bank_transfer",
    "reference_number": "TXN123456"
}
```

Response:
```json
{
    "status": "success",
    "payment_id": 78
}
```

## Project Structure

```
SplashProperty/
├── app/
│   ├── controllers/      # Request handlers
│   ├── models/          # Database models
│   ├── views/           # HTML templates
│   ├── core/            # Framework core (Router, Controller, Model, etc.)
│   └── helpers/         # Utilities (Validation, Paginator, etc.)
├── config/              # Configuration files
├── public/              # Web root
│   ├── index.php       # Entry point
│   ├── .htaccess       # Apache rewrite rules
│   └── assets/         # CSS, JS, images
├── storage/
│   ├── uploads/        # Uploaded files
│   └── logs/           # Application logs
├── database.sql        # Database schema
├── .env.example        # Environment template
└── README.md           # This file
```

## Security Features

1. **SQL Injection Protection**: All queries use PDO prepared statements
2. **CSRF Protection**: Token validation on all state-changing requests
3. **Password Security**: Bcrypt hashing with password_hash()
4. **Login Brute-Force Protection**: Account lockout after 5 failed attempts
5. **Tenant Isolation**: All queries enforce tenant_id filtering
6. **File Upload Security**: MIME type validation, size limits, unique filenames
7. **XSS Protection**: All output escaped with htmlspecialchars()
8. **Session Security**: HttpOnly cookies, secure flags, regeneration

## Cron Jobs

Add these to your crontab for automated tasks:

```bash
# Update overdue payment schedules (daily at 1 AM)
0 1 * * * cd /path/to/SplashProperty && php scripts/update_overdue_payments.php

# Expire bookings (daily at 2 AM)
0 2 * * * cd /path/to/SplashProperty && php scripts/expire_bookings.php

# Send payment reminders (daily at 9 AM)
0 9 * * * cd /path/to/SplashProperty && php scripts/send_payment_reminders.php
```

## Testing

### Manual Testing Checklist

- [ ] Authentication (login, logout, failed attempts)
- [ ] Tenant isolation (verify data separation)
- [ ] CRUD operations (units, customers, contracts)
- [ ] Booking workflow (create → reserve unit → convert to contract)
- [ ] Payment schedules and payments
- [ ] Subscription quota enforcement
- [ ] API endpoints (create customer, list units, create booking)
- [ ] File uploads (unit images, documents)
- [ ] Role-based permissions
- [ ] Dashboard statistics

### Testing User Flow

1. **Sales Flow**: Lead → Contact → Visit → Booking → Contract → Payment
2. **Rental Flow**: Tenant → Visit → Rental Contract → Monthly Payments
3. **API Integration**: External website → Create Lead → List Units → Create Booking

## Performance Optimization

### Recommended Indexes

All critical indexes are included in `database.sql`:
- Tenant ID on all tables
- Status fields for filtering
- Foreign keys for joins
- Date fields for range queries

### Caching (Future Enhancement)

Consider implementing:
- Redis/Memcached for session storage
- Query result caching for dashboards
- Static asset CDN

### Database Optimization

- Regular ANALYZE TABLE on large tables
- Archive old data periodically
- Monitor slow query log

## Troubleshooting

### Common Issues

**Problem**: "Database connection failed"
- Check .env credentials
- Verify MySQL service is running
- Confirm database exists

**Problem**: "404 on all pages except login"
- Enable Apache mod_rewrite
- Check .htaccess is in /public
- Verify DocumentRoot points to /public

**Problem**: "Permission denied" on file uploads
- Run: `chmod -R 755 storage/`
- Check web server user has write access

**Problem**: "CSRF token validation failed"
- Clear browser cookies
- Check session is started properly

## License

Proprietary - All Rights Reserved

## Support

For support and inquiries:
- Email: ahmed.sha3ban13@gmail.com
- Documentation: https://docs.splashproperty.com
- GitHub Issues: https://github.com/ahmedsaadawi13/SplashProperty/issues

## Credits

Built with ❤️ for modern real estate businesses.

---

**Version**: 1.0.0
**Last Updated**: January 2025
