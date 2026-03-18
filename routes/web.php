<?php

use App\Http\Controllers\Admin\Attribute\AttributeController;
use App\Http\Controllers\Admin\Banner\BannerController;
use App\Http\Controllers\Admin\Video\VideoController;
use App\Http\Controllers\Frontend\AboutUs\AboutUsController;
use App\Http\Controllers\Frontend\Address\AddressController;
use App\Http\Controllers\Frontend\Auth\SignUpController;
use App\Http\Controllers\Frontend\BulkBuy\BulkBuyController;
use App\Http\Controllers\Frontend\CancellationAndRefund\CancellationAndRefundController;
use App\Http\Controllers\Frontend\Cart\CartController;
use App\Http\Controllers\Frontend\ContactUs\ContactUsController;
use App\Http\Controllers\Frontend\Dashboard\DashboardController;
use App\Http\Controllers\Frontend\Index\IndexController;
use App\Http\Controllers\Frontend\PrivacyPolicy\PrivacyPolicyController;
use App\Http\Controllers\Frontend\Product\ShopPageController;
use App\Http\Controllers\Frontend\Product\SingleProductController;
use App\Http\Controllers\Frontend\ReturnAndExchange\ReturnAndExchangeController;
use App\Http\Controllers\Frontend\Search\SearchController;
use App\Http\Controllers\Frontend\ShippingAndDelivery\ShippingAndDeliveryController;
use App\Http\Controllers\Frontend\ShopGrid\ShopGridController;
use App\Http\Controllers\Frontend\TermsAndConditions\TermsAndConditionsController;
use App\Http\Controllers\Frontend\Wishlist\WishlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\IthickLogisticsController;

use App\Http\Controllers\Admin\CouponController as AdminCouponController;

//Admin Controllersf
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TwilioController;
use App\Http\Controllers\Admin\MapController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\VarientController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\SocietyController;
/* use App\Http\Controllers\Admin\TimeSlotController; */
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\AdminorderController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\ReedemController;
use App\Http\Controllers\Admin\SecondaryBannerController;
use App\Http\Controllers\Admin\SecretloginController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\HideController;
use App\Http\Controllers\Admin\NoticeController;
use App\Http\Controllers\Admin\ImportExcelController;
use App\Http\Controllers\Admin\SalesreportController;
use App\Http\Controllers\Admin\RequiredController;
use App\Http\Controllers\Admin\ProductapproveController;
use App\Http\Controllers\Admin\StorehideController;
use App\Http\Controllers\Admin\PayoutController;
use App\Http\Controllers\Admin\ReasonController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\PayController;
use App\Http\Controllers\Admin\StorecallbackController;
use App\Http\Controllers\Admin\DrivercallbackController;
use App\Http\Controllers\Admin\UsercallbackController;
use App\Http\Controllers\Admin\UserwalletController;
use App\Http\Controllers\Admin\SubController;
use App\Http\Controllers\Admin\OrderstatussController;
use App\Http\Controllers\Admin\TaxController;
use App\Http\Controllers\Admin\TrendingController;
use App\Http\Controllers\Admin\IdController;
use App\Http\Controllers\Admin\SubadController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TaxreportController;
use App\Http\Controllers\Admin\CityAdminController;

//Store Controllers
use App\Http\Controllers\Store\AssignHomecateController;
use App\Http\Controllers\Store\AssignorderController;
use App\Http\Controllers\Store\ByphotoController;
use App\Http\Controllers\Store\CallbackController;
use App\Http\Controllers\Store\CouponController;
use App\Http\Controllers\Store\DealController;
use App\Http\Controllers\Store\DeliveryboyController;
use App\Http\Controllers\Store\DriverfinanceController;
use App\Http\Controllers\Store\HomecateController;
use App\Http\Controllers\Store\StoreHomeController;
use App\Http\Controllers\Store\ImpexcelController;
use App\Http\Controllers\Store\InvoiceController;
use App\Http\Controllers\Store\StoreLoginController;
use App\Http\Controllers\Store\StorePayoutController;
use App\Http\Controllers\Store\PriceController;
use App\Http\Controllers\Store\StProductController;
use App\Http\Controllers\Store\StRequiredController;
use App\Http\Controllers\Store\StSalesreportController;
use App\Http\Controllers\Store\StoreassignHomecateController;
use App\Http\Controllers\Store\StorebannerController;
use App\Http\Controllers\Store\StorehomecateController;
use App\Http\Controllers\Store\StoreordersController;
use App\Http\Controllers\Store\StoreProductController;
use App\Http\Controllers\Store\StoreregController;
use App\Http\Controllers\Store\StoreTimeslotController;
use App\Http\Controllers\Store\StoreVarientController;
use App\Http\Controllers\Store\UsrnotificationController;
use App\Http\Controllers\Store\OrderController;
use App\Http\Controllers\Store\SecondaryController;
use App\Http\Controllers\Store\AreaController;
use App\Http\Controllers\RazorpayController;


use App\Http\Controllers\Installer\InstallController;

use App\Http\Controllers\CityAdmin\AreaController as CityAreaController;
use App\Http\Controllers\CityAdmin\CityAdLoginController;
use App\Http\Controllers\CityAdmin\CityAdHomeController;
use App\Http\Controllers\CityAdmin\CityAdNotifyController;
use App\Http\Controllers\CityAdmin\CityAdDBoyController;
use App\Http\Controllers\CityAdmin\CityAdStoreController;
use App\Http\Controllers\PayGlocalController;
use App\Http\Controllers\Frontend\Auth\AuthController as CustomerAuthController;
use App\Http\Controllers\Frontend\Order\OrderController as CustomerOrderContoller;
use App\Http\Controllers\Frontend\Product\ProductController as CustomerProductController;
use App\Http\Controllers\Admin\BulkBuy\BulkBuyController as AdminBulkBuyController;
use App\Http\Controllers\Admin\ContactUs\ContactUsController as AdminContactUsController;
use App\Http\Controllers\Frontend\Profile\ProfileController as CustomerProfileController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\TestShippingController;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\RatingReviewController;
use App\Http\Controllers\ShiprocketWebhookController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;

use App\Models\Orders;
use App\Mail\ConfirmOrderWithShipment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Artisan;


