<!-- FILE: /app/views/layouts/main.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? $title . ' - ' : ''; ?>SplashProperty</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <?php echo CSRF::meta(); ?>
</head>
<body>
    <header>
        <div class="container">
            <h1>SplashProperty</h1>
            <nav>
                <ul>
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/units">Units</a></li>
                    <li><a href="/customers">Customers</a></li>
                    <li><a href="/contracts">Contracts</a></li>
                    <li><a href="/logout">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="container">
            <?php if (isset($_SESSION['_flash']['success'])): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($_SESSION['_flash']['success']); unset($_SESSION['_flash']['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['_flash']['error'])): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($_SESSION['_flash']['error']); unset($_SESSION['_flash']['error']); ?>
                </div>
            <?php endif; ?>

            <?php echo $content; ?>
        </div>
    </main>

    <footer style="background: #2c3e50; color: white; padding: 1rem 0; text-align: center; margin-top: 2rem;">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> SplashProperty - Multi-tenant Real Estate Management SaaS</p>
        </div>
    </footer>
</body>
</html>
