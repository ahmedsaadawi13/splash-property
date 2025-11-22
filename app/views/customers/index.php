<!-- FILE: /app/views/customers/index.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Customers</h2>
    <a href="/customers/create" class="btn btn-primary">Add New Customer</a>
</div>

<div class="card">
    <form method="GET" action="/customers">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Name, email, phone...">
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" name="type" class="form-control">
                    <option value="">All</option>
                    <option value="lead" <?php echo ($filters['type'] ?? '') === 'lead' ? 'selected' : ''; ?>>Lead</option>
                    <option value="buyer" <?php echo ($filters['type'] ?? '') === 'buyer' ? 'selected' : ''; ?>>Buyer</option>
                    <option value="tenant" <?php echo ($filters['type'] ?? '') === 'tenant' ? 'selected' : ''; ?>>Tenant</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All</option>
                    <option value="new" <?php echo ($filters['status'] ?? '') === 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="contacted" <?php echo ($filters['status'] ?? '') === 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="qualified" <?php echo ($filters['status'] ?? '') === 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                    <option value="active_client" <?php echo ($filters['status'] ?? '') === 'active_client' ? 'selected' : ''; ?>>Active Client</option>
                </select>
            </div>
            <div class="form-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Type</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($customers)): ?>
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['customer_code']); ?></td>
                        <td><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($customer['type'])); ?></td>
                        <td><span class="badge badge-info"><?php echo htmlspecialchars($customer['status']); ?></span></td>
                        <td>
                            <a href="/customers/<?php echo $customer['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 12px;">View</a>
                            <a href="/customers/<?php echo $customer['id']; ?>/edit" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No customers found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (isset($paginator)): ?>
        <div style="margin-top: 1rem;">
            <?php echo $paginator->render('/customers'); ?>
        </div>
    <?php endif; ?>
</div>
