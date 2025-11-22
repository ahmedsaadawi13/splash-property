// FILE: /app/controllers/UnitController.php
<?php

/**
 * Unit Controller
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class UnitController extends Controller
{
    private $unitModel;

    public function __construct()
    {
        parent::__construct();
        $this->unitModel = new UnitModel();
    }

    /**
     * List units
     */
    public function index()
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $page = (int)$this->request->query('page', 1);
        $perPage = 20;

        $filters = [
            'status' => $this->request->query('status'),
            'unit_type' => $this->request->query('unit_type'),
            'listing_type' => $this->request->query('listing_type'),
            'search' => $this->request->query('search')
        ];

        $total = $this->unitModel->count($tenantId, array_filter($filters));
        $paginator = new Paginator($total, $perPage, $page);

        $units = $this->unitModel->search($tenantId, $filters, $perPage, $paginator->getOffset());

        $projectModel = new ProjectModel();
        $projects = $projectModel->all($tenantId);

        return $this->render('units/index', [
            'units' => $units,
            'projects' => $projects,
            'filters' => $filters,
            'paginator' => $paginator
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->requireRole(['tenant_admin', 'sales_manager']);
        $tenantId = $this->getTenantId();

        // Check quota
        $subscriptionModel = new TenantSubscriptionModel();
        if (!$subscriptionModel->canCreate($tenantId, 'unit')) {
            $this->session->flash('error', 'Unit quota exceeded. Please upgrade your plan.');
            return $this->redirect('/units');
        }

        $projectModel = new ProjectModel();
        $projects = $projectModel->all($tenantId);

        return $this->render('units/create', ['projects' => $projects]);
    }

    /**
     * Store unit
     */
    public function store()
    {
        $this->requireRole(['tenant_admin', 'sales_manager']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();

        $data = $this->request->only([
            'project_id', 'unit_code', 'unit_number', 'unit_type', 'floor_number',
            'bedrooms', 'bathrooms', 'area_size', 'area_unit', 'view',
            'furnishing_status', 'listing_type', 'sale_price', 'rent_price',
            'currency', 'status', 'description'
        ]);

        $data['tenant_id'] = $tenantId;

        $id = $this->unitModel->create($data);

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $this->getUser()['id'], 'unit', $id, 'created', "Unit {$data['unit_code']} created");

        $this->session->flash('success', 'Unit created successfully');
        return $this->redirect('/units');
    }

    /**
     * View unit
     */
    public function view($id)
    {
        $this->requireAuth();
        $tenantId = $this->getTenantId();

        $unit = $this->unitModel->find($id, $tenantId);

        if (!$unit) {
            return $this->redirect('/units');
        }

        return $this->render('units/view', ['unit' => $unit]);
    }

    /**
     * Edit unit
     */
    public function edit($id)
    {
        $this->requireRole(['tenant_admin', 'sales_manager']);
        $tenantId = $this->getTenantId();

        $unit = $this->unitModel->find($id, $tenantId);

        if (!$unit) {
            return $this->redirect('/units');
        }

        $projectModel = new ProjectModel();
        $projects = $projectModel->all($tenantId);

        return $this->render('units/edit', [
            'unit' => $unit,
            'projects' => $projects
        ]);
    }

    /**
     * Update unit
     */
    public function update($id)
    {
        $this->requireRole(['tenant_admin', 'sales_manager']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();

        $data = $this->request->only([
            'project_id', 'unit_code', 'unit_number', 'unit_type', 'floor_number',
            'bedrooms', 'bathrooms', 'area_size', 'area_unit', 'view',
            'furnishing_status', 'listing_type', 'sale_price', 'rent_price',
            'currency', 'status', 'description'
        ]);

        $this->unitModel->update($id, $data, $tenantId);

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $this->getUser()['id'], 'unit', $id, 'updated', "Unit updated");

        $this->session->flash('success', 'Unit updated successfully');
        return $this->redirect('/units');
    }

    /**
     * Delete unit
     */
    public function delete($id)
    {
        $this->requireRole(['tenant_admin']);
        $this->verifyCsrf();
        $tenantId = $this->getTenantId();

        $this->unitModel->delete($id, $tenantId);

        // Log activity
        $activityLog = new ActivityLogModel();
        $activityLog->log($tenantId, $this->getUser()['id'], 'unit', $id, 'deleted', "Unit deleted");

        $this->session->flash('success', 'Unit deleted successfully');
        return $this->redirect('/units');
    }
}
