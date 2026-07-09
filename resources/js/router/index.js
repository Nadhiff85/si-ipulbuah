import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  // ================= MODUL PELANGGAN (Storefront) =================
  {
    path: '/',
    component: () => import('../layouts/CustomerLayout.vue'),
    children: [
      // --- Publik: hanya tampilan sekilas, tanpa perlu login ---
      { path: '', name: 'home', component: () => import('../views/customer/HomeView.vue') },
      { path: 'tentang-kami', name: 'about', component: () => import('../views/customer/AboutView.vue') },
      { path: 'faq', name: 'faq', component: () => import('../views/customer/FaqView.vue') },
      { path: 'kontak', name: 'contact', component: () => import('../views/customer/ContactView.vue') },
      { path: 'login', name: 'login', component: () => import('../views/customer/LoginView.vue') },
      { path: 'register', name: 'register', component: () => import('../views/customer/RegisterView.vue') },

      // --- Wajib login: detail produk, transaksi, & seluruh fitur belanja ---
      { path: 'katalog', name: 'catalog', component: () => import('../views/customer/CatalogView.vue'), meta: { requiresAuth: true } },
      { path: 'produk/:slug', name: 'product-detail', component: () => import('../views/customer/ProductDetailView.vue'), meta: { requiresAuth: true } },
      { path: 'paket-buah', name: 'hampers', component: () => import('../views/customer/HampersView.vue'), meta: { requiresAuth: true } },
      { path: 'parsel-kustom', name: 'custom-hamper', component: () => import('../views/customer/CustomHamperView.vue'), meta: { requiresAuth: true } },
      { path: 'keranjang', name: 'cart', component: () => import('../views/customer/CartView.vue'), meta: { requiresAuth: true } },
      { path: 'checkout', name: 'checkout', component: () => import('../views/customer/CheckoutView.vue'), meta: { requiresAuth: true } },
      { path: 'pesanan', name: 'orders', component: () => import('../views/customer/OrdersView.vue'), meta: { requiresAuth: true } },
      { path: 'pesanan/:id', name: 'order-detail', component: () => import('../views/customer/OrderDetailView.vue'), meta: { requiresAuth: true } },
      { path: 'wishlist', name: 'wishlist', component: () => import('../views/customer/WishlistView.vue'), meta: { requiresAuth: true } },
      { path: 'akun', name: 'account', component: () => import('../views/customer/AccountView.vue'), meta: { requiresAuth: true } },
      { path: 'musiman', name: 'seasonal', component: () => import('../views/customer/SeasonalView.vue'), meta: { requiresAuth: true } },
      { path: 'promo', name: 'promo', component: () => import('../views/customer/PromoView.vue'), meta: { requiresAuth: true } },
    ],
  },

  // ================= MODUL ADMIN TOKO =================
  {
    path: '/admin',
    component: () => import('../layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' },
    children: [
      { path: '', name: 'admin-dashboard', component: () => import('../views/admin/DashboardView.vue') },
      { path: 'produk', name: 'admin-products', component: () => import('../views/admin/ProductsView.vue') },
      { path: 'kategori', name: 'admin-categories', component: () => import('../views/admin/CategoriesView.vue') },
      { path: 'paket-hampers', name: 'admin-hampers', component: () => import('../views/admin/HampersView.vue') },
      { path: 'promo', name: 'admin-promotions', component: () => import('../views/admin/PromotionsView.vue') },
      { path: 'pesanan', name: 'admin-orders', component: () => import('../views/admin/OrdersView.vue') },
      { path: 'pembayaran', name: 'admin-payments', component: () => import('../views/admin/PaymentsView.vue') },
      { path: 'jadwal-pengiriman', name: 'admin-delivery-slots', component: () => import('../views/admin/DeliverySlotsView.vue') },
      { path: 'notifikasi', name: 'admin-notifications', component: () => import('../views/admin/NotificationCenterView.vue') },
      { path: 'area-pengiriman', name: 'admin-delivery-areas', component: () => import('../views/admin/DeliveryAreasView.vue') },
      { path: 'ulasan', name: 'admin-reviews', component: () => import('../views/admin/ReviewsView.vue') },
      { path: 'pelanggan', name: 'admin-customers', component: () => import('../views/admin/CustomersView.vue') },
      { path: 'konten', name: 'admin-content', component: () => import('../views/admin/ContentView.vue') },
      { path: 'laporan', name: 'admin-reports', component: () => import('../views/admin/ReportsView.vue') },
      { path: 'whatsapp-center', name: 'admin-whatsapp', component: () => import('../views/admin/WhatsappCenterView.vue') },
      { path: 'pengaturan-toko', name: 'admin-settings', component: () => import('../views/admin/StoreSettingsView.vue') },
    ],
  },

  // ================= MODUL SUPERADMIN =================
  {
    path: '/superadmin',
    component: () => import('../layouts/SuperadminLayout.vue'),
    meta: { requiresAuth: true, role: 'superadmin' },
    children: [
      { path: '', name: 'superadmin-dashboard', component: () => import('../views/superadmin/DashboardView.vue') },
      { path: 'admin-toko', name: 'superadmin-admins', component: () => import('../views/superadmin/AdminManagementView.vue') },
      { path: 'audit-trail-pelanggan', name: 'superadmin-audit-customer', component: () => import('../views/superadmin/AuditCustomerView.vue') },
      { path: 'audit-trail-admin', name: 'superadmin-audit-admin', component: () => import('../views/superadmin/AuditAdminView.vue') },
      { path: 'wilayah-global', name: 'superadmin-regions', component: () => import('../views/superadmin/RegionsView.vue') },
      { path: 'backup-database', name: 'superadmin-backup', component: () => import('../views/superadmin/BackupView.vue') },
      { path: 'role-permission', name: 'superadmin-roles', component: () => import('../views/superadmin/RolesView.vue') },
      { path: 'keamanan', name: 'superadmin-security', component: () => import('../views/superadmin/SecurityView.vue') },
      { path: 'monitoring', name: 'superadmin-monitoring', component: () => import('../views/superadmin/MonitoringView.vue') },
      { path: 'pengaturan-sistem', name: 'superadmin-system-settings', component: () => import('../views/superadmin/SystemSettingsView.vue') },
      { path: 'log-sistem', name: 'superadmin-system-logs', component: () => import('../views/superadmin/SystemLogsView.vue') },
      { path: 'pengaturan-notifikasi', name: 'superadmin-notification-settings', component: () => import('../views/superadmin/NotificationSettingsView.vue') },
    ],
  },

  {
    path: '/staff/login',
    name: 'staff-login',
    component: () => import('../views/StaffLoginView.vue'),
  },

  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFoundView.vue') },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

// Guard sederhana: cek auth & role sebelum masuk halaman admin/superadmin
router.beforeEach((to, from, next) => {
  const auth = useAuthStore()

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return next({
      name: 'login',
      query: {
        redirect: to.fullPath,
        reason: 'Masuk atau daftar dulu untuk melihat detail produk dan mulai belanja 🍊',
      },
    })
  }

  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return next({ name: 'home' })
  }

  next()
})

export default router
