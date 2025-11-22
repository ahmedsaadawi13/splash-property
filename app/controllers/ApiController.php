// FILE: /app/controllers/ApiController.php
<?php

/**
 * API Controller
 * RESTful API for external integrations
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class ApiController extends Controller
{
    private $tenantId;

    /**
     * Authenticate API request
     */
    private function authenticateApi()
    {
        $apiKey = $this->request->header('X-API-KEY');

        if (!$apiKey) {
            return $this->json(['status' => 'error', 'message' => 'API key required'], 401);
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT tenant_id FROM tenant_api_keys
            WHERE api_key = :api_key AND status = 'active'
            LIMIT 1
        ");
        $stmt->execute([':api_key' => $apiKey]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return $this->json(['status' => 'error', 'message' => 'Invalid API key'], 401);
        }

        $this->tenantId = $result['tenant_id'];

        // Update last used
        $stmt = $db->prepare("UPDATE tenant_api_keys SET last_used_at = NOW() WHERE api_key = :api_key");
        $stmt->execute([':api_key' => $apiKey]);

        return true;
    }

    /**
     * Create customer
     * POST /api/customers/create
     */
    public function createCustomer()
    {
        $auth = $this->authenticateApi();
        if ($auth !== true) {
            return $auth;
        }

        $data = $this->request->json();

        // Validate
        $validation = new Validation();
        $valid = $validation->validate($data, [
            'first_name' => 'required',
            'email' => 'email',
            'phone' => 'required'
        ]);

        if (!$valid) {
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 400);
        }

        // Check quota
        $subscriptionModel = new TenantSubscriptionModel();
        if (!$subscriptionModel->canCreate($this->tenantId, 'customer')) {
            return $this->json([
                'status' => 'error',
                'message' => 'Customer quota exceeded'
            ], 403);
        }

        // Create customer
        $customerModel = new CustomerModel();
        $data['tenant_id'] = $this->tenantId;
        $data['customer_code'] = $customerModel->generateCode($this->tenantId);
        $data['source'] = $data['source'] ?? 'api';
        $data['status'] = $data['status'] ?? 'new';
        $data['type'] = $data['type'] ?? 'lead';

        $customerId = $customerModel->create($data);

        return $this->json([
            'status' => 'success',
            'customer_id' => $customerId,
            'customer_code' => $data['customer_code']
        ], 201);
    }

    /**
     * List available units
     * GET /api/units/available
     */
    public function listAvailableUnits()
    {
        $auth = $this->authenticateApi();
        if ($auth !== true) {
            return $auth;
        }

        $unitModel = new UnitModel();

        $filters = [
            'project_id' => $this->request->query('project_id'),
            'unit_type' => $this->request->query('unit_type'),
            'listing_type' => $this->request->query('listing_type'),
            'price_min' => $this->request->query('price_min'),
            'price_max' => $this->request->query('price_max'),
            'bedrooms_min' => $this->request->query('bedrooms_min')
        ];

        $filters['status'] = 'available';

        $units = $unitModel->search($this->tenantId, $filters, 100, 0);

        // Format response
        $formatted = array_map(function($unit) {
            return [
                'id' => $unit['id'],
                'unit_code' => $unit['unit_code'],
                'project_name' => $unit['project_name'] ?? null,
                'unit_type' => $unit['unit_type'],
                'bedrooms' => $unit['bedrooms'],
                'bathrooms' => $unit['bathrooms'],
                'area_size' => $unit['area_size'],
                'area_unit' => $unit['area_unit'],
                'sale_price' => $unit['sale_price'],
                'rent_price' => $unit['rent_price'],
                'currency' => $unit['currency'],
                'status' => $unit['status']
            ];
        }, $units);

        return $this->json([
            'status' => 'success',
            'count' => count($formatted),
            'units' => $formatted
        ]);
    }

    /**
     * Create booking
     * POST /api/bookings/create
     */
    public function createBooking()
    {
        $auth = $this->authenticateApi();
        if ($auth !== true) {
            return $auth;
        }

        $data = $this->request->json();

        // Validate
        $validation = new Validation();
        $valid = $validation->validate($data, [
            'customer_id' => 'required|integer',
            'unit_id' => 'required|integer',
            'booking_date' => 'required|date',
            'reserved_until_date' => 'required|date'
        ]);

        if (!$valid) {
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 400);
        }

        // Check unit is available
        $unitModel = new UnitModel();
        $unit = $unitModel->find($data['unit_id'], $this->tenantId);

        if (!$unit || $unit['status'] !== 'available') {
            return $this->json([
                'status' => 'error',
                'message' => 'Unit not available'
            ], 400);
        }

        // Create booking
        $bookingModel = new BookingModel();
        $data['tenant_id'] = $this->tenantId;
        $data['booking_code'] = $bookingModel->generateCode($this->tenantId);
        $data['booking_status'] = 'active';
        $data['currency'] = $data['currency'] ?? $unit['currency'];

        $bookingId = $bookingModel->create($data);

        // Update unit status
        $unitModel->update($data['unit_id'], ['status' => 'reserved'], $this->tenantId);

        return $this->json([
            'status' => 'success',
            'booking_id' => $bookingId,
            'booking_code' => $data['booking_code'],
            'unit_status' => 'reserved'
        ], 201);
    }

    /**
     * Add payment
     * POST /api/payments/add
     */
    public function addPayment()
    {
        $auth = $this->authenticateApi();
        if ($auth !== true) {
            return $auth;
        }

        $data = $this->request->json();

        // Validate
        $validation = new Validation();
        $valid = $validation->validate($data, [
            'contract_id' => 'required|integer',
            'amount_paid' => 'required|numeric',
            'payment_date' => 'required|date',
            'method' => 'required'
        ]);

        if (!$valid) {
            return $this->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validation->errors()
            ], 400);
        }

        // Create payment
        $paymentModel = new PaymentModel();
        $data['tenant_id'] = $this->tenantId;

        $paymentId = $paymentModel->create($data);

        // Update schedule status if schedule_id provided
        if (!empty($data['schedule_id'])) {
            $scheduleModel = new PaymentScheduleModel();
            $scheduleModel->updateStatus($data['schedule_id'], $this->tenantId);
        }

        return $this->json([
            'status' => 'success',
            'payment_id' => $paymentId
        ], 201);
    }
}
