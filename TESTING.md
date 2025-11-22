# SplashProperty - Testing Guide

## Testing Checklist

### 1. Authentication & Security Tests

#### Login System
- [ ] Successful login with valid credentials
- [ ] Failed login with invalid email
- [ ] Failed login with invalid password
- [ ] Account lockout after 5 failed attempts (15 min)
- [ ] Session regeneration after login
- [ ] Logout clears session
- [ ] CSRF token validation on forms
- [ ] Redirect to login when accessing protected pages

#### Password Security
- [ ] Passwords stored as bcrypt hashes
- [ ] Cannot login with plain password from database

### 2. Tenant Isolation Tests

#### Data Separation
- [ ] Users from Tenant A cannot see Tenant B data
- [ ] All queries include tenant_id filter
- [ ] API key scoped to correct tenant
- [ ] File uploads isolated by tenant

#### Cross-Tenant Attack Prevention
- [ ] Cannot access units from another tenant by manipulating URL
- [ ] Cannot access customers from another tenant
- [ ] Cannot access contracts from another tenant

### 3. Role-Based Access Control Tests

#### Platform Admin
- [ ] Can view all tenants
- [ ] Can create new tenants
- [ ] Can manage subscription plans
- [ ] Cannot be assigned to a tenant

#### Tenant Admin
- [ ] Full CRUD on units
- [ ] Full CRUD on customers
- [ ] Full CRUD on contracts
- [ ] Can manage tenant users
- [ ] Cannot access platform admin features

#### Sales Agent
- [ ] Can create customers
- [ ] Can schedule visits
- [ ] Can create bookings
- [ ] Cannot delete units
- [ ] Cannot access financial reports

#### Accountant
- [ ] Can view all payments
- [ ] Can add payments
- [ ] Can view financial reports
- [ ] Cannot create contracts

#### Viewer
- [ ] Read-only access to allowed sections
- [ ] Cannot create, update, or delete anything

### 4. Unit Management Tests

#### CRUD Operations
- [ ] Create unit with all fields
- [ ] Create unit with minimal required fields
- [ ] Update unit details
- [ ] Delete unit (admin only)
- [ ] List units with pagination
- [ ] Filter units by status
- [ ] Filter units by type
- [ ] Search units by code/number

#### Business Logic
- [ ] Unit status changes to "reserved" when booked
- [ ] Unit status changes to "sold" when sale contract is active
- [ ] Unit status changes to "rented" when rental contract is active
- [ ] Cannot create unit when quota exceeded

### 5. Customer Management Tests

#### CRUD Operations
- [ ] Create customer with auto-generated code
- [ ] Update customer details
- [ ] View customer profile
- [ ] List customers with pagination
- [ ] Filter customers by type
- [ ] Filter customers by status
- [ ] Search customers by name/email/phone

#### Business Logic
- [ ] Cannot create customer when quota exceeded
- [ ] Customer code is unique per tenant
- [ ] Assigned agent can see customer

### 6. Visit & Booking Tests

#### Visits
- [ ] Schedule visit for customer and unit
- [ ] View upcoming visits
- [ ] Mark visit as completed
- [ ] Mark visit as no-show
- [ ] Cancel visit

#### Bookings
- [ ] Create booking for available unit
- [ ] Cannot book already reserved unit
- [ ] Booking reserves the unit
- [ ] Expired bookings release unit
- [ ] Convert booking to contract

### 7. Contract Tests

#### Contract Creation
- [ ] Create sale contract
- [ ] Create rental contract
- [ ] Auto-generate contract code
- [ ] Calculate tax correctly
- [ ] Calculate total amount correctly
- [ ] Cannot create contract when quota exceeded

#### Contract Lifecycle
- [ ] Draft contract doesn't affect unit status
- [ ] Active contract updates unit status
- [ ] Completed contract releases unit (for rent)
- [ ] Canceled contract releases unit

#### Payment Schedules
- [ ] Generate installment schedule
- [ ] Update schedule status based on payments
- [ ] Mark as overdue after due date
- [ ] Mark as paid when fully paid

### 8. Payment Tests

#### Payment Operations
- [ ] Add payment to contract
- [ ] Link payment to schedule
- [ ] Update schedule status after payment
- [ ] Calculate total paid correctly
- [ ] Calculate outstanding balance

#### Payment Reports
- [ ] View payments by date range
- [ ] View overdue payments
- [ ] View upcoming payments
- [ ] Total collected by period

### 9. Subscription & Quota Tests

#### Quota Enforcement
- [ ] Check unit quota before creation
- [ ] Check customer quota before creation
- [ ] Check contract quota before creation
- [ ] Check user quota before creation
- [ ] Show error message when quota exceeded

#### Subscription Status
- [ ] Active subscription allows all operations
- [ ] Trialing subscription allows all operations
- [ ] Inactive subscription shows read-only mode
- [ ] Past_due subscription shows warning

#### Usage Tracking
- [ ] Unit count updates correctly
- [ ] Customer count updates correctly
- [ ] Contract count updates correctly
- [ ] User count updates correctly

### 10. API Tests

#### Authentication
- [ ] Valid API key allows access
- [ ] Invalid API key returns 401
- [ ] Missing API key returns 401
- [ ] API key scoped to correct tenant

