// FILE: /app/controllers/ContractController.php
<?php

/**
 * Contract Controller
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class ContractController extends Controller
{
    private $contractModel;

    public function __construct()
    {
        parent::__construct();
        $this->contractModel = new ContractModel();
    }

    /**
     * List contracts
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = (int)$this->request->query('page', 1);
        $perPage = 20;

        $filters = [
            'status' => $this->request->query('status'),
            'contract_type' => $this->request->query('contract_type'),
            'search' => $this->request->query('search')
        ];

        $total = $this->contractModel->count($tenantId, array_filter($filters));
        $paginator = new Paginator($total, $perPage, $page);

        $contracts = $this->contractModel->getWithRelations($tenantId, $filters, $perPage, $paginator->getOffset());

        return $this->render('contracts/index', [
            'contracts' => $contracts,
            'filters' => $filters,
            'paginator' => $paginator
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'contract_manager']);
        $tenantId = $this->getTenantId();

        $customerModel = new CustomerModel();
        $unitModel = new UnitModel();

        return $this->render('contracts/create', [
            'customers' => $customerModel->all($tenantId),
            'units' => $unitModel->getAvailable($tenantId)
        ]);
    }

    /**
     * Store contract
     */
    public function store()
    {
        $this->requireRole(['tenant_admin', 'sales_manager', 'contract_manager']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();
        $userId = $this->getUser()['id'];

        $data = $this->request->only([
            'contract_type', 'customer_id', 'unit_id', 'start_date', 'end_date',
            'signing_date', 'base_amount', 'tax_percentage', 'currency',
            'payment_plan_type', 'status'
        ]);

        // Calculate tax and total
        $data['tax_amount'] = ($data['base_amount'] * $data['tax_percentage']) / 100;
        $data['total_amount'] = $data['base_amount'] + $data['tax_amount'];
        $data['tenant_id'] = $tenantId;
        $data['created_by_user_id'] = $userId;
        $data['contract_code'] = $this->contractModel->generateCode($tenantId, $data['contract_type']);

        $contractId = $this->contractModel->create($data);

        // Update unit status if contract is active
        if ($data['status'] === 'active') {
            $unitModel = new UnitModel();
            $newStatus = $data['contract_type'] === 'sale' ? 'sold' : 'rented';
            $unitModel->update($data['unit_id'], ['status' => $newStatus], $tenantId);
        }

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $userId, 'contract', $contractId, 'created', "Contract {$data['contract_code']} created");

        $this->session->flash('success', 'Contract created successfully');
        return $this->redirect('/contracts/' . $contractId);
    }

    /**
     * View contract
     */
    public function view($id)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $contract = $this->contractModel->find($id, $tenantId);

        if (!$contract) {
            return $this->redirect('/contracts');
        }

        // Get payment schedules and payments
        $scheduleModel = new PaymentScheduleModel();
        $paymentModel = new PaymentModel();

        $data = [
            'contract' => $contract,
            'schedules' => $scheduleModel->getByContract($id, $tenantId),
            'payments' => $paymentModel->getByContract($id, $tenantId),
            'total_paid' => $paymentModel->getTotalPaidByContract($id, $tenantId)
        ];

        return $this->render('contracts/view', $data);
    }
}
