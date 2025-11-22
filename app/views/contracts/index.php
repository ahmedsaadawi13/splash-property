<!-- FILE: /app/views/contracts/index.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Contracts</h2>
    <a href="/contracts/create" class="btn btn-primary">Create Contract</a>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Contract Code</th>
                <th>Type</th>
                <th>Customer</th>
                <th>Unit</th>
                <th>Amount</th>
                <th>Start Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($contracts)): ?>
                <?php foreach ($contracts as $contract): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($contract['contract_code']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($contract['contract_type'])); ?></td>
                        <td><?php echo htmlspecialchars($contract['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($contract['unit_code']); ?></td>
                        <td><?php echo NumberHelper::formatCurrency($contract['total_amount'], $contract['currency']); ?></td>
                        <td><?php echo DateHelper::format($contract['start_date']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $contract['status'] === 'active' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($contract['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="/contracts/<?php echo $contract['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 12px;">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No contracts found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (isset($paginator)): ?>
        <div style="margin-top: 1rem;">
            <?php echo $paginator->render('/contracts'); ?>
        </div>
    <?php endif; ?>
</div>
