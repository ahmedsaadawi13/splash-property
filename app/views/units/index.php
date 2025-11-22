<!-- FILE: /app/views/units/index.php -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2>Units</h2>
    <a href="/units/create" class="btn btn-primary">Add New Unit</a>
</div>

<div class="card">
    <form method="GET" action="/units">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" placeholder="Unit code, number...">
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="">All</option>
                    <option value="available" <?php echo ($filters['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="reserved" <?php echo ($filters['status'] ?? '') === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
                    <option value="sold" <?php echo ($filters['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="rented" <?php echo ($filters['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Rented</option>
                </select>
            </div>
            <div class="form-group">
                <label for="unit_type">Type</label>
                <select id="unit_type" name="unit_type" class="form-control">
                    <option value="">All</option>
                    <option value="apartment" <?php echo ($filters['unit_type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="villa" <?php echo ($filters['unit_type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                    <option value="office" <?php echo ($filters['unit_type'] ?? '') === 'office' ? 'selected' : ''; ?>>Office</option>
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
                <th>Unit Code</th>
                <th>Type</th>
                <th>Bedrooms</th>
                <th>Area</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($units)): ?>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($unit['unit_code']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($unit['unit_type'])); ?></td>
                        <td><?php echo htmlspecialchars($unit['bedrooms']); ?></td>
                        <td><?php echo NumberHelper::formatArea($unit['area_size'], $unit['area_unit']); ?></td>
                        <td>
                            <?php if ($unit['sale_price'] > 0): ?>
                                <?php echo NumberHelper::formatCurrency($unit['sale_price'], $unit['currency']); ?>
                            <?php elseif ($unit['rent_price'] > 0): ?>
                                <?php echo NumberHelper::formatCurrency($unit['rent_price'], $unit['currency']); ?>/mo
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $unit['status'] === 'available' ? 'success' : 'warning'; ?>">
                                <?php echo htmlspecialchars($unit['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="/units/<?php echo $unit['id']; ?>" class="btn btn-secondary" style="padding: 0.25rem 0.5rem; font-size: 12px;">View</a>
                            <a href="/units/<?php echo $unit['id']; ?>/edit" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 12px;">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No units found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (isset($paginator)): ?>
        <div style="margin-top: 1rem;">
            <?php echo $paginator->render('/units'); ?>
        </div>
    <?php endif; ?>
</div>
