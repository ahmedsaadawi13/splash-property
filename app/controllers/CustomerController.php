// FILE: /app/controllers/CustomerController.php
<?php

/**
 * Customer Controller
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class CustomerController extends Controller
{
    private $customerModel;

    public function __construct()
    {
        parent::__construct();
        $this->customerModel = new CustomerModel();
    }

    /**
     * List customers
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = (int)$this->request->query('page', 1);
        $perPage = 20;

        $filters = [
            'status' => $this->request->query('status'),
            'type' => $this->request->query('type'),
            'search' => $this->request->query('search')
        ];

        $total = $this->customerModel->count($tenantId, array_filter($filters));
        $paginator = new Paginator($total, $perPage, $page);

        $customers = $this->customerModel->search($tenantId, $filters, $perPage, $paginator->getOffset());

        return $this->render('customers/index', [
            'customers' => $customers,
            'filters' => $filters,
            'paginator' => $paginator
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'sales_agent']);
        $tenantId = $this->getTenantId();

        // Check quota
        $subscriptionModel = new TenantSubscriptionModel();
        if (!$subscriptionModel->canCreate($tenantId, 'customer')) {
            $this->session->flash('error', 'Customer quota exceeded. Please upgrade your plan.');
            return $this->redirect('/customers');
        }

        $userModel = new UserModel();
        $agents = $userModel->getSalesAgents($tenantId);

        return $this->render('customers/create', ['agents' => $agents]);
    }

    /**
     * Store customer
     */
    public function store()
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'sales_agent']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();

        $data = $this->request->only([
            'first_name', 'last_name', 'email', 'phone', 'secondary_phone',
            'type', 'preferred_contact_channel', 'preferred_city', 'preferred_unit_type',
            'preferred_budget_min', 'preferred_budget_max', 'source', 'assigned_to_user_id',
            'status', 'notes'
        ]);

        $data['tenant_id'] = $tenantId;
        $data['customer_code'] = $this->customerModel->generateCode($tenantId);

        $id = $this->customerModel->create($data);

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $this->getUser()['id'], 'customer', $id, 'created', "Customer {$data['customer_code']} created");

        $this->session->flash('success', 'Customer created successfully');
        return $this->redirect('/customers');
    }

    /**
     * View customer
     */
    public function view($id)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $customer = $this->customerModel->find($id, $tenantId);

        if (!$customer) {
            return $this->redirect('/customers');
        }

        // Get related data
        $visitModel = new VisitModel();
        $bookingModel = new BookingModel();
        $contractModel = new ContractModel();

        $data = [
            'customer' => $customer,
            'visits' => $visitModel->getWithRelations($tenantId, ['customer_id' => $id]),
            'bookings' => $bookingModel->getWithRelations($tenantId),
            'contracts' => $contractModel->getWithRelations($tenantId, ['customer_id' => $id])
        ];

        return $this->render('customers/view', $data);
    }

    /**
     * Edit customer
     */
    public function edit($id)
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'sales_agent']);
        $tenantId = $this->getTenantId();

        $customer = $this->customerModel->find($id, $tenantId);

        if (!$customer) {
            return $this->redirect('/customers');
        }

        $userModel = new UserModel();
        $agents = $userModel->getSalesAgents($tenantId);

        return $this->render('customers/edit', [
            'customer' => $customer,
            'agents' => $agents
        ]);
    }

    /**
     * Update customer
     */
    public function update($id)
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'sales_agent']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();

        $data = $this->request->only([
            'first_name', 'last_name', 'email', 'phone', 'secondary_phone',
            'type', 'preferred_contact_channel', 'preferred_city', 'preferred_unit_type',
            'preferred_budget_min', 'preferred_budget_max', 'source', 'assigned_to_user_id',
            'status', 'notes'
        ]);

        $this->customerModel->update($id, $data, $tenantId);

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $this->getUser()['id'], 'customer', $id, 'updated', "Customer updated");

        $this->session->flash('success', 'Customer updated successfully');
        return $this->redirect('/customers');
    }
}
