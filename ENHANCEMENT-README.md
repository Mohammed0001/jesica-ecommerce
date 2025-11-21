# Laravel E-commerce Enhancement Documentation

This document covers the new navbar, asset checking, and link validation features added to the Laravel e-commerce project.

## ✨ New Features

### 🎨 Enhanced Navbar
- **Adobe Futura PT font** applied site-wide
- **Responsive design** with search icon, centered logo, and social icons
- **Accessible navigation** with ARIA labels and keyboard focus states
- **Component-based architecture** using Blade components

### 🔍 Asset Checking
- **Automated asset validation** for Blade views and database images
- **Missing asset detection** across public and storage directories
- **Admin dashboard integration** for visual reporting

### 🔗 Link Validation
- **Configurable link checker** for essential site pages
- **Performance monitoring** with response time tracking
- **CI/CD integration** for automated testing

### 🧪 Enhanced Testing
- **Page availability tests** for all essential routes
- **Navbar component testing** with authentication states
- **Accessibility compliance testing**

## 🚀 Setup Instructions

### 1. Adobe Fonts Setup
Replace the placeholder in `resources/views/layouts/app.blade.php`:

```html
<!-- Replace this line -->
<link rel="stylesheet" href="https://use.typekit.net/ckz0ivc.css">

<!-- With your Adobe Fonts embed code -->
<link rel="stylesheet" href="https://use.typekit.net/YOUR-KIT-ID.css">
```

### 2. Install Dependencies
```bash
# Install Node.js dependencies (including axios for link checking)
npm install

# Install Composer dependencies
composer install
```

### 3. Setup Storage Link
```bash
php artisan storage:link
```

### 4. Register Admin Routes (if not auto-discovered)
Add to your `routes/web.php` or admin routes file:

```php
Route::middleware(['auth', 'can:admin'])->group(function () {
    Route::get('/admin/tools/assets', [AdminToolsController::class, 'assetReport'])->name('admin.tools.assets');
    Route::post('/admin/tools/assets/check', [AdminToolsController::class, 'runAssetCheck'])->name('admin.tools.assets.check');
});
```

## 🛠️ Usage

### Asset Checking

#### Command Line
```bash
# Check for missing assets
php artisan assets:check

# Verbose output
php artisan assets:check --verbose
```

#### Admin Dashboard
Visit `/admin/tools/assets` to view the visual asset report with:
- Statistics overview
- Missing assets table
- Link check results
- Help and instructions

### Link Checking

#### Command Line
```bash
# Run link checker
npm run check-links

# Or directly
node scripts/check-links.js
```

#### Configuration
Edit `link-check-config.json` to add new routes:

```json
{
  "baseUrl": "http://localhost:8000",
  "timeout": 10000,
  "routes": [
    "/",
    "/collections",
    "/your-new-page"
  ]
}
```

### Running Tests
```bash
# All tests
php artisan test

# Specific test suites
php artisan test --testsuite=Feature
php artisan test tests/Feature/NavbarTest.php
php artisan test tests/Feature/PagesAvailabilityTest.php
```

## 🏗️ Architecture

### Blade Components
- `<x-navbar />` - Main navigation component
- `<x-logo />` - Centered logo with separators
- `<x-icon-button />` - Reusable icon buttons
- `<x-nav-link />` - Navigation links with active states

### Services
- `AssetCheckerService` - Handles asset validation logic
- Reusable across commands and controllers

### Commands
- `php artisan assets:check` - Asset validation command
- Configurable output and error reporting

### Scripts
- `scripts/check-links.js` - Node.js link checker
- JSON configuration and reporting

## 🔧 CI/CD Integration

The `.github/workflows/checks.yml` file includes:

1. **Asset Validation** - Fails CI if assets are missing
2. **Link Checking** - Validates all configured routes
3. **Test Execution** - Runs all feature and unit tests
4. **Security Auditing** - Composer security checks
5. **Code Quality** - PHP CS Fixer and PHPStan (if available)

### Environment Variables
```env
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
# ... other standard Laravel variables
```

## 📁 File Structure

```
├── app/
│   ├── Console/Commands/
│   │   └── CheckAssets.php
│   ├── Http/Controllers/Admin/
│   │   └── AdminToolsController.php
│   └── Services/
│       └── AssetCheckerService.php
├── resources/views/
│   ├── components/
│   │   ├── navbar.blade.php
│   │   ├── logo.blade.php
│   │   ├── icon-button.blade.php
│   │   └── nav-link.blade.php
│   └── admin/tools/
│       └── asset-report.blade.php
├── scripts/
│   └── check-links.js
├── tests/Feature/
│   ├── NavbarTest.php
│   └── PagesAvailabilityTest.php
├── public/css/
│   └── navbar.css
├── .github/workflows/
│   └── checks.yml
└── link-check-config.json
```

## 🎨 Styling

### CSS Custom Properties
```css
:root {
    --iris-maroon: #8B4513;
    --iris-hover: rgba(139, 69, 19, 0.1);
    --iris-border: #f0f0f0;
}
```

### Typography
All text elements use the Futura PT font stack:
```css
font-family: "Futura PT", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
font-weight: 200;
```

## 🔍 Troubleshooting

### Common Issues

**Missing Assets Error:**
```bash
# Check storage link
php artisan storage:link

# Verify file permissions
chmod -R 755 storage/app/public
```

**Link Checker Fails:**
```bash
# Ensure server is running
php artisan serve --host=127.0.0.1 --port=8000

# Check axios installation
npm install axios
```

**Tests Failing:**
```bash
# Clear caches
php artisan config:clear
php artisan view:clear

# Run migrations
php artisan migrate --env=testing
```

### Asset Path Resolution
The asset checker looks for files in:
1. `public/` directory
2. `storage/app/public/` (via Storage disk)
3. Database image fields

### Link Checker Configuration
- Configurable timeout (default: 10s)
- Supports redirects (3xx responses)
- Reports slow responses (>2s)
- Saves detailed JSON reports

## 📝 Adding New Pages

### To Link Checker
Edit `link-check-config.json`:
```json
{
  "routes": [
    "/existing-route",
    "/your-new-route"
  ]
}
```

### To Tests
Update `tests/Feature/PagesAvailabilityTest.php`:
```php
$publicRoutes = [
    '/your-new-route' => 'your.route.name',
];
```

## 🎯 Best Practices

1. **Always** run asset checks before deployment
2. **Update** link-check-config.json when adding routes
3. **Use** semantic HTML and ARIA labels for accessibility
4. **Test** navbar components with different user states
5. **Monitor** response times in link check reports

## 🤝 Contributing

When adding new components:
1. Follow the `iris-` CSS class prefix
2. Include ARIA labels for accessibility
3. Add corresponding tests
4. Update link checker configuration
5. Document any new asset paths

## 📄 License

This enhancement maintains the same license as the base Laravel project.
