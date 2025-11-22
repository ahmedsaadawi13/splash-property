<!-- FILE: /app/views/dashboard/admin.php -->
<h2>Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Units</h3>
        <div class="value"><?php echo number_format($total_units); ?></div>
    </div>
    <div class="stat-card">
        <h3>Available Units</h3>
        <div class="value"><?php echo number_format($available_units); ?></div>
    </div>
    <div class="stat-card">
        <h3>Sold Units</h3>
        <div class="value"><?php echo number_format($sold_units); ?></div>
    </div>
    <div class="stat-card">
        <h3>Rented Units</h3>
        <div class="value"><?php echo number_format($rented_units); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Customers</h3>
        <div class="value"><?php echo number_format($total_customers); ?></div>
    </div>
    <div class="stat-card">
        <h3>Active Contracts</h3>
        <div class="value"><?php echo number_format($total_contracts); ?></div>
    </div>
    <div class="stat-card">
        <h3>Overdue Payments</h3>
        <div class="value" style="color: #e74c3c;"><?php echo number_format($overdue_payments); ?></div>
    </div>
</div>

<div class="card">
    <div class="card-header">Recent Bookings</div>
    <?php if (!empty($recent_bookings)): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking Code</th>
                    <th>Customer</th>
                    <th>Unit</th>
                    <th>Reserved Until</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($recent_bookings, 0, 5) as $booking): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_code']); ?></td>
                        <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($booking['unit_code']); ?></td>
                        <td><?php echo htmlspecialchars($booking['reserved_until_date']); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($booking['booking_status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No recent bookings</p>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">Recent Contracts</div>
    <?php if (!empty($recent_contracts)): ?>
        <table>
            <thead>
                <tr>
                    <th>Contract Code</th>
                    <th>Type</th>
                    <th>Customer</th>
                    <th>Unit</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_contracts as $contract): ?>
                    <tr>
                        <td><a href="/contracts/<?php echo $contract['id']; ?>"><?php echo htmlspecialchars($contract['contract_code']); ?></a></td>
                        <td><?php echo htmlspecialchars(ucfirst($contract['contract_type'])); ?></td>
                        <td><?php echo htmlspecialchars($contract['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($contract['unit_code']); ?></td>
                        <td><?php echo NumberHelper::formatCurrency($contract['total_amount'], $contract['currency']); ?></td>
                        <td><span class="badge badge-<?php echo $contract['status'] === 'active' ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($contract['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No recent contracts</p>
    <?php endif; ?>
</div>
