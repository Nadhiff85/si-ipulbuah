<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\DeliveryAreaController;
use App\Http\Controllers\Api\Admin\HamperController as AdminHamperController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\PromotionController;
use App\Http\Controllers\Api\Admin\ReportController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\StoreSettingsController as AdminStoreSettingsController;
use App\Http\Controllers\Api\Customer\AddressController;
use App\Http\Controllers\Api\Customer\CartController;
use App\Http\Controllers\Api\Customer\CategoryController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\DeliveryController;
use App\Http\Controllers\Api\Customer\HamperController;
use App\Http\Controllers\Api\Customer\OrderController;
use App\Http\Controllers\Api\Customer\ProductController;
use App\Http\Controllers\Api\Customer\ReviewController;
use App\Http\Controllers\Api\Customer\WishlistController;
use App\Http\Controllers\Api\Admin\DeliverySlotController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Api\Customer\ProfileController;
use App\Http\Controllers\Api\Superadmin\AdminManagementController;
use App\Http\Controllers\Api\Superadmin\AuditLogController;
use App\Http\Controllers\Api\Superadmin\BackupController;
use App\Http\Controllers\Api\Superadmin\MonitoringController;
use App\Http\Controllers\Api\Superadmin\RolePermissionController;
use App\Http\Controllers\Api\Superadmin\SystemLogController;
use App\Http\Controllers\Api\Superadmin\SystemSettingController;
use App\Http\Controllers\Api\StoreSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - SI-IPULBUAH
|--------------------------------------------------------------------------
| Dikelompokkan per modul: Publik, Pelanggan, Admin Toko, Superadmin.
| Auth memakai Laravel Sanctum (token based, dikonsumsi Vue SPA).
*/

// ===================== PUBLIK (tanpa login) =====================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/store-settings', [StoreSettingsController::class, 'show']);
Route::get('/faqs', [StoreSettingsController::class, 'faqs']);