Route::get('/run-command', function () {
    // Password protection
    if (request('password') !== env('ADMIN_PANEL_PASSWORD', 'your-secret-password-123')) {
        abort(403, 'Access Denied');
    }

    $action = request('action');
    $password = request('password');

    // VIEW LOGS
    if ($action === 'logs') {
        $logFile = storage_path('logs/laravel.log');
        $lines = request('lines', 500);
        $search = request('search');

        if (!file_exists($logFile)) {
            $logs = ['Log file not found!'];
        } else {
            $logs = file($logFile);
            $logs = array_slice($logs, -$lines);
            $logs = array_reverse($logs);

            if ($search) {
                $logs = array_filter($logs, function ($log) use ($search) {
                    return stripos($log, $search) !== false;
                });
            }
        }

?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Laravel Logs</title>
            <style>
                body {
                    margin: 0;
                    font-family: monospace;
                    background: #1e1e1e;
                    color: #d4d4d4;
                }

                .header {
                    background: #2d2d2d;
                    padding: 15px;
                    border-bottom: 2px solid #444;
                    position: sticky;
                    top: 0;
                }

                .header input,
                .header select {
                    padding: 8px;
                    margin: 0 5px;
                    background: #1e1e1e;
                    border: 1px solid #444;
                    color: #fff;
                }

                .header button,
                .header a {
                    padding: 8px 15px;
                    background: #007acc;
                    color: white;
                    border: none;
                    cursor: pointer;
                    text-decoration: none;
                    display: inline-block;
                    border-radius: 4px;
                }

                .header button:hover,
                .header a:hover {
                    background: #005a9e;
                }

                pre {
                    margin: 0;
                    padding: 20px;
                    overflow: auto;
                    height: calc(100vh - 80px);
                }

                .error {
                    color: #f44336;
                }

                .warning {
                    color: #ff9800;
                }

                .info {
                    color: #2196f3;
                }
            </style>
        </head>

        <body>
            <div class="header">
                <form method="GET" style="display: inline;">
                    <input type="hidden" name="password" value="<?= $password ?>">
                    <input type="hidden" name="action" value="logs">
                    <input type="text" name="search" placeholder="Search logs..." value="<?= htmlspecialchars($search ?? '') ?>">
                    <select name="lines">
                        <option value="200" <?= $lines == 200 ? 'selected' : '' ?>>200 lines</option>
                        <option value="500" <?= $lines == 500 ? 'selected' : '' ?>>500 lines</option>
                        <option value="1000" <?= $lines == 1000 ? 'selected' : '' ?>>1000 lines</option>
                        <option value="2000" <?= $lines == 2000 ? 'selected' : '' ?>>2000 lines</option>
                    </select>
                    <button type="submit">Filter</button>
                </form>
                <button onclick="location.reload()">Refresh</button>
                <a href="?password=<?= $password ?>">← Dashboard</a>
            </div>
            <pre><?php
                    foreach ($logs as $log) {
                        $class = '';
                        if (stripos($log, 'error') !== false || stripos($log, 'ERROR') !== false) $class = 'error';
                        elseif (stripos($log, 'warning') !== false || stripos($log, 'WARNING') !== false) $class = 'warning';
                        elseif (stripos($log, 'info') !== false || stripos($log, 'INFO') !== false) $class = 'info';

                        echo "<span class='$class'>" . htmlspecialchars($log) . "</span>";
                    }
                    ?></pre>
        </body>

        </html>
    <?php
        return;
    }

    // RUN COMMAND
    if ($action === 'run') {
        $cmd = request('cmd');
        $customCmd = request('custom_cmd');

        // Use custom command if provided
        if ($customCmd) {
            $cmd = $customCmd;
        }

        if (!$cmd) {
            echo "<h2 style='color: red;'>❌ No command provided!</h2>";
            echo "<a href='?password={$password}' style='padding: 10px 20px; background: #007acc; color: white; text-decoration: none; border-radius: 4px;'>← Back</a>";
            return;
        }

    ?>
        <!DOCTYPE html>
        <html>

        <head>
            <title>Command Output</title>
            <style>
                body {
                    font-family: monospace;
                    padding: 20px;
                    background: #1e1e1e;
                    color: #d4d4d4;
                }

                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                }

                .status {
                    font-size: 24px;
                    margin-bottom: 10px;
                }

                .success {
                    color: #4caf50;
                }

                .error {
                    color: #f44336;
                }

                .command {
                    background: #2d2d2d;
                    padding: 15px;
                    border-radius: 4px;
                    margin-bottom: 20px;
                    border-left: 4px solid #007acc;
                }

                .output {
                    background: #0d0d0d;
                    padding: 20px;
                    border-radius: 4px;
                    white-space: pre-wrap;
                    max-height: 600px;
                    overflow: auto;
                }

                .back {
                    display: inline-block;
                    margin-top: 20px;
                    padding: 10px 20px;
                    background: #007acc;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                }

                .back:hover {
                    background: #005a9e;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <?php
                try {
                    $startTime = microtime(true);
                    Artisan::call($cmd);
                    $output = Artisan::output();
                    $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                    $status = "✅ Success";
                    $statusClass = "success";
                } catch (\Exception $e) {
                    $output = $e->getMessage();
                    $executionTime = 0;
                    $status = "❌ Failed";
                    $statusClass = "error";
                }
                ?>

                <div class="status <?= $statusClass ?>"><?= $status ?></div>
                <div class="command">
                    <strong>Command:</strong> php artisan <?= htmlspecialchars($cmd) ?><br>
                    <strong>Execution Time:</strong> <?= $executionTime ?> ms
                </div>
                <div class="output"><?= htmlspecialchars($output) ?: 'No output' ?></div>
                <a href="?password=<?= $password ?>" class="back">← Back to Dashboard</a>
            </div>
        </body>

        </html>
    <?php
        return;
    }

    // MAIN DASHBOARD
    ?>
    <!DOCTYPE html>
    <html>

    <head>
        <title>Laravel Admin Panel</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                min-height: 100vh;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
            }

            h1 {
                color: white;
                margin-bottom: 30px;
                text-align: center;
                font-size: 32px;
                text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 20px;
            }

            .card {
                background: white;
                padding: 25px;
                border-radius: 12px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            }

            .card h2 {
                color: #333;
                margin-bottom: 15px;
                font-size: 20px;
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .btn {
                display: inline-block;
                padding: 10px 18px;
                margin: 6px 4px;
                background: #007acc;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 14px;
                transition: all 0.3s;
                border: none;
                cursor: pointer;
            }

            .btn:hover {
                background: #005a9e;
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .btn-success {
                background: #4caf50;
            }

            .btn-success:hover {
                background: #388e3c;
            }

            .btn-danger {
                background: #f44336;
            }

            .btn-danger:hover {
                background: #d32f2f;
            }

            .btn-warning {
                background: #ff9800;
            }

            .btn-warning:hover {
                background: #f57c00;
            }

            .custom-cmd {
                margin-top: 15px;
                padding-top: 15px;
                border-top: 2px solid #eee;
            }

            .custom-cmd input {
                width: 100%;
                padding: 12px;
                border: 2px solid #ddd;
                border-radius: 6px;
                font-size: 14px;
                font-family: monospace;
            }

            .custom-cmd input:focus {
                outline: none;
                border-color: #007acc;
            }

            .custom-cmd button {
                margin-top: 10px;
                width: 100%;
            }

            .info-box {
                background: #e3f2fd;
                padding: 12px;
                border-radius: 6px;
                margin-bottom: 15px;
                color: #1976d2;
                font-size: 13px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>🛠️ Laravel Admin Dashboard</h1>

            <div class="grid">
                <!-- LOGS SECTION -->
                <div class="card">
                    <h2>📋 Application Logs</h2>
                    <div class="info-box">View and search through Laravel logs in real-time</div>
                    <a href="?password=<?= $password ?>&action=logs" class="btn btn-success">📖 View Logs</a>
                    <a href="?password=<?= $password ?>&action=logs&search=error" class="btn btn-danger">🔴 View Errors Only</a>
                </div>

                <!-- CACHE COMMANDS -->
                <div class="card">
                    <h2>🗑️ Clear Caches</h2>
                    <div class="info-box">Clear various Laravel caches and optimization files</div>
                    <a href="?password=<?= $password ?>&action=run&cmd=cache:clear" class="btn">Cache Clear</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=config:clear" class="btn">Config Clear</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=route:clear" class="btn">Route Clear</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=view:clear" class="btn">View Clear</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=optimize:clear" class="btn btn-warning">Clear All</a>
                </div>

                <!-- OPTIMIZATION -->
                <div class="card">
                    <h2>⚡ Optimization</h2>
                    <div class="info-box">Cache configurations and routes for better performance</div>
                    <a href="?password=<?= $password ?>&action=run&cmd=config:cache" class="btn btn-success">Config Cache</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=route:cache" class="btn btn-success">Route Cache</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=view:cache" class="btn btn-success">View Cache</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=optimize" class="btn btn-success">Optimize All</a>
                </div>

                <!-- DATABASE -->
                <div class="card">
                    <h2>💾 Database</h2>
                    <div class="info-box">Run migrations and database operations</div>
                    <a href="?password=<?= $password ?>&action=run&cmd=migrate:status" class="btn">Migration Status</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=migrate" class="btn btn-warning">Run Migrations</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=db:seed" class="btn btn-warning">Run Seeders</a>
                </div>

                <!-- QUEUE -->
                <div class="card">
                    <h2>📬 Queue</h2>
                    <div class="info-box">Manage Laravel queues and jobs</div>
                    <a href="?password=<?= $password ?>&action=run&cmd=queue:work --stop-when-empty" class="btn">Process Queue</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=queue:restart" class="btn">Restart Workers</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=queue:failed" class="btn">Failed Jobs</a>
                </div>

                <!-- SYSTEM INFO -->
                <div class="card">
                    <h2>ℹ️ System Info</h2>
                    <div class="info-box">View system and application information</div>
                    <a href="?password=<?= $password ?>&action=run&cmd=about" class="btn">Laravel About</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=route:list" class="btn">Route List</a>
                    <a href="?password=<?= $password ?>&action=run&cmd=env" class="btn">Environment</a>
                </div>
            </div>

            <!-- CUSTOM COMMAND SECTION -->
            <div class="card" style="margin-top: 20px;">
                <h2>⌨️ Run Custom Artisan Command</h2>
                <div class="info-box">
                    <strong>⚠️ Warning:</strong> Be careful when running custom commands. Enter command without "php artisan" prefix.
                </div>
                <div class="custom-cmd">
                    <form method="GET">
                        <input type="hidden" name="password" value="<?= $password ?>">
                        <input type="hidden" name="action" value="run">
                        <input
                            type="text"
                            name="custom_cmd"
                            placeholder="Example: make:controller MyController or cache:clear --tags=posts"
                            required>
                        <button type="submit" class="btn btn-success">▶️ Execute Command</button>
                    </form>
                </div>

                <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
                    <h3 style="margin-bottom: 10px; color: #666; font-size: 16px;">💡 Common Commands:</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <a href="?password=<?= $password ?>&action=run&cmd=make:controller UserController" class="btn" style="font-size: 12px;">make:controller</a>
                        <a href="?password=<?= $password ?>&action=run&cmd=make:model Post -m" class="btn" style="font-size: 12px;">make:model -m</a>
                        <a href="?password=<?= $password ?>&action=run&cmd=make:migration create_posts_table" class="btn" style="font-size: 12px;">make:migration</a>
                        <a href="?password=<?= $password ?>&action=run&cmd=storage:link" class="btn" style="font-size: 12px;">storage:link</a>
                        <a href="?password=<?= $password ?>&action=run&cmd=key:generate" class="btn btn-warning" style="font-size: 12px;">key:generate</a>
                    </div>
                </div>
            </div>
        </div>
    </body>

    </html>
<?php
});

Route::get('/clear-all', function () {
    Artisan::call('cache:clear');
    Artisan::call('view:clear');
    // Note: config:cache and route:cache should be run via CLI only
    // Run: php artisan config:cache && php artisan route:cache
    return "Application and view cache cleared. Run 'php artisan config:cache && php artisan route:cache' via CLI to rebuild config.";
});


Route::get('/debug-mail', function () {
    $order = Orders::where('order_id', 36)->first(); // or factory()
    //  new SendOrderPlaced($order,0);
    return new ConfirmOrderWithShipment($order, "");
});


Route::get('/view-logs', function () {
    // Simple password protection
    if (request('password') !== '963') {
        return "Access Denied! Use: /view-logs?password=your-secret-password-123";
    }

    $logFile = storage_path('logs/laravel.log');

    if (!file_exists($logFile)) {
        return "Log file not found!";
    }

    $logs = file($logFile);
    $logs = array_slice($logs, -500);
    $logs = array_reverse($logs);

    echo "<pre style='background: #1e1e1e; color: #d4d4d4; padding: 20px; overflow: auto; height: 90vh;'>";
    foreach ($logs as $log) {
        echo htmlspecialchars($log);
    }
    echo "</pre>";
});

Route::get('/debug-invoice', function () {

    $logoBase64 = null;
    $logoMime   = null;

    $logoPath = public_path('images/logo.png');

    if (file_exists($logoPath)) {
        $logoMime   = mime_content_type($logoPath);
        $logoBase64 = base64_encode(file_get_contents($logoPath));
    }

    $order = Orders::with([
        'address',
        'orderItems.variation.product.tax',
        'orderItems.variation.variation_attributes.attribute.attribute',
    ])->orderBy('order_id', 'desc')->first();

    // dd($order);
    // 🔴 IMPORTANT: render the SAME view used in PDF
    return view('frontend.order.invoice', [
        'order'      => $order,
        'logoBase64' => $logoBase64,
        'logoMime'   => $logoMime,
    ]);
});

// PayGlocal routes
Route::prefix('payglocal')->group(function () {
    Route::post('/initiate', [PayGlocalController::class, 'initiatePayment'])->name('payglocal.initiate');
    Route::match(['get', 'post'], '/callback', [PayGlocalController::class, 'handleCallback'])->name('payglocal.callback');
    Route::get('/status/{identifier}', [PayGlocalController::class, 'checkStatus'])->name('payglocal.status');
    Route::post('/refund', [PayGlocalController::class, 'processRefund'])->name('payglocal.refund');
});

// Route::get('/payglocal/pay/{order}', [PayGlocalController::class, 'pay'])->name('payglocal.pay');

Route::get('/login', [CustomerAuthController::class, 'index'])->name('login.index');
// Route::post('/login', [CustomerAuthController::class, 'login'])->name('login');


// Route::get('/test-shipping', [TestShippingController::class, 'testShipping']);

// Route::prefix('shipping')->group(function () {
//     // Create shipping order
//     Route::post('/orders', [ShippingController::class, 'createShippingOrder']);
//     // Track shipment
//     Route::get('/track/{waybill}', [ShippingController::class, 'trackShipment']);
//     // Cancel shipment
//     Route::delete('/cancel/{waybill}', [ShippingController::class, 'cancelShipment']);
// });



// Payment result pages
Route::get('/payment/success/{gid}', function ($gid) {
    return view('payment.success', compact('gid'));
})->name('payment.success');

Route::get('/payment/failed/{gid}', function ($gid) {
    return view('payment.failed', compact('gid'));
})->name('payment.failed');

Route::get('/payment/pending/{gid}', function ($gid) {
    return view('payment.pending', compact('gid'));
})->name('payment.pending');

Route::get('/payment/error', function () {
    return view('payment.error');
})->name('payment.error');

Route::get('/payment/customer-cancelled', function () {
    return view('payment.customer-cancelled');
})->name('payment.customer-cancelled');

Route::post('/payment/cancel-payment', [RazorpayController::class, 'handlePaymentCancel'])->name('customer.payment.cancel');



Route::prefix('payment')->group(function () {

    // Show checkout page
    Route::get('/checkout/{cart_id}', [RazorpayController::class, 'show'])->name('payment.checkout');

    // Initiate Razorpay payment (AJAX / API)
    Route::post('/initiate', [RazorpayController::class, 'initiatePayment'])->name('payment.initiate');

    // Handle Razorpay callback
    Route::post('/callback', [RazorpayController::class, 'handleCallback'])->name('payment.callback');

    // Payment failed / error routes
    // Route::get('/failed', function (Illuminate\Http\Request $request) {
    //     $cartId = $request->query('cart_id');
    //     $toastError = session('error') ?? 'Something went wrong while processing payment.';

    //     return redirect()->route('checkout.index', ['cart_id' => $cartId])
    //         ->with('error', $toastError);
    // })->name('payment.failed');

    // Route::get('/error', function (Illuminate\Http\Request $request) {
    //     $cartId = $request->query('cart_id');
    //     $toastError = session('error') ?? 'Something went wrong while processing payment.';

    //     return redirect()->route('checkout.index', ['cart_id' => $cartId])
    //         ->with('error', $toastError);
    // })->name('payment.error');
});


Route::get('/payment/{cart_id}', [RazorpayController::class, 'show'])->name('payment');

Route::any('/webhook/tracking', [ShiprocketWebhookController::class, 'handleShiprocketWebhook'])
    ->name('shiprocket.webhook');
Route::post('/razorpay/webhook', [RazorpayController::class, 'handleWebhook']);
Route::get('/order-success/{orderId}', [CustomerOrderContoller::class, 'order_success'])->name('customer.orders.order_success');

Route::get('verifyLicense', [InstallController::class, 'verifyLicense'])->name('verifyLicense');

Route::group(['middleware' => ['verifylicense']], function () {
    Route::get('installFinish', [InstallController::class, 'installFinish'])->name('installFinish');

    Route::get('verification', [InstallController::class, 'requirement']);

    Route::get('requirement', [InstallController::class, 'requirement']);

    Route::get('verify', [InstallController::class, 'verify'])->name('verify');
    Route::post('verifyPost', [InstallController::class, 'verifyPost'])->name('verifyPost');

    Route::get('databaseinst', [InstallController::class, 'databaseinst'])->name('databaseinst');

    Route::post('databasePost', [InstallController::class, 'databasePost'])->name('databasePost');

    Route::post('databaseVerifyPost', function () {
        Artisan::call('config:cache');
        Artisan::call('config:clear');

        return app(\App\Http\Controllers\Installer\InstallController::class)->databaseVerifyPost();
    });

    Route::namespace("Admin")->prefix('admin')->group(function () {
        Route::view('/powergrid', 'powergrid-demo');
        Route::get('/', [LoginController::class, 'adminLogin'])->name('adminLogin');
        Route::get('/login', [LoginController::class, 'adminLogin'])->name('login');
        Route::post('loginCheck', [LoginController::class, 'adminLoginCheck'])->name('adminLoginCheck');
        Route::get('reset_pass', [AuthController::class, 'reset_pass'])->name('reset_pass');
        Route::post('reset_password_without_token', [AuthController::class, 'validatePasswordRequest'])->name('reset_password_without_token');

        Route::get('change_pass2/{token}', [AuthController::class, 'change_pass2'])->name('change_pass2');
        Route::post('forgot_passwordadmin/{token}', [AuthController::class, 'forgot_passwordadmin'])->name('forgot_passwordadmin');
        Route::get('adminlogout', [LoginController::class, 'logout'])->name('adminlogout');


        Route::group(['middleware' => 'auth:admin'], function () {


            Route::post('/admin/orders/{order}/fetch-delivery-rates', [AdminorderController::class, 'fetchDeliveryRates'])
                ->name('admin.orders.fetch-delivery-rates');
            Route::get('/shippings', [App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('shippings.index');
            Route::get('/shippings/create', [App\Http\Controllers\Admin\ShippingController::class, 'create'])->name('shippings.create');
            Route::post('/shippings/store', [App\Http\Controllers\Admin\ShippingController::class, 'store'])->name('shippings.store');
            Route::get('/shippings/{id}/edit', [App\Http\Controllers\Admin\ShippingController::class, 'edit'])->name('shippings.edit');
            Route::post('/shippings/{id}/update', [App\Http\Controllers\Admin\ShippingController::class, 'update'])->name('shippings.update');
            Route::delete('/shippings/{id}', [App\Http\Controllers\Admin\ShippingController::class, 'destroy'])->name('shippings.destroy');

            Route::get('report/tax', [TaxreportController::class, 'taxreport'])->name('taxreport');
            Route::post('report/taxdatewise', [TaxreportController::class, 'taxdatewise'])->name('taxdatewise');
            Route::get('logout', [LoginController::class, 'logout'])->name('logout');
            Route::get('home', [HomeController::class, 'adminHome'])->name('adminHome');
            Route::get('profile', [ProfileController::class, 'adminProfile'])->name('prof');
            Route::post('profile/update/{id}', [ProfileController::class, 'adminUpdateProfile'])->name('updateprof');
            Route::get('password/change', [ProfileController::class, 'adminChangePass'])->name('passchange');
            Route::post('password/update/{id}', [ProfileController::class, 'adminChangePassword'])->name('updatepass');

            /////settings/////
            Route::get('global_settings', [SettingsController::class, 'app_details'])->name('app_details');
            Route::post('app_details/update', [SettingsController::class, 'updateappdetails'])->name('updateappdetails');

            //Enquiry Routes
            Route::get('/enquiry', [AdminBulkBuyController::class, 'index'])->name('enquiry');
            Route::get('/enquiry/{uuid}/delete', [AdminBulkBuyController::class, 'delete'])->name('enquiry.delete');

            //Enquiry Routes
            Route::get('/contact-us', [AdminContactUsController::class, 'index'])->name('contact_us');
            Route::get('/contact-us/export', [AdminContactUsController::class, 'export'])->name('contact_us.export');
            Route::get('/contact-us/{uuid}/delete', [AdminContactUsController::class, 'delete'])->name('contact_us.delete');

            //Video Routes
            Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
            Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
            Route::post('/videos/store', [VideoController::class, 'store'])->name('videos.store');
            Route::get('/videos/edit/{uuid}', [VideoController::class, 'edit'])->name('videos.edit');
            Route::post('/videos/update/{uuid}', [VideoController::class, 'update'])->name('videos.update');
            Route::get('/videos/delete/{uuid}', [VideoController::class, 'delete'])->name('videos.delete');

            //Banner Route
            Route::get('/banners', [BannerController::class, 'index'])->name('banners');
            Route::get('/banners/create', [BannerController::class, 'create'])->name('banners.create');
            Route::post('/banners/store', [BannerController::class, 'store'])->name('banners.store');
            Route::get('/banners/{uuid}/edit', [BannerController::class, 'edit'])->name('banners.edit');
            Route::post('/banners/{uuid}/update', [BannerController::class, 'update'])->name('banners.update');
            Route::get('/banners/{uuid}/delete', [BannerController::class, 'delete'])->name('banners.delete');

            Route::get('msgby', [SettingsController::class, 'msg91'])->name('msg91');
            Route::post('msg91/update', [SettingsController::class, 'updatemsg91'])->name('updatemsg91');
            Route::post('twilio/update', [TwilioController::class, 'updatetwilio'])->name('updatetwilio');
            Route::post('msgoff', [TwilioController::class, 'msgoff'])->name('msgoff');

            Route::get('map_api', [MapController::class, 'mapsettings'])->name('mapapi');
            Route::post('map_api/update', [MapController::class, 'updategooglemap'])->name('updatemap');
            Route::post('mapbox/update', [MapController::class, 'updatemapbox'])->name('updatemapbox');

            Route::get('app_settings', [SettingsController::class, 'fcm'])->name('app_settings');
            Route::post('app_settings/update', [SettingsController::class, 'updatefcm'])->name('updatefcm');
            Route::post('updatheme', [SettingsController::class, 'custom_theme'])->name('custom_theme');

            //Update Order Status Routes
            Route::post('/change/order/status/confirmed/{id}', [AdminorderController::class, 'changeOrderStatusConfirmed'])->name('changeOrderStatusConfirmed');
            Route::get('/change/order/status/{id}', [AdminorderController::class, 'changeOrderStatusCompleted'])->name('changeOrderStatusCompleted');
            Route::get('/change/order/status/cancelled/{id}', [AdminorderController::class, 'changeOrderStatusCancelled'])->name('changeOrderStatusCancelled');

            // AJAX Order Status Routes (No Redirect)
            Route::post('/ajax/order/complete/{id}', [AdminorderController::class, 'ajaxCompleteOrder'])->name('ajax.order.complete');
            Route::post('/ajax/order/cancel/{id}', [AdminorderController::class, 'ajaxCancelOrder'])->name('ajax.order.cancel');
            Route::post('/ajax/order/mark-viewed/{id}', [AdminorderController::class, 'ajaxMarkViewed'])->name('ajax.order.markViewed');
            Route::post('/ajax/order/confirm/{id}', [AdminorderController::class, 'ajaxConfirmOrder'])->name('ajax.order.confirm');
            Route::delete('/ajax/order/delete/{id}', [AdminorderController::class, 'ajaxDeleteOrder'])->name('ajax.order.delete');

            ////notification
            Route::get('notification/to-store', [NotificationController::class, 'adminNotification'])->name('adminNotification');
            Route::post('Notification_to_store/send', [NotificationController::class, 'Notification_to_store_Send'])->name('adminNotificationSendtostore');

            //Attribute Routes
            Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes');
            Route::get('/attributes/create', [AttributeController::class, 'create'])->name('attributes.create');
            Route::post('/attributes/store', [AttributeController::class, 'store'])->name('attributes.store');
            Route::get('/attributes/edit/{uuid}', [AttributeController::class, 'edit'])->name('attributes.edit');
            Route::post('/attributes/update/{uuid}', [AttributeController::class, 'update'])->name('attributes.update');
            Route::get('/attributes/delete/{uuid}', [AttributeController::class, 'delete'])->name('attributes.delete');

            Route::post('currency/update', [SettingsController::class, 'updatecurrency'])->name('updatecurrency');
            ///////category////////
            Route::get('category/list', [CategoryController::class, 'list'])->name('catlist');
            Route::get('category/add', [CategoryController::class, 'AddCategory'])->name('AddCategory');
            Route::post('category/add/new', [CategoryController::class, 'AddNewCategory'])->name('AddNewCategory');
            Route::get('category/edit/{category_id}', [CategoryController::class, 'EditCategory'])->name('EditCategory');
            Route::post('category/update/{category_id}', [CategoryController::class, 'UpdateCategory'])->name('UpdateCategory');
            Route::get('category/delete/{category_id}', [CategoryController::class, 'DeleteCategory'])->name('DeleteCategory');
            Route::post('change-postion', [CategoryController::class, 'changePostion'])->name('change-postion');

            Route::get('cityadminers/list', [CityAdminController::class, 'cityAdminList'])->name('CityAdminList');
            Route::get('cityadminers/add', [CityAdminController::class, 'cityAdminAdd'])->name('CityAdAdd');
            Route::post('cityadminers/add', [CityAdminController::class, 'cityAdminNew'])->name('CityAdNew');
            Route::get('cityadminers/edit/{id}', [CityAdminController::class, 'cityAdminEdit'])->name('CityAdEdit');
            Route::post('cityadminers/update/{id}', [CityAdminController::class, 'cityAdminUpdate'])->name('CityAdUpdate');
            Route::get('cityadminers/delete/{id}', [CityAdminController::class, 'cityAdminDelete'])->name('CityAdDelete');
            Route::get('cityadminers/secretlogin/{id}', [CityAdminController::class, 'cityAdminSecretLogin'])->name('CityAdSecretLogin');

            ///////sub category////////
            Route::get('/get-subcategories/{categoryId}', [CategoryController::class, 'getSubcategories'])->name('get.subcategories');

            Route::get('sub-category/list', [SubController::class, 'SubCatlist'])->name('subcatlist');
            Route::get('sub-category/export', [SubController::class, 'exportSubCategories'])->name('subcategories.export');
            Route::get('sub-category/add', [SubController::class, 'AddSubCategory'])->name('AddsubCategory');
            Route::get('sub-category/edit/{category_id}', [SubController::class, 'EditSubCategory'])->name('EditsubCategory');

            Route::get('child-category/list', [SubController::class, 'childCatlist'])->name('childcatlist');
            Route::get('child-category/add', [SubController::class, 'AddChildCategory'])->name('AddChildCategory');
            Route::get('child-category/edit/{category_id}', [SubController::class, 'EditChildCategory'])->name('EditChildCategory');



            ///////Product////////
            Route::get('product/generate-sku', [ProductController::class, 'getAutoSKU'])->name('generate.sku');
            Route::get('product/list', [ProductController::class, 'list'])->name('productlist');
            Route::get('product/export', [ProductController::class, 'exportProducts'])->name('products.export');
            Route::get('product/add', [ProductController::class, 'AddProduct'])->name('AddProduct');
            Route::post('product/add/new', [ProductController::class, 'AddNewProduct'])->name('AddNewProduct');
            Route::get('product/edit/{uuid}', [ProductController::class, 'EditProduct'])->name('EditProduct');
            Route::post('product/update/{uuid}', [ProductController::class, 'UpdateProduct'])->name('UpdateProduct');
            Route::get('product/delete/{uuid}', [ProductController::class, 'DeleteProduct'])->name('DeleteProduct');


            //////Product Varient//////////
            Route::get('varient/{id}', [VarientController::class, 'varient'])->name('varient');
            Route::get('varient/add/{id}', [VarientController::class, 'Addproduct'])->name('add-varient');
            Route::post('varient/add/new', [VarientController::class, 'AddNewproduct'])->name('AddNewvarient');
            Route::get('varient/edit/{id}', [VarientController::class, 'Editproduct'])->name('edit-varient');
            Route::post('varient/update/{id}', [VarientController::class, 'Updateproduct'])->name('update-varient');
            Route::get('varient/delete/{id}', [VarientController::class, 'deleteproduct'])->name('delete-varient');

            ///////Delivery Boy////////
            Route::get('d_boy/list', [DeliveryController::class, 'list'])->name('d_boylist');
            Route::get('d_boy/add', [DeliveryController::class, 'AddD_boy'])->name('AddD_boy');
            Route::post('d_boy/add/new', [DeliveryController::class, 'AddNewD_boy'])->name('AddNewD_boy');
            Route::get('d_boy/edit/{id}', [DeliveryController::class, 'EditD_boy'])->name('EditD_boy');
            Route::post('d_boy/update/{id}', [DeliveryController::class, 'UpdateD_boy'])->name('UpdateD_boy');
            Route::get('d_boy/delete/{id}', [DeliveryController::class, 'DeleteD_boy'])->name('DeleteD_boy');

            ///////User////////
            Route::get('user/list', [UserController::class, 'list'])->name('userlist');
            Route::get('user/export', [UserController::class, 'exportUsers'])->name('users.export');
            Route::get('user/block/{id}', [UserController::class, 'block'])->name('userblock');
            Route::match(['get', 'post'], 'user/unblock/{id}', [UserController::class, 'unblock'])->name('userunblock');
            // for city
            Route::get('city/list', [CityController::class, 'citylist'])->name('citylist');
            Route::get('city/add', [CityController::class, 'city'])->name('city');
            Route::post('city/add/new', [CityController::class, 'cityadd'])->name('cityadd');
            Route::get('city/edit/{city_id}', [CityController::class, 'cityedit'])->name('cityedit');
            Route::post('city/update', [CityController::class, 'cityupdate'])->name('cityupdate');
            Route::get('city/delete/{city_id}', [CityController::class, 'citydelete'])->name('citydelete');
            // for society
            Route::get('society/list', [SocietyController::class, 'societylist'])->name('societylist');
            Route::get('society/add', [SocietyController::class, 'society'])->name('society');
            Route::post('society/add/new', [SocietyController::class, 'societyadd'])->name('societyadd');
            Route::get('society/edit/{society_id}', [SocietyController::class, 'societyedit'])->name('societyedit');
            Route::post('society/update', [SocietyController::class, 'societyupdate'])->name('societyupdate');
            Route::get('society/delete/{society_id}', [SocietyController::class, 'societydelete'])->name('societydelete');

            // for delivery time
            /* Route::get('timeslot', [TimeSlotController::class, 'timeslot'])->name('timeslot');
            Route::post('timeslotupdate', [TimeSlotController::class, 'timeslotupdate'])->name('timeslotupdate'); */

            // for store
            Route::get('admin/store/list', [StoreController::class, 'storeclist'])->name('storeclist');
            Route::get('admin/store/add', [StoreController::class, 'store'])->name('store');
            Route::post('admin/store/added', [StoreController::class, 'storeadd'])->name('storeadd');
            Route::get('admin/store/edit/{store_id}', [StoreController::class, 'storedit'])->name('storedit');
            Route::post('admin/store/update/{store_id}', [StoreController::class, 'storeupdate'])->name('storeupdate');
            Route::get('admin/store/delete/{store_id}', [StoreController::class, 'storedelete'])->name('storedelete');
            //store orders//
            Route::get('admin/store/orders/{id}', [AdminorderController::class, 'admin_store_orders'])->name('admin_store_orders');
            Route::get('cancelled/orders', [AdminorderController::class, 'store_cancelled'])->name('store_cancelled');
            //assign store//
            Route::post('admin/store/assign/{id}', [AdminorderController::class, 'assignstore'])->name('store_assign');

            Route::get('stores/finance', [FinanceController::class, 'finance'])->name('finance');
            Route::post('store_pay/{store_id}', [FinanceController::class, 'store_pay'])->name('store_pay');


            /////pages////////

            Route::get('about-us', [PagesController::class, 'about_us'])->name('about_us');
            Route::post('about-us/update', [PagesController::class, 'updateabout_us'])->name('updateabout_us');

            Route::get('terms', [PagesController::class, 'terms'])->name('terms');
            Route::post('terms/update', [PagesController::class, 'updateterms'])->name('updateterms');

            Route::get('prv', [SettingsController::class, 'prv'])->name('prv');
            Route::post('prv/update', [SettingsController::class, 'updateprv'])->name('updateprv');

            Route::get('privacy-policy', [PagesController::class, 'privacypolicy'])->name('privacypolicy');
            Route::post('privacy-policy/update', [PagesController::class, 'updateprivacypolicy'])->name('updateprivacypolicy');

            Route::get('return-and-exchange-policy', [PagesController::class, 'returnandexchangepolicy'])->name('returnandexchangepolicy');
            Route::post('return-and-exchange-policy/update', [PagesController::class, 'updatereturnandexchangepolicy'])->name('updatereturnandexchangepolicy');

            Route::get('cancel-and-refund-policy', [PagesController::class, 'cancelandrefundpolicy'])->name('cancelandrefundpolicy');
            Route::post('cancel-and-refund-policy/update', [PagesController::class, 'updatecancelandrefundpolicy'])->name('updatecancelandrefundpolicy');

            Route::get('shipping-and-delivery-policy', [PagesController::class, 'shippinganddeliverypolicy'])->name('shippinganddeliverypolicy');
            Route::post('shipping-and-delivery-policy/update', [PagesController::class, 'updateshippinganddeliverypolicy'])->name('updateshippinganddeliverypolicy');

            // for reward
            Route::get('reward', [RewardController::class, 'RewardList'])->name('RewardList');
            Route::get('reward/add', [RewardController::class, 'reward'])->name('reward');
            Route::post('reward/add/new', [RewardController::class, 'rewardadd'])->name('rewardadd');
            Route::get('reward/edit/{reward_id}', [RewardController::class, 'rewardedit'])->name('rewardedit');
            Route::post('reward/update', [RewardController::class, 'rewardupate'])->name('rewardupate');
            Route::get('reward/delete/{reward_id}', [RewardController::class, 'rewarddelete'])->name('rewarddelete');


            Route::get('reviews', [App\Http\Controllers\Admin\RatingReviewController::class, 'index'])->name('reviews.index');
            Route::post('reviews/{id}/approve', [App\Http\Controllers\Admin\RatingReviewController::class, 'approve'])->name('reviews.approve');
            Route::post('reviews/{id}/reject', [App\Http\Controllers\Admin\RatingReviewController::class, 'reject'])->name('reviews.reject');
            Route::delete('reviews/{id}', [App\Http\Controllers\Admin\RatingReviewController::class, 'destroy'])->name('reviews.destroy');

            // for reedem
            Route::get('reedem', [ReedemController::class, 'reedem'])->name('reedem');
            Route::post('reedemupdate', [ReedemController::class, 'reedemupdate'])->name('reedemupdate');

            ////store payout////
            Route::get('payout_req', [PayoutController::class, 'pay_req'])->name('pay_req');
            Route::post('payout_req/{req_id}', [PayoutController::class, 'store_pay'])->name('com_payout');

            // for  Secondary banner
            Route::get('secbannerlist', [SecondaryBannerController::class, 'secbannerlist'])->name('secbannerlist');
            Route::get('secbanner', [SecondaryBannerController::class, 'secbanner'])->name('secbanner');
            Route::post('secbanneradd', [SecondaryBannerController::class, 'secbanneradd'])->name('secbanneradd');
            Route::get('secbanneredit/{sec_banner_id}', [SecondaryBannerController::class, 'secbanneredit'])->name('secbanneredit');
            Route::post('secbannerupdate/{sec_banner_id}', [SecondaryBannerController::class, 'secbannerupdate'])->name('secbannerupdate');
            Route::get('secbannerdelete/{sec_banner_id}', [SecondaryBannerController::class, 'secbannerdelete'])->name('secbannerdelete');

            Route::get('admin/d_boy/orders/{id}', [AdminorderController::class, 'admin_dboy_orders'])->name('admin_dboy_orders');
            //assign delivery boy//
            Route::post('admin/d_boy/assign/{id}', [AdminorderController::class, 'assigndboy'])->name('dboy_assign');
            ////completed orders/////
            Route::get('admin/completed_orders', [AdminorderController::class, 'admin_com_orders'])->name('admin_com_orders');
            Route::get('admin/completed_orders/export', [AdminorderController::class, 'exportCompletedOrders'])->name('completed_orders.export');
            Route::get('admin/pending_orders/export', [AdminorderController::class, 'exportPendingOrders'])->name('pending_orders.export');
            Route::get('admin/cancelled_orders/export', [AdminorderController::class, 'exportCancelledOrders'])->name('cancelled_orders.export');
            Route::get('admin/completed_orders/export', [AdminorderController::class, 'exportCompletedOrders'])->name('completed_orders.export');
            Route::get('admin/ongoing_orders/export', [AdminorderController::class, 'exportOngoingOrders'])->name('ongoing_orders.export');
            Route::get('admin/orders/export', [AdminorderController::class, 'exportOrders'])->name('orders.export');
            Route::get('/orders/day-wise/export', [SalesreportController::class, 'exportDatewiseOrders'])->name('datewise_orders_export');
            Route::get('admin/payment_failed_orders/export', [AdminorderController::class, 'exportFailedOrders'])->name('failed_orders.export');

            Route::get('admin/generate_invoice/{orderId}', [AdminorderController::class, 'generate_invoice'])->name('generate_invoice');
            ////Pending orders/////
            Route::get('admin/pending_orders', [AdminorderController::class, 'admin_pen_orders'])->name('admin_pen_orders');

            Route::post('admin/reject/order/{id}', [AdminorderController::class, 'rejectorder'])->name('admin_reject_order');
            Route::get('admin/cancelled_orders', [AdminorderController::class, 'admin_can_orders'])->name('admin_can_orders');
            Route::get('payment_gateway', [PayController::class, 'payment_gateway'])->name('gateway');
            Route::post('payment_gateway/update', [PayController::class, 'updatepymntvia'])->name('updategateway');


            ////approval waiting list////
            Route::get('stores/waiting_for_approval/stores/list', [ApprovalController::class, 'storeclist'])->name('storeapprove');
            Route::get('approved/stores/{id}', [ApprovalController::class, 'storeapproved'])->name('storeapproved');


            Route::get('user/delete/{id}', [UserController::class, 'del_user'])->name('del_userfromlist');

            Route::get('changeStatus', [HideController::class, 'hideproduct'])->name('hideprod');
            Route::get('app_notice', [NoticeController::class, 'adminnotice'])->name('app_notice');
            Route::post('app_notice/update', [NoticeController::class, 'adminupdatenotice'])->name('app_noticeupdate');
            Route::get('updatefirebase', [HideController::class, 'updatefirebase'])->name('updatefirebase');
            /// for bulk upload

            Route::get('bulk/upload', [ImportExcelController::class, 'bulkup'])->name('bulkup');
            Route::post('bulk/upload', [ImportExcelController::class, 'import'])->name('bulk_upload');
            Route::post('bulk/v_upload', [ImportExcelController::class, 'import_varients'])->name('bulk_v_upload');

            Route::get('orders/user/all/', [SalesreportController::class, 'user_sales'])->name('user_sales');
            Route::get('orders/user/all/export', [SalesreportController::class, 'exportUserSales'])->name('user_sales.export');
            Route::get('report/stock', [SalesreportController::class, 'stock_report'])->name('stock_report');
            Route::get('report/stock/export', [SalesreportController::class, 'exportStockReport'])->name('stock_report.export');
            Route::get('orders/user/day-wise/', [SalesreportController::class, 'user_orders'])->name('user_datewise_orders');
            Route::get('orders/category/all/', [SalesreportController::class, 'category_sales'])->name('category_sales');
            Route::get('orders/category/day-wise/', [SalesreportController::class, 'category_orders'])->name('category_datewise_orders');
            Route::get('orders/today/all/', [SalesreportController::class, 'sales_today'])->name('sales_today');
            // Route::post('orders/day-wise/', [SalesreportController::class, 'orders'])->name('datewise_orders');

            // Or accept both methods
            Route::match(['GET', 'POST'], '/orders/day-wise/', [SalesreportController::class, 'orders'])->name('datewise_orders');
            Route::get('/orders/day-wise/export', [SalesreportController::class, 'exportDatewiseOrders'])->name('datewise_orders_export');
            Route::match(['get', 'post'], 'user/list/day-wise/', [UserController::class, 'daywise'])->name('daywise_reg');
            Route::get('report/item_sale/by_store/', [RequiredController::class, 'storeclist'])->name('item_sale_rep');

            Route::get('required/itemlist/today/store/{id}', [RequiredController::class, 'reqfortoday'])->name('req_items_today');
            Route::post('required/itemlist/store/day-wise/{id}', [RequiredController::class, 'reqdaywise'])->name('datewise_itemsalesreport');

            Route::post('storehide/{id}', [StorehideController::class, 'off'])->name('storehide');
            Route::get('storeunhide/{id}', [StorehideController::class, 'on'])->name('storeunhide');

            Route::post('firebase/iso_code', [SettingsController::class, 'updatefirebase_iso'])->name('updatefirebase_iso');
            Route::post('update/ref', [SettingsController::class, 'updateref'])->name('updateref');


            ///////Store Products////////
            // Route::get('store_products/list', [ProductapproveController::class, 'list'])->name('st_plist');
            // Route::get('store_products/approve/{id}', [ProductapproveController::class, 'approve'])->name('st_p_approve');
            // Route::get('store_products/reject/{id}', [ProductapproveController::class, 'reject'])->name('st_p_reject');
            //////incentive////
            // Route::post('incentive', [SettingsController::class, 'updateincentive'])->name('up_admin_incentive');

            // Route::get('dboy_incentive', [FinanceController::class, 'boy_incentive'])->name('boy_incentive');
            // Route::post('dboy_pay/{dboy_id}', [FinanceController::class, 'incentive_pay'])->name('incentive_pay');
            // Route::get('store/missed/orders', [AdminorderController::class, 'missed_orders'])->name('missed_orders');
            Route::get('status/change/{cart_id}', [OrderstatussController::class, 'change'])->name('changeStatus');
            //assign delivery boy//
            Route::post('d_boy/assign/{id}', [OrderstatussController::class, 'assigndboy'])->name('ad_dboy_assign');
            Route::get('report/total-item-sales/last-30-days', [RequiredController::class, 'ad_reqforthirty'])->name('admin_reqforthirty');
            Route::get('report/order', [DeliveryController::class, 'boy_reports'])->name('ad_boy_reports');

            ////notification to Driver
            // Route::get('notification/to-driver', [NotificationController::class, 'adminNotificationdriver'])->name('adminNotificationdriver');
            // Route::post('notification/to-driver/send', [NotificationController::class, 'Notification_to_driver_Send'])->name('adminNotificationSendtodriver');


            // Route::get('cancelling_reasons/list', [ReasonController::class, 'can_res_list'])->name('can_res_list');
            // Route::get('cancelling_reasons/add', [ReasonController::class, 'can_res_add'])->name('can_res_add');
            // Route::post('cancelling_reasons/added', [ReasonController::class, 'can_res_added'])->name('can_res_added');
            // Route::get('cancelling_reasons/edit/{res_id}', [ReasonController::class, 'can_res_edit'])->name('can_res_edit');
            // Route::post('cancelling_reasons/updated/', [ReasonController::class, 'can_res_edited'])->name('can_res_edited');
            // Route::get('cancelling_reasons/delete/{res_id}', [ReasonController::class, 'can_res_del'])->name('can_res_del');

            // Route::post('gateway_option/change', [PayController::class, 'gateway_status'])->name('gateway_status');

            // Route::get('user_feedback/list', [FeedbackController::class, 'user_feedback'])->name('user_feedback');
            // Route::get('store_feedback/list', [FeedbackController::class, 'store_feedback'])->name('store_feedback');
            // Route::get('driver_feedback/list', [FeedbackController::class, 'driver_feedback'])->name('driver_feedback');

            // Route::get('store_callback_requests', [StorecallbackController::class, 'storecallbacklist'])->name('store_callback_requests');
            // Route::get('store_callbackproc/{id}', [StorecallbackController::class, 'store_call_proc'])->name('store_callbackproc');
            // Route::get('driver_callback_requests', [DrivercallbackController::class, 'drivercallbacklist'])->name('driver_callback_requests');
            // Route::get('driver_callbackproc/{id}', [DrivercallbackController::class, 'driver_call_proc'])->name('driver_callbackproc');
            // Route::get('user_callback_requests', [UsercallbackController::class, 'usercallbacklist'])->name('user_callback_requests');

            // Route::get('user_callbackproc/{id}', [UsercallbackController::class, 'user_call_proc'])->name('user_callbackproc');
            // Route::get('updatereferral', [SettingsController::class, 'updatereferral'])->name('updatereferral_codes');
            // Route::post('app_link', [SettingsController::class, 'app_link'])->name('app_link');
            // Route::get('users/wallet_recharge_history', [UserwalletController::class, 'list'])->name('user_wallet');
            Route::post('usr_recharge/{id}', [UserwalletController::class, 'pay'])->name('usr_recharge');
            // Route::post('updatespace', [SettingsController::class, 'updatespace'])->name('updatespace');

            //////notification to user
            // Route::get('notification/to-users', [NotificationController::class, 'adminNotificationuser'])->name('adminNotificationuser');
            // Route::post('notification/to-users/send', [NotificationController::class, 'userNotificationSend'])->name('userNotificationSend');

            Route::get('user/edit/{id}', [UserController::class, 'ed_user'])->name('ed_user');
            Route::post('user/update/{id}', [UserController::class, 'up_user'])->name('up_user');

            // Route::get('area_bulk_upload/upload/city-society', [ImportExcelController::class, 'bulkupcity'])->name('bulkupcity');
            // Route::post('area_bulk_upload/city', [ImportExcelController::class, 'importcity'])->name('importcity');
            // Route::post('area_bulk_upload/society', [ImportExcelController::class, 'importsociety'])->name('importsociety');
            ///////Member////////
            Route::get('membership/list', [MemberController::class, 'list'])->name('memlist');
            // Route::get('membership/add', [MemberController::class, 'AddMember'])->name('AddMember');
            // Route::post('membership/add/new', [MemberController::class, 'AddNewMember'])->name('AddNewMember');
            // Route::get('membership/edit/{plan_id}', [MemberController::class, 'EditMember'])->name('EditMember');
            // Route::post('membership/update/{plan_id}', [MemberController::class, 'UpdateMember'])->name('UpdateMember');
            // Route::get('membership/delete/{plan_id}', [MemberController::class, 'DeleteMember'])->name('DeleteMember');

            Route::get('user/membership/{id}', [UserController::class, 'mem_list'])->name('mem_list');
            // for city
            Route::get('tax/list', [TaxController::class, 'taxlist'])->name('taxlist');
            Route::get('tax', [TaxController::class, 'tax'])->name('tax');
            Route::post('tax/add', [TaxController::class, 'taxadd'])->name('taxadd');
            Route::get('tax/edit/{tax_id}', [TaxController::class, 'taxedit'])->name('taxedit');
            Route::post('tax/update', [TaxController::class, 'taxupdate'])->name('taxupdate');
            Route::get('tax/delete/{tax_id}', [TaxController::class, 'taxdelete'])->name('taxdelete');


            Route::post('/admin/orders/{order}/ithink-create', [AdminorderController::class, 'createShipment'])
                ->name('ithinklogistics.create');
            // View tracking page for an order (uses order id and waybill from order.shipment.waybill)
            Route::get('/order/{order}/tracking', [AdminorderController::class, 'tracking'])
                ->name('admin.orders.tracking');

            Route::get('/admin/shiprocket/orders', [AdminorderController::class, 'getShiprocketOrders'])
                ->name('shiprocket.orders');

            Route::get('/admin/shiprocket/track/{orderId}', [AdminorderController::class, 'track'])
                ->name('shiprocket.track');

            Route::get('order/confirm/{order_id}', [AdminorderController::class, 'create_shipp_order_confirm'])->name('create_shipp_order_confirm');
            // Route::get('trending_search/product/add', [TrendingController::class, 'sel_product'])->name('trendsel_product');
            // Route::post('trending_search/added', [TrendingController::class, 'added_product'])->name('trendadded_product');
            // Route::get('trending_search/product/delete/{id}', [TrendingController::class, 'delete_product'])->name('trenddelete_product1');
            Route::get('admin/ongoing_orders', [AdminorderController::class, 'admin_on_orders'])->name('admin_on_orders');
            Route::get('admin/all_orders', [AdminorderController::class, 'admin_all_orders'])->name('admin_all_orders');
            Route::get('admin/out_for_delivery_orders', [AdminorderController::class, 'admin_out_orders'])->name('admin_out_orders');
            Route::get('admin/payment_failed_orders', [AdminorderController::class, 'admin_failed_orders'])->name('admin_failed_orders');

            // Blogs
            // Route::get('admin/blogs', [BlogController::class, 'index'])->name('admin.blog');
            Route::get('/blogs',               [AdminBlogController::class, 'index'])->name('admin.blog.index');
            Route::get('/blogs/create',        [AdminBlogController::class, 'create'])->name('admin.blog.create');
            Route::post('/blogs',              [AdminBlogController::class, 'store'])->name('admin.blog.store');
            Route::get('/blogs/{id}/edit',     [AdminBlogController::class, 'edit'])->name('admin.blog.edit');
            Route::put('/blogs/{id}',          [AdminBlogController::class, 'update'])->name('admin.blog.update');
            Route::get('/blogs/{id}/delete',   [AdminBlogController::class, 'destroy'])->name('admin.blog.destroy');

            // Route::get('id/list', [IdController::class, 'idlist'])->name('idlist');
            // Route::get('id', [IdController::class, 'idd'])->name('id');
            // Route::post('id/add', [IdController::class, 'idadd'])->name('idadd');
            // Route::get('id/edit/{type_id}', [IdController::class, 'idedit'])->name('idedit');
            // Route::post('id/update', [IdController::class, 'idupdate'])->name('idupdate');
            // Route::get('id/delete/{type_id}', [IdController::class, 'iddelete'])->name('iddelete');

            // for roles
            // Route::get('roles/add', [SubadController::class, 'add'])->name('roles');
            // Route::get('roles', [SubadController::class, 'sub'])->name('rolelist');
            // Route::post('roles/added', [SubadController::class, 'addnew'])->name('addnewrole');
            // Route::get('roles/edit/{id}', [SubadController::class, 'edit'])->name('roleedit');
            // Route::post('roles/update/{id}', [SubadController::class, 'update'])->name('updaterole');
            // Route::get('roles/delete/{id}', [SubadController::class, 'delete'])->name('deleterole');
            ///////SubAdmin////////
            // Route::get('subadmin/list', [AdminController::class, 'list'])->name('subadminlist');
            // Route::get('subadmin/add', [AdminController::class, 'Add'])->name('AddSubadmin');
            // Route::post('subadmin/add/new', [AdminController::class, 'AddNew'])->name('AddNewSubadmin');
            // Route::get('subadmin/edit/{id}', [AdminController::class, 'Edit'])->name('EditSubadmin');
            // Route::post('subadmin/update/{id}', [AdminController::class, 'Update'])->name('UpdateSubadmin');
            // Route::get('subadmin/delete/{id}', [AdminController::class, 'Delete'])->name('DeleteSubadmin');
            // Route::get('list/notification/list/user', [NotificationController::class, 'usernotlist'])->name('usernotlist');
            // Route::get('list/notification/list/store', [NotificationController::class, 'storenotlist'])->name('storenotlist');
            // Route::get('list/notification/list/driver', [NotificationController::class, 'drivernotlist'])->name('drivernotlist');
            // Route::get('notification/list/user/delete_all', [NotificationController::class, 'delete_all_user'])->name('delete_all_user_not');
            // Route::get('notification/list/user/delete_read', [NotificationController::class, 'delete_read_user'])->name('delete_read_user_not');
            // Route::get('notification/list/store/delete_all', [NotificationController::class, 'delete_all_store'])->name('delete_all_store_not');
            // Route::get('notification/list/driver/delete_all', [NotificationController::class, 'delete_all_driver'])->name('delete_all_driver_not');
            // Route::get('set_delivery_boy_incentive', [SettingsController::class, 'driverinc'])->name('driverinc');
            Route::get('add2home/{id}', [SubController::class, 'add2home'])->name('add2home');
            // Route::get('delfromhome/{id}', [SubController::class, 'delfromhome'])->name('delfromhome');

            Route::get('coupon/list', [AdminCouponController::class, 'couponlist'])->name('couponlist');
            Route::get('coupon/add', [AdminCouponController::class, 'coupon'])->name('coupon');
            Route::post('coupon/add/new', [AdminCouponController::class, 'addcoupon'])->name('addcoupon');
            Route::get('coupon/edit/{coupon_id}', [AdminCouponController::class, 'editcoupon'])->name('editcoupon');
            Route::post('coupon/update', [AdminCouponController::class, 'updatecoupon'])->name('updatecoupon');
            Route::get('coupon/delete/{coupon_id}', [AdminCouponController::class, 'deletecoupon'])->name('deletecoupon');
            Route::post(
                '/admin/coupon/toggle-visibility',
                [AdminCouponController::class, 'toggleVisibility']
            )->name('coupon.toggle.visibility');


            // Route::get('coupon/list', [CouponController::class, 'couponlist'])->name('couponlist');
            // Route::get('coupon/add', [CouponController::class, 'coupon'])->name('coupon');
            // Route::post('coupon/add/new', [CouponController::class, 'addcoupon'])->name('addcoupon');
            // Route::get('coupon/edit/{coupon_id}', [CouponController::class, 'editcoupon'])->name('editcoupon');
            // Route::post('coupon/update', [CouponController::class, 'updatecoupon'])->name('updatecoupon');
            // Route::get('coupon/delete/{coupon_id}', [CouponController::class, 'deletecoupon'])->name('deletecoupon');

        });
    });

    Route::get('lang/change', [LanguageController::class, 'change'])->name('changeLang');


    // Route::namespace("Store")->prefix('store')->group(function () {

    // for login
    // Route::get('/', [StoreLoginController::class, 'storeLogin'])->name('storeLogin');
    // Route::get('secret-store-login/{id}', [StoreLoginController::class, 'secretStoreLogin'])->name('secret-store-login');
    // Route::get('secret-store-login1', [StoreLoginController::class, 'secretStoreLogin1'])->name('secret-store-login1');
    // Route::get('store_register/', [StoreregController::class, 'register_store'])->name('store_register');
    // Route::post('store_registered/', [StoreregController::class, 'store_registered'])->name('store_registered');
    // Route::post('loginCheck', [StoreLoginController::class, 'storeLoginCheck'])->name('storeLoginCheck');

    // Route::group(['middleware' => 'auth:store'], function () {
    //     Route::get('logout', [StoreLoginController::class, 'logout'])->name('storelogout');
    //     Route::get('home', [StoreHomeController::class, 'storeHome'])->name('storeHome');
    //     Route::get('product/add', [StProductController::class, 'sel_product'])->name('sel_product');
    //     Route::post('product/added', [StProductController::class, 'added_product'])->name('added_product');
    //     Route::get('product/delete/{id}', [StProductController::class, 'delete_product'])->name('delete_product');
    //     Route::post('product/stock/{id}', [StProductController::class, 'stock_update'])->name('stock_update');
    //     Route::get('logout/', [StoreLoginController::class, 'logout'])->name('storelogout');
    //     Route::get('orders/next_day', [AssignorderController::class, 'orders'])->name('storeOrders');
    //     Route::get('orders/today', [AssignorderController::class, 'assignedorders'])->name('storeassignedorders');
    //     Route::post('orders/confirm/{cart_id}', [AssignorderController::class, 'confirm_order'])->name('store_confirm_order');
    //     Route::get('orders/reject/{cart_id}', [OrderController::class, 'reject_order'])->name('store_reject_order');
    //     Route::get('orders/products/cancel/{store_order_id}', [OrderController::class, 'cancel_products'])->name('store_cancel_product');

    //     Route::get('update/stock', [StProductController::class, 'st_product'])->name('st_product');
    //     Route::get('payout/request', [StorePayoutController::class, 'payout_req'])->name('payout_req');
    //     Route::post('payout/request/sent', [StorePayoutController::class, 'req_sent'])->name('payout_req_sent');

    //     Route::get('store/invoice/{cart_id}', [InvoiceController::class, 'invoice'])->name('invoice');
    //     Route::get('store/pdf/invoice/{cart_id}', [InvoiceController::class, 'pdfinvoice'])->name('pdfinvoice');
    //     Route::get('stproducts/price', [PriceController::class, 'stt_product'])->name('stt_product');
    //     Route::post('stproduct/price/update/{id}', [PriceController::class, 'price_update'])->name('price_update');

    //     Route::get('bulk/upload', [ImpexcelController::class, 'bulkup'])->name('bulkuprice');
    //     Route::post('bulk_upload/price', [ImpexcelController::class, 'import'])->name('bulk_uploadprice');
    //     Route::post('bulk_upload/stock', [ImpexcelController::class, 'importstock'])->name('bulk_uploadstock');


    //     Route::get('itemlist/requirement/today', [StRequiredController::class, 'reqfortoday'])->name('reqfortoday');
    //     Route::post('itemlist/requirement/datewise', [StRequiredController::class, 'reqfordate'])->name('datewise_itemsales');

    //     Route::get('store_orders/today/all/', [StSalesreportController::class, 'sales_today'])->name('store_sales_today');
    //     Route::post('store_orders/day-wise/', [StSalesreportController::class, 'orders'])->name('store_datewise_orders');

    //     Route::get('store/orderbyphoto/', [ByphotoController::class, 'user_list'])->name('storeorder_byphoto');
    //     Route::get('store/makeorder/{id}', [ByphotoController::class, 'sel_product'])->name('store_accept_order');
    //     Route::post('list/product/added/', [ByphotoController::class, 'added_product'])->name('listadded_product');
    //     Route::get('admin/reject/orderlist/{id}', [ByphotoController::class, 'rejectorder'])->name('admin_reject_orderphoto');

    //     Route::get('list/product/delete_from_cart/{id}', [ByphotoController::class, 'delete_product'])->name('delete_product_from_cart');
    //     Route::post('list/product/add_qty/{id}', [ByphotoController::class, 'add_qty'])->name('add_qty_to_cart');
    //     Route::post('reject/order/{id}', [ByphotoController::class, 'rejectorder'])->name('store_reject_orderbyphoto');
    //     Route::post('order/processed/{ord_id}', [ByphotoController::class, 'checkout'])->name('process_orderby');

    //     Route::get('storebannerlist', [StorebannerController::class, 'bannerlist'])->name('storebannerlist');
    //     Route::get('storebanner', [StorebannerController::class, 'banner'])->name('storebanner');
    //     Route::post('storebanneradd', [StorebannerController::class, 'banneradd'])->name('storebanneradd');
    //     Route::get('storebanneredit/{banner_id}', [StorebannerController::class, 'banneredit'])->name('storebanneredit');
    //     Route::post('storebannerupdate/{banner_id}', [StorebannerController::class, 'bannerupdate'])->name('storebannerupdate');
    //     Route::get('storebannerdelete/{banner_id}', [StorebannerController::class, 'bannerdelete'])->name('storebannerdelete');


    //     Route::get('deal/list', [DealController::class, 'list'])->name('deallist');
    //     Route::get('deal/add', [DealController::class, 'AddDeal'])->name('AddDeal');
    //     Route::post('deal/add/new', [DealController::class, 'AddNewDeal'])->name('AddNewDeal');
    //     Route::get('deal/edit/{id}', [DealController::class, 'EditDeal'])->name('EditDeal');
    //     Route::post('deal/update/{id}', [DealController::class, 'UpdateDeal'])->name('UpdateDeal');
    //     Route::get('deal/delete/{id}', [DealController::class, 'DeleteDeal'])->name('DeleteDeal');


    //     Route::get('store/timeslot', [StoreTimeslotController::class, 'timeslot'])->name('storetimeslot');
    //     Route::post('store/timeslotupdate', [StoreTimeslotController::class, 'timeslotupdate'])->name('storetimeslotupdate');
    //     Route::post('amountupdate', [StoreTimeslotController::class, 'amountupdate'])->name('amountupdate');
    //     Route::post('del_charge/update', [StoreTimeslotController::class, 'updatedel_charge'])->name('updatedel_charge');

    //     Route::get('st/product/list', [StoreProductController::class, 'list'])->name('storeproductlist');
    //     Route::get('st/product/add', [StoreProductController::class, 'AddProduct'])->name('storeAddProduct');
    //     Route::post('st/product/add/new', [StoreProductController::class, 'AddNewProduct'])->name('storeAddNewProduct');
    //     Route::get('st/product/edit/{product_id}', [StoreProductController::class, 'EditProduct'])->name('storeEditProduct');
    //     Route::post('st/product/update/{product_id}', [StoreProductController::class, 'UpdateProduct'])->name('storeUpdateProduct');
    //     Route::get('st/product/delete/{product_id}', [StoreProductController::class, 'DeleteProduct'])->name('storeDeleteProduct');

    //     Route::get('special/varient/{id}', [StoreVarientController::class, 'varient'])->name('storevarient');
    //     Route::get('special/varient/add/{id}', [StoreVarientController::class, 'Addproduct'])->name('storeadd-varient');
    //     Route::post('special/varient/add/new', [StoreVarientController::class, 'AddNewproduct'])->name('storeAddNewvarient');
    //     Route::get('special/varient/edit/{id}', [StoreVarientController::class, 'Editproduct'])->name('storeedit-varient');
    //     Route::post('special/varient/update/{id}', [StoreVarientController::class, 'Updateproduct'])->name('storeupdate-varient');
    //     Route::get('special/varient/delete/{id}', [StoreVarientController::class, 'deleteproduct'])->name('storedelete-varient');


    //     Route::get('callback_requests', [CallbackController::class, 'callbacklist'])->name('callback_requests');
    //     Route::get('callbackproc/{id}', [CallbackController::class, 'call_proc'])->name('callbackproc');

    //     Route::get('notification/to-users', [UsrnotificationController::class, 'storeNotification'])->name('storeNotification');
    //     Route::post('notification/send', [UsrnotificationController::class, 'storeNotificationSend'])->name('storeNotificationSend');


    //     Route::get('store/completed_orders', [StoreordersController::class, 'store_com_orders'])->name('store_com_orders');
    //     Route::get('store/pending_orders', [StoreordersController::class, 'store_pen_orders'])->name('store_pen_orders');
    //     Route::get('store/cancelled_orders', [StoreordersController::class, 'store_can_orders'])->name('store_can_orders');


    //     Route::get('d_boy/list', [DeliveryboyController::class, 'list'])->name('store_d_boylist');
    //     Route::get('d_boy/add', [DeliveryboyController::class, 'AddD_boy'])->name('store_AddD_boy');
    //     Route::post('d_boy/add/new', [DeliveryboyController::class, 'AddNewD_boy'])->name('store_AddNewD_boy');
    //     Route::get('d_boy/edit/{id}', [DeliveryboyController::class, 'EditD_boy'])->name('store_EditD_boy');
    //     Route::post('d_boy/update/{id}', [DeliveryboyController::class, 'UpdateD_boy'])->name('store_UpdateD_boy');
    //     Route::get('d_boy/delete/{id}', [DeliveryboyController::class, 'DeleteD_boy'])->name('store_DeleteD_boy');

    //     Route::get('st_driver_callback_requests', [CallbackController::class, 'drivercallbacklist'])->name('st_driver_callback_requests');
    //     Route::get('st_driver_callbackproc/{id}', [CallbackController::class, 'driver_call_proc'])->name('st_driver_callbackproc');


    //     Route::get('d_boy/orders/{id}', [StoreordersController::class, 'store_dboy_orders'])->name('store_dboy_orders');
    //     Route::post('st/d_boy/assign/{id}', [StoreordersController::class, 'assigndboyo'])->name('sto_dboy_assign');
    //     Route::get('st/missed/orders', [StoreordersController::class, 'missed_orders'])->name('st_missed_orders');

    //     Route::get('st/status/change/{cart_id}', [StoreordersController::class, 'change'])->name('st_changeStatus');
    //     Route::post('d_boy/assign/{id}', [StoreordersController::class, 'assigndboy'])->name('st_dboy_assign');
    //     Route::get('item-sales-report/last-30-days', [StRequiredController::class, 'reqforthirty'])->name('reqforthirty');


    //     Route::get('order/reports', [DeliveryboyController::class, 'boy_reports'])->name('st_boy_reports');
    //     Route::post('incentive', [StoreTimeslotController::class, 'updateincentive'])->name('up_store_incentive');
    //     Route::get('dboy_incentive', [DriverfinanceController::class, 'boy_incentive'])->name('store_boy_incentive');


    //     Route::post('dboy_pay/{dboy_id}', [DriverfinanceController::class, 'incentive_pay'])->name('store_incentive_pay');
    //     Route::post('Orders/Reassign/{cart_id}', [AssignorderController::class, 'reassign_order'])->name('store_reassign_order');


    //     Route::get('driver/Notification', [UsrnotificationController::class, 'storeNotificationdriver'])->name('storeNotificationdriver');
    //     Route::post('driver/Notification/send', [UsrnotificationController::class, 'Notification_to_driver_Send'])->name('store_Notification_to_driver_Send');
    //     Route::get('sendnotificationus', [UsrnotificationController::class, 'sendnotificationus'])->name('sendnotificationus');

    //     Route::get('store/home-category', [StorehomecateController::class, 'allhomecate'])->name('storehomecate');
    //     Route::get('store/home-category/add', [StorehomecateController::class, 'AddCategory'])->name('storeAddHomeCategory');
    //     Route::post('store/home-category/insert', [StorehomecateController::class, 'InsertCategory'])->name('storeInsertHomeCategory');
    //     Route::get('store/home-category/edit/{id}', [StorehomecateController::class, 'EditCategory'])->name('storeoHomecateEditCategory');
    //     Route::post('store/home-category/update/{id}', [StorehomecateController::class, 'UpdateCategory'])->name('storeUpdateHomeCategory');
    //     Route::get('store/home-category/delete/{id}', [StorehomecateController::class, 'DeleteCategory'])->name('storeHomecateDeleteCategory');


    //     Route::get('store/assign-home-category/{id}', [StoreassignHomecateController::class, 'assignhomecat'])->name('storeAssignHomeCategory');
    //     Route::post('store/assign-home-category/insert', [StoreassignHomecateController::class, 'InsertAssignHomeCat'])->name('storeInsertAssignHomeCategory');
    //     Route::get('store/assign-home-category/delete/{id}', [StoreassignHomecateController::class, 'DeleteAssignhomecat'])->name('storeDeleteAssignHomeCategory');

    //     Route::get('products/order_quantity', [PriceController::class, 'stt_product2'])->name('stt_product2');
    //     Route::post('product/order_quantity/update/{id}', [PriceController::class, 'qty_update'])->name('qty_update');

    //     Route::post('bulk_upload/order_qty', [ImpexcelController::class, 'importquantity'])->name('importquantity');


    //     // for Secondary banner
    //     Route::get('secondary_bannerlist', [SecondaryController::class, 'bannerlist'])->name('sec_bannerlist');
    //     Route::get('secondary_banner', [SecondaryController::class, 'banner'])->name('sec_banner');
    //     Route::post('secondary_banneradd', [SecondaryController::class, 'banneradd'])->name('sec_banneradd');
    //     Route::get('secondary_banneredit/{banner_id}', [SecondaryController::class, 'banneredit'])->name('sec_banneredit');
    //     Route::post('secondary_bannerupdate/{banner_id}', [SecondaryController::class, 'bannerupdate'])->name('sec_bannerupdate');
    //     Route::get('secondary_bannerdelete/{banner_id}', [SecondaryController::class, 'bannerdelete'])->name('sec_bannerdelete');

    //     // for society
    //     Route::get('service_societylist', [AreaController::class, 'societylist'])->name('ser_societylist');
    //     Route::get('service_societyedit/{ser_id}', [AreaController::class, 'societyedit'])->name('ser_societyedit');
    //     Route::post('service_societyupdate/{ser_id}', [AreaController::class, 'societyupdate'])->name('ser_societyupdate');


    //     Route::get('societyenable/{ser_id}', [AreaController::class, 'societyenable'])->name('ser_societycheck');

    //     Route::get('service_societydelete/{ser_id}', [AreaController::class, 'societydelete'])->name('ser_delete');
    //     Route::get('store/all_orders', [StoreordersController::class, 'store_all_orders'])->name('store_all_orders');
    //     Route::get('store/ongoing_orders', [StoreordersController::class, 'store_on_orders'])->name('store_on_orders');
    //     Route::get('store/out_for_delivery_orders', [StoreordersController::class, 'store_out_orders'])->name('store_out_orders');
    //     Route::get('store/payment_failed_orders', [StoreordersController::class, 'store_failed_orders'])->name('store_failed_orders');

    //     Route::get('spotlight/add', [StProductController::class, 'sp_product'])->name('spotlight_product');
    //     Route::post('spotlight/added', [StProductController::class, 'added_spotlight'])->name('added_spotlight');
    //     Route::get('spotlight/delete/{id}', [StProductController::class, 'rem_spotlight'])->name('rem_spotlight');
    //     Route::get('store/invoice/a4/{cart_id}', [InvoiceController::class, 'a4invoice'])->name('a4invoice');
    //     Route::post('serarea/add', [AreaController::class, 'societyadd'])->name('ser_societyadddd');
    // });

    // });

    // Route::namespace("CityAdmin")->prefix('cityadmin')->group(function () {

    //     Route::get('/', [CityAdLoginController::class, 'cityAdLogin'])->name('cityadmin-login');
    //     Route::get('checklogin', [CityAdLoginController::class, 'cityAdGetCheckLogin'])->name('cityadmin-getchecklogin');
    //     Route::post('checklogin', [CityAdLoginController::class, 'cityAdCheckLogin'])->name('cityadmin-checklogin');

    //     Route::group(['middleware' => 'auth:cityadmin'], function () {
    //         Route::get('home', [CityAdHomeController::class, 'cityHome'])->name('cityadhome');
    //         Route::get('logout', [CityAdLoginController::class, 'cityAdLogOut'])->name('cityad-logout');

    //         Route::get('notificateUser', [CityAdNotifyController::class, 'cityAdNotifyUser'])->name('cityad-note2user');
    //         Route::post('notificateUser', [CityAdNotifyController::class, 'cityAdSendNotifyUser'])->name('cityad-send_note2user');
    //         Route::get('notificateVendor', [CityAdNotifyController::class, 'cityAdNotifyVendor'])->name('cityad-note2vendor');
    //         Route::post('notificateVendor', [CityAdNotifyController::class, 'cityAdSendNotifyVendor'])->name('cityad-send_note2vendor');

    //         Route::get('delivery_boy', [CityAdDBoyController::class, 'cityAdDBoyList'])->name('cityad-dboylist');
    //         Route::get('delivery_boy/add', [CityAdDBoyController::class, 'cityAdDBoyAdd'])->name('cityad-dboyadd');
    //         Route::post('delivery_boy/add', [CityAdDBoyController::class, 'cityAdDBoyAddNew'])->name('cityad-dboynew');
    //         Route::get('delivery_boy/edit/{id}', [CityAdDBoyController::class, 'cityAdDBoyEdit'])->name('cityad-dboyedit');
    //         Route::post('delivery_boy/edit/{id}', [CityAdDBoyController::class, 'cityAdDBoyUpdate'])->name('cityad-dboyupdate');
    //         Route::get('delivery_boy/delete/{id}', [CityAdDBoyController::class, 'cityAdDBoyDelete'])->name('cityad-dboydelete');
    //         Route::get('delivery_boy/delete/{id}', [CityAdDBoyController::class, 'cityAdDBoyDelete'])->name('cityad-dboydelete');
    //         Route::get('delivery_boy/incentive/report', [CityAdDBoyController::class, 'cityAdDBoyListCommision'])->name('cityad-dboylist-commisions');
    //         Route::post('delivery_boy/set-incentive', [CityAdDBoyController::class, 'cityAdDBoyAddCommision'])->name('cityad-dboyadd-commisions');

    //         Route::get('vendor', [CityAdStoreController::class, 'cityAdStoreList'])->name('cityad-storelist');
    //         Route::get('vendor/add', [CityAdStoreController::class, 'cityAdStoreAdd'])->name('cityad-storeadd');
    //         Route::post('vendor/add', [CityAdStoreController::class, 'cityAdStoreAddNew'])->name('cityad-storenew');
    //         Route::get('vendor/edit/{id}', [CityAdStoreController::class, 'cityAdStoreEdit'])->name('cityad-storedit');
    //         Route::post('vendor/edit/{id}', [CityAdStoreController::class, 'cityAdStoreUpdate'])->name('cityad-storeupdate');
    //         Route::get('vendor/delete/{id}', [CityAdStoreController::class, 'cityAdStoreDelete'])->name('cityad-storedelete');
    //         Route::get('orders/today', [CityAdStoreController::class, 'cityAdStoreOrdersToday'])->name('cityad-orders2day');
    //         Route::get('vendor/{id}/orders/today', [CityAdStoreController::class, 'cityAdStoreOrdersToday'])->name('cityad-ordersbystore');

    //         Route::get('area', [CityAreaController::class, 'cityAdAreaList'])->name('cityad-arealist');
    //         Route::get('area/add', [CityAreaController::class, 'cityAdAreaAdd'])->name('cityad-areadd');
    //         Route::post('area/add', [CityAreaController::class, 'cityAdAreaNew'])->name('cityad-areanew');
    //         Route::get('area/edit/{id}', [CityAreaController::class, 'cityAdAreaEdit'])->name('cityad-areaedit');
    //         Route::post('area/edit/{id}', [CityAreaController::class, 'cityAdAreaUpdate'])->name('cityad-areaupdate');
    //         Route::get('area/delete/{id}', [CityAreaController::class, 'cityAdAreaDelete'])->name('cityad-areadelete');

    //     });
    // });
});

//Frontend Routes
Route::get('/', [IndexController::class, 'index'])->name('index');
Route::get('/generate-invoice/{orderId}', [IndexController::class, 'generate_invoice'])->name('user.generate_invoice');

//Product Routes{
Route::get('/product/{slug}', [SingleProductController::class, 'index'])->name('single.product.view');
// Route::get('/category/{slug}/{sub_category_slug}', [CustomerProductController::class, 'products_category_wise'])->name('all_products_category_wise');
Route::get('/best-seller-products', [CustomerProductController::class, 'bestSellerProducts'])->name('bestSellerProducts');
Route::get('/new-products', [CustomerProductController::class, 'newProducts'])->name('newProducts');

//Auth Routes
Route::get('/customer/login', [CustomerAuthController::class, 'index'])->name('customer.login.index');
Route::post('/customer/login', [CustomerAuthController::class, 'login'])->name('customer.login');
Route::get('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

//Sign Up Routes
Route::get('/customer/register', [SignUpController::class, 'index'])->name('customer.register.index');
Route::post('/customer/register', [SignUpController::class, 'register'])->name('customer.register');

//Search Routes
Route::get('/search', [SearchController::class, 'search'])->name('search');
Route::get('/search-results', [SearchController::class, 'searchResults'])->name('search.results');

// Blogs
Route::get('/blogs', [BlogController::class, 'index'])->name('customer.blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('customer.blog.show');


//Shop Grid Routes
Route::get('categories/{slug}', [ShopGridController::class, 'index'])->name('shop_grid');
Route::get('category/{slug}/{sub_category_slug?}/{child_category_slug?}', [ShopGridController::class, 'getCatList'])->name('getCatList');
Route::get('/categories', [ShopGridController::class, 'allCategories'])->name('allCategories');

//Shop Page
Route::get('shop-page', [ShopPageController::class, 'index'])->name('shop.page.index');

//Bulk Buy Routes
Route::get('/bulk-buy', [BulkBuyController::class, 'index'])->name('bulk-buy.index');
Route::post('/bulk-buy/store', [BulkBuyController::class, 'store'])->name('bulk-buy.store');

//Contact Us Routes
Route::get('/contact-us', [ContactUsController::class, 'index'])->name('contact-us.index');
Route::post('/contact-us/store', [ContactUsController::class, 'store'])->name('contact-us.store');

//About Us Routes
Route::get('about-us', [AboutUsController::class, 'index'])->name('frontend.about-us');

//Privacy Policy Routes
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'index'])->name('frontend.privacy_policy');

//Terms and Condition Routes
Route::get('/terms-and-conditions', [TermsAndConditionsController::class, 'index'])->name('frontend.terms_and_conditions');

//Return And Exchange Routes
Route::get('/return-and-exchange-policy', [ReturnAndExchangeController::class, 'index'])->name('frontend.return_and_exchange');

//Terms and Condition Routes
Route::get('/cancellation-and-refund-policy', [CancellationAndRefundController::class, 'index'])->name('frontend.cancellation_and_refund');

//Terms and Condition Routes
Route::get('/shipping-and-delivery-policy', [ShippingAndDeliveryController::class, 'index'])->name('frontend.shipping_and_delivery');

//Cart Routes
Route::get('/cart', [CartController::class, 'getCartItems'])->name('getCartItems');
Route::post('/add/cart', [CartController::class, 'addToCart'])->name('addToCart');
Route::post('/remove/cart', [CartController::class, 'removeFromCart'])->name('removeFromCart');
Route::post('/update/cart/decrease', [CartController::class, 'cartUpdateDecrease'])->name('cartUpdateDecrease');
Route::post('/update/cart/increase', [CartController::class, 'cartUpdateIncrease'])->name('cartUpdateIncrease');



Route::get('orders/address/delete/{uuid}', [CustomerOrderContoller::class, 'deleteAddress'])->name('orders.address.delete');


Route::get('/track', [CustomerOrderContoller::class, 'trackOrder'])
    ->name('tracking');

Route::middleware(['frontend.auth'])->group(function () {

    Route::get('/checkout', [CustomerOrderContoller::class, 'checkout'])->name('checkout.index');
    Route::post('checkout', [CustomerOrderContoller::class, 'storeOrder'])->name('checkout');


    Route::get('/orders/{order}/tracking', [CustomerOrderContoller::class, 'tracking'])
        ->name('customer.orders.tracking');

    //Dashboard Routes
    Route::get('/customer/dashboard', [DashboardController::class, 'index'])->name('customer.dashboard.index');

    Route::get('/profile', [CustomerProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [CustomerProfileController::class, 'update'])->name('profile.update');

    //Order Routes

    Route::get('/success/order/{cart_id}', [CustomerOrderContoller::class, 'successOrder'])->name('successOrder');
    Route::get('/orders', [CustomerOrderContoller::class, 'index'])->name('customer.orders.index');
    Route::get('/track-orders', [CustomerOrderContoller::class, 'getTrackOrder'])->name('customer.track-orders.index');
    Route::post('orders/address/store', [CustomerOrderContoller::class, 'addressStore'])->name('orders.address.store');
    Route::post('orders/address/update', [CustomerOrderContoller::class, 'updateAddress'])->name('orders.address.update');

    //Address Routes
    Route::get('address/list', [AddressController::class, 'index'])->name('dashboard_my_addresses');
    Route::post('address/store', [AddressController::class, 'store'])->name('customer.address.store');
    Route::get('address/edit', [AddressController::class, 'edit'])->name('customer.address.edit');
    Route::post('address/update', [AddressController::class, 'update'])->name('customer.address.update');
    Route::get('address/delete/{address_id}', [AddressController::class, 'delete'])->name('customer.address.delete');


    Route::get('/order/invoice/{cart_id}', [OrderController::class, 'viewInvoice'])->name('order.invoice');
    Route::post('/order/review/{orderItem}', [RatingReviewController::class, 'addReview'])->name('frontend.order.review.store');

    //Wishlist Routes
});
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
Route::post('/addToWishlist', [WishlistController::class, 'addToWishlist'])->name('addToWishlist');
Route::post('/removeFromWishlist', [WishlistController::class, 'removeFromWishlist'])->name('removeFromWishlist');


// Livewire::setScriptRoute(function ($handle) {
//     return Route::get('/' . env('FILAMENT_PATH') . '/livewire/livewire.js', $handle);
// });

// Livewire::setUpdateRoute(function ($handle) {
//     return Route::get('/' . env('FILAMENT_PATH') . '/livewire/update', $handle);
// });
