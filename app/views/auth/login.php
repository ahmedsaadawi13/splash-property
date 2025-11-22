<!-- FILE: /app/views/auth/login.php -->
<div class="login-container">
    <div class="login-box">
        <h2>SplashProperty</h2>
        <p style="text-align: center; color: #666; margin-bottom: 2rem;">Multi-tenant Real Estate Management</p>

        <?php if (isset($_SESSION['_flash']['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['_flash']['error']); unset($_SESSION['_flash']['error']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <?php echo CSRF::field(); ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e0e0e0;">
            <p style="font-size: 12px; color: #666;">Demo Credentials:</p>
            <p style="font-size: 12px;"><strong>Platform Admin:</strong> platform@splashproperty.com / Admin@123</p>
            <p style="font-size: 12px;"><strong>Tenant Admin:</strong> admin@demo.com / Admin@123</p>
            <p style="font-size: 12px;"><strong>Sales Agent:</strong> agent@demo.com / Agent@123</p>
        </div>
    </div>
</div>