// ===================== TERAUTENTIKASI (semua role) =====================
Route::middleware(['auth:sanctum', 'session.timeout'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // ---------- Modul Pelanggan ----------
    Route::middleware('role:pelanggan')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{slug}', [ProductController::class, 'show']);
        Route::get('/categories', [CategoryController::class, 'index']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'store']);
        Route::patch('/cart/items/{item}', [CartController::class, 'update']);
        Route::delete('/cart/items/{item}', [CartController::class, 'destroy']);

        Route::get('/wishlist', [WishlistController::class, 'index']);
        Route::post('/wishlist', [WishlistController::class, 'store']);
        Route::delete('/wishlist/{wishlist}', [WishlistController::class, 'destroy']);
        Route::post('/wishlist/{wishlist}/notify-me', [WishlistController::class, 'notifyMe']);

        Route::get('/hampers', [HamperController::class, 'index']);
        Route::get('/hampers/custom-options', [HamperController::class, 'customOptions']);
        Route::get('/hampers/{hamper}', [HamperController::class, 'show']);

        Route::get('/addresses', [AddressController::class, 'index']);
        Route::post('/addresses', [AddressController::class, 'store']);
        Route::put('/addresses/{address}', [AddressController::class, 'update']);
        Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);

        Route::get('/delivery-slots', [DeliveryController::class, 'slots']);
        Route::get('/delivery-regions', [DeliveryController::class, 'regions']);

        Route::post('/checkout', [CheckoutController::class, 'store']);

        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
        Route::post('/orders/{order}/payment-proof', [OrderController::class, 'uploadPaymentProof']);

        Route::put('/profile', [ProfileController::class, 'update']);
        Route::put('/profile/password', [ProfileController::class, 'updatePassword']);

        Route::post('/reviews', [ReviewController::class, 'store']);
    });

    // ---------- Modul Admin Toko ----------
    Route::middleware('role:admin,superadmin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);

        Route::apiResource('products', AdminProductController::class);
        Route::apiResource('categories', AdminCategoryController::class)->except(['show']);
        Route::apiResource('hampers', AdminHamperController::class)->except(['index', 'show']);
        Route::get('/hampers', [AdminHamperController::class, 'index']);

        Route::apiResource('promotions', PromotionController::class)->except(['show']);

        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{order}', [AdminOrderController::class, 'show']);
        Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus']);
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'printInvoice']);

        Route::get('/payments', [AdminPaymentController::class, 'index']);
        Route::patch('/payments/{payment}/status', [AdminPaymentController::class, 'updateStatus']);

        Route::apiResource('delivery-areas', DeliveryAreaController::class)->except(['show']);
        Route::patch('/delivery-areas/{region}/toggle', [DeliveryAreaController::class, 'toggleActive']);

        Route::get('/reviews', [AdminReviewController::class, 'index']);
        Route::patch('/reviews/{review}/moderate', [AdminReviewController::class, 'moderate']);
        Route::patch('/reviews/{review}/reply', [AdminReviewController::class, 'reply']);

        Route::get('/customers', [AdminCustomerController::class, 'index']);
        Route::get('/customers/{customer}', [AdminCustomerController::class, 'show']);
        Route::patch('/customers/{customer}/toggle-active', [AdminCustomerController::class, 'toggleActive']);

        Route::get('/content/banners', [AdminContentController::class, 'banners']);
        Route::post('/content/banners', [AdminContentController::class, 'storeBanner']);
        Route::delete('/content/banners/{banner}', [AdminContentController::class, 'destroyBanner']);
        Route::get('/content/faqs', [AdminContentController::class, 'faqs']);
        Route::post('/content/faqs', [AdminContentController::class, 'storeFaq']);
        Route::put('/content/faqs/{faq}', [AdminContentController::class, 'updateFaq']);
        Route::delete('/content/faqs/{faq}', [AdminContentController::class, 'destroyFaq']);

        Route::get('/delivery-slots', [DeliverySlotController::class, 'index']);
        Route::put('/delivery-slots/{slot}', [DeliverySlotController::class, 'update']);

        Route::get('/notifications', [AdminNotificationController::class, 'index']);
        Route::post('/notifications/send', [AdminNotificationController::class, 'send']);

        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/products', [ReportController::class, 'products']);
        Route::get('/reports/stock', [ReportController::class, 'stock']);
        Route::get('/reports/shipping', [ReportController::class, 'shipping']);
        Route::get('/reports/customers', [ReportController::class, 'customers']);
        Route::get('/reports/payments', [ReportController::class, 'payments']);
        Route::get('/reports/sales/export-excel', [ReportController::class, 'exportSalesExcel']);
        Route::get('/reports/sales/export-pdf', [ReportController::class, 'exportSalesPdf']);

        Route::get('/store-settings', [AdminStoreSettingsController::class, 'show']);
        Route::put('/store-settings', [AdminStoreSettingsController::class, 'update']);
        Route::post('/store-settings/qris', [AdminStoreSettingsController::class, 'updateQris']);
    });

    // ---------- Modul Superadmin ----------
    Route::middleware('role:superadmin')->prefix('superadmin')->group(function () {
        Route::get('/admins', [AdminManagementController::class, 'index']);
        Route::post('/admins', [AdminManagementController::class, 'store']);
        Route::patch('/admins/{admin}/toggle-active', [AdminManagementController::class, 'toggleActive']);
        Route::post('/admins/{admin}/reset-password', [AdminManagementController::class, 'resetPassword']);
        Route::delete('/admins/{admin}', [AdminManagementController::class, 'destroy']);

        Route::get('/audit-trail/customers', [AuditLogController::class, 'customerList']);
        Route::get('/audit-trail/customers/{customer}', [AuditLogController::class, 'customerTrail']);
        Route::get('/audit-trail/admins', [AuditLogController::class, 'adminTrail']);

        Route::get('/backups', [BackupController::class, 'index']);
        Route::post('/backups', [BackupController::class, 'store']);
        Route::post('/backups/{backup}/restore', [BackupController::class, 'restore']);
        Route::get('/backups/{backup}/download', [BackupController::class, 'download']);

        Route::get('/roles-permissions', [RolePermissionController::class, 'index']);
        Route::post('/roles', [RolePermissionController::class, 'storeRole']);
        Route::patch('/roles/{role}/permissions', [RolePermissionController::class, 'syncPermissions']);

        Route::get('/monitoring', [MonitoringController::class, 'index']);
        Route::get('/system-logs/notifications', [SystemLogController::class, 'notificationLogs']);
        Route::get('/system-logs/errors', [SystemLogController::class, 'errorLogs']);

        Route::get('/system-settings', [SystemSettingController::class, 'show']);
        Route::put('/system-settings', [SystemSettingController::class, 'update']);
        Route::patch('/system-settings/toggle-global-delivery', [SystemSettingController::class, 'toggleGlobalDelivery']);
    });
});