#### Create Customer Endpoint
- [ ] POST /api/customers/create with valid data
- [ ] Returns customer ID and code
- [ ] Validates required fields
- [ ] Returns 400 for invalid data
- [ ] Checks quota before creation

#### List Available Units Endpoint
- [ ] GET /api/units/available returns units
- [ ] Filter by project_id works
- [ ] Filter by unit_type works
- [ ] Filter by price range works
- [ ] Only returns available units

#### Create Booking Endpoint
- [ ] POST /api/bookings/create with valid data
- [ ] Returns booking ID and code
- [ ] Updates unit status to reserved
- [ ] Validates unit is available
- [ ] Returns error if unit unavailable

#### Add Payment Endpoint
- [ ] POST /api/payments/add with valid data
- [ ] Returns payment ID
- [ ] Updates schedule status
- [ ] Validates contract exists

### 11. File Upload Tests

#### Security
- [ ] Only allowed MIME types accepted
- [ ] File size limit enforced (10MB)
- [ ] Files renamed to prevent overwrites
- [ ] Files stored in tenant-specific folders
- [ ] Cannot access other tenant's files

#### Unit Images
- [ ] Upload single image
- [ ] Upload multiple images
- [ ] Delete image
- [ ] Images displayed in unit view

#### Documents
- [ ] Upload customer documents
- [ ] Upload contract documents
- [ ] PDF and DOCX accepted
- [ ] Documents downloadable

### 12. Dashboard Tests

#### Platform Admin Dashboard
- [ ] Shows total tenants
- [ ] Shows active tenants
- [ ] Lists all tenants
- [ ] Shows subscription status

#### Tenant Admin Dashboard
- [ ] Shows unit statistics
- [ ] Shows customer count
- [ ] Shows active contracts count
- [ ] Shows overdue payments count
- [ ] Shows recent bookings
- [ ] Shows recent contracts

#### Sales Agent Dashboard
- [ ] Shows assigned customers
- [ ] Shows upcoming visits
- [ ] Shows active bookings

#### Accountant Dashboard
- [ ] Shows total collected this month
- [ ] Shows overdue schedules
- [ ] Shows upcoming schedules

### 13. Pagination Tests

- [ ] First page shows correct items
- [ ] Last page shows correct items
- [ ] Page numbers generated correctly
- [ ] Previous/Next buttons work
- [ ] Info text shows correct range

### 14. Activity Log Tests

- [ ] User login logged
- [ ] User logout logged
- [ ] Unit creation logged
- [ ] Customer creation logged
- [ ] Contract creation logged
- [ ] Logs include IP address
- [ ] Logs include user agent

### 15. Data Validation Tests

#### Form Validation
- [ ] Required fields validated
- [ ] Email format validated
- [ ] Numeric fields validated
- [ ] Date format validated
- [ ] Min/Max length validated

#### Business Rules
- [ ] Cannot delete unit with active contract
- [ ] Cannot delete customer with active contract
- [ ] Contract dates validated (end > start)
- [ ] Payment amount validated (> 0)

## Performance Tests

### Load Testing
- [ ] 100 concurrent users
- [ ] 1000 units load in < 2 seconds
- [ ] Search with 10,000 customers < 1 second
- [ ] Dashboard loads < 1 second

### Database Performance
- [ ] All queries use indexes
- [ ] No N+1 query problems
- [ ] Joins optimized
- [ ] Large result sets paginated

## Browser Compatibility

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## Security Audit

- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities
- [ ] No CSRF vulnerabilities
- [ ] No directory traversal in file uploads
- [ ] No sensitive data in logs
- [ ] No passwords in plain text
- [ ] Sessions expire properly
- [ ] HTTPS enforced (production)

## Final Integration Tests

### Complete User Flow 1: Sale
1. [ ] Create customer (lead)
2. [ ] Schedule visit
3. [ ] Mark visit completed
4. [ ] Create booking
5. [ ] Create sale contract
6. [ ] Generate payment schedule (4 installments)
7. [ ] Add payment 1
8. [ ] Add payment 2
9. [ ] Add payment 3
10. [ ] Add payment 4
11. [ ] Verify contract completed
12. [ ] Verify unit marked as sold

### Complete User Flow 2: Rental
1. [ ] Create customer (tenant)
2. [ ] Schedule visit
3. [ ] Create rental contract (12 months)
4. [ ] Generate monthly payment schedule
5. [ ] Add payments for 3 months
6. [ ] Verify outstanding balance
7. [ ] Check overdue if payment missed

### Complete User Flow 3: API Integration
1. [ ] Generate API key
2. [ ] API: Create customer
3. [ ] API: List available units
4. [ ] API: Create booking
5. [ ] Verify booking in system
6. [ ] API: Add payment
7. [ ] Verify payment in system

## Test Results Template

| Test Category | Pass | Fail | Skip | Notes |
|--------------|------|------|------|-------|
| Authentication | | | | |
| Tenant Isolation | | | | |
| RBAC | | | | |
| Units | | | | |
| Customers | | | | |
| Visits & Bookings | | | | |
| Contracts | | | | |
| Payments | | | | |
| Subscriptions | | | | |
| API | | | | |
| File Uploads | | | | |
| Dashboards | | | | |
| Security | | | | |

## Bug Reporting

When reporting bugs, include:
1. Steps to reproduce
2. Expected behavior
3. Actual behavior
4. User role being tested
5. Browser and version
6. Screenshots if applicable
7. Error messages from logs
