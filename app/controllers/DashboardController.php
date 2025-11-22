// FILE: /app/controllers/DashboardController.php
<?php

/**
 * Dashboard Controller
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAuth();

        $user = $this->getUser();
        $tenantId = $user['tenant_id'];

        // Platform admin sees platform dashboard
        if ($user['role'] === 'platform_admin') {
            return $this->platformDashboard();
        }

        // Check subscription status
        $subscriptionModel = new TenantSubscriptionModel();
        $subscription = $subscriptionModel->getActiveSubscription($tenantId);

        if (!$subscription || !in_array($subscription['status'], ['active', 'trialing'])) {
            return $this->render('dashboard/subscription_inactive', [
                'subscription' => $subscription
            ]);
        }

        // Different dashboards for different roles
        switch ($user['role']) {
            case 'tenant_admin':
            case 'sales_manager':
                return $this->adminDashboard($tenantId);
            case 'sales_agent':
                return $this->agentDashboard($tenantId, $user['id']);
            case 'accountant':
                return $this->accountantDashboard($tenantId);
            default:
                return $this->defaultDashboard($tenantId);
        }
    }

    /**
     * Platform admin dashboard
     */
    private function platformDashboard()
    {
        $tenantModel = new TenantModel();
        $subscriptionModel = new TenantSubscriptionModel();

        $data = [
            'total_tenants' => $tenantModel->count(),
            'active_tenants' => $tenantModel->count(null, ['status' => 'active']),
            'tenants' => $tenantModel->all(null, 'created_at DESC')
        ];

        return $this->render('dashboard/platform_admin', $data);
    }

    /**
     * Admin dashboard
     */
    private function adminDashboard($tenantId)
    {
        $unitModel = new UnitModel();
        $customerModel = new CustomerModel();
        $contractModel = new ContractModel();
        $paymentScheduleModel = new PaymentScheduleModel();
        $bookingModel = new BookingModel();

        $unitCounts = $unitModel->countByStatus($tenantId);

        $data = [
            'total_units' => array_sum($unitCounts),
            'available_units' => $unitCounts['available'] ?? 0,
            'sold_units' => $unitCounts['sold'] ?? 0,
            'rented_units' => $unitCounts['rented'] ?? 0,
            'total_customers' => $customerModel->count($tenantId),
            'total_contracts' => $contractModel->count($tenantId, ['status' => 'active']),
            'overdue_payments' => $paymentScheduleModel->count($tenantId, ['status' => 'overdue']),
            'recent_bookings' => $bookingModel->getWithRelations($tenantId, 'active'),
            'recent_contracts' => $contractModel->getWithRelations($tenantId, [], 5, 0),
            'upcoming_payments' => $paymentScheduleModel->getUpcoming($tenantId, 30)
        ];

        return $this->render('dashboard/admin', $data);
    }

    /**
     * Sales agent dashboard
     */
    private function agentDashboard($tenantId, $userId)
    {
        $customerModel = new CustomerModel();
        $visitModel = new VisitModel();
        $bookingModel = new BookingModel();

        $data = [
            'my_customers' => $customerModel->all($tenantId),
            'my_visits' => $visitModel->getUpcoming($tenantId, 7),
            'my_bookings' => $bookingModel->getWithRelations($tenantId, 'active')
        ];

        return $this->render('dashboard/agent', $data);
    }

    /**
     * Accountant dashboard
     */
    private function accountantDashboard($tenantId)
    {
        $paymentModel = new PaymentModel();
        $paymentScheduleModel = new PaymentScheduleModel();

        $thisMonth = DateHelper::startOfMonth();
        $endMonth = DateHelper::endOfMonth();

        $data = [
            'total_collected_month' => $paymentModel->getTotalByPeriod($tenantId, $thisMonth, $endMonth),
            'overdue_schedules' => $paymentScheduleModel->getOverdue($tenantId),
            'upcoming_schedules' => $paymentScheduleModel->getUpcoming($tenantId, 30)
        ];

        return $this->render('dashboard/accountant', $data);
    }

    /**
     * Default dashboard
     */
    private function defaultDashboard($tenantId)
    {
        return $this->render('dashboard/default', []);
    }
}
