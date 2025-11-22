<!-- FILE: /app/views/units/create.php -->
<h2>Create New Unit</h2>

<div class="card">
    <form method="POST" action="/units/create">
        <?php echo CSRF::field(); ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="unit_code">Unit Code *</label>
                <input type="text" id="unit_code" name="unit_code" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="unit_number">Unit Number</label>
                <input type="text" id="unit_number" name="unit_number" class="form-control">
            </div>

            <div class="form-group">
                <label for="project_id">Project</label>
                <select id="project_id" name="project_id" class="form-control">
                    <option value="">Select Project</option>
                    <?php foreach ($projects as $project): ?>
                        <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="unit_type">Unit Type *</label>
                <select id="unit_type" name="unit_type" class="form-control" required>
                    <option value="apartment">Apartment</option>
                    <option value="villa">Villa</option>
                    <option value="office">Office</option>
                    <option value="shop">Shop</option>
                    <option value="land">Land</option>
                    <option value="warehouse">Warehouse</option>
                </select>
            </div>

            <div class="form-group">
                <label for="bedrooms">Bedrooms</label>
                <input type="number" id="bedrooms" name="bedrooms" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label for="bathrooms">Bathrooms</label>
                <input type="number" id="bathrooms" name="bathrooms" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label for="area_size">Area Size</label>
                <input type="number" step="0.01" id="area_size" name="area_size" class="form-control">
            </div>

            <div class="form-group">
                <label for="area_unit">Area Unit</label>
                <select id="area_unit" name="area_unit" class="form-control">
                    <option value="sqm">Square Meters (sqm)</option>
                    <option value="sqft">Square Feet (sqft)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="listing_type">Listing Type *</label>
                <select id="listing_type" name="listing_type" class="form-control" required>
                    <option value="sale">For Sale</option>
                    <option value="rent">For Rent</option>
                    <option value="both">Both</option>
                </select>
            </div>

            <div class="form-group">
                <label for="sale_price">Sale Price</label>
                <input type="number" step="0.01" id="sale_price" name="sale_price" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label for="rent_price">Rent Price (Monthly)</label>
                <input type="number" step="0.01" id="rent_price" name="rent_price" class="form-control" value="0">
            </div>

            <div class="form-group">
                <label for="currency">Currency</label>
                <select id="currency" name="currency" class="form-control">
                    <option value="SAR">SAR</option>
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="AED">AED</option>
                </select>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="available">Available</option>
                    <option value="blocked">Blocked</option>
                </select>
            </div>

            <div class="form-group">
                <label for="furnishing_status">Furnishing Status</label>
                <select id="furnishing_status" name="furnishing_status" class="form-control">
                    <option value="unfurnished">Unfurnished</option>
                    <option value="semi_furnished">Semi Furnished</option>
                    <option value="furnished">Furnished</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control"></textarea>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <button type="submit" class="btn btn-primary">Create Unit</button>
            <a href="/units" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
