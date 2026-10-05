import { createRouter, createWebHistory } from "vue-router";

// Layouts & Static Imports
import DashboardLayout from "../layouts/DashboardLayout.vue";
import AdminLayout from "../layouts/AdminLayout.vue";

// Main / Public Imports
import Marketplace from "../pages/Marketplace.vue";
import ServicesIndex from "../pages/services/Index.vue";
import ServiceShow from "../pages/services/Show.vue";

// Farmer Imports
import FarmerDashboard from "../pages/farmer/Dashboard.vue";
import FarmerListings from "../pages/farmer/Listings.vue";
import FarmerCreateListing from "../pages/farmer/CreateListing.vue";
import FarmerEditListing from "../pages/farmer/EditListing.vue";
import FarmerOrders from "../pages/farmer/Orders.vue";
import FarmerLogistics from "../pages/farmer/Logistics.vue";
import FarmerWallet from "../pages/farmer/Wallet.vue";
import FarmerWithdrawals from "../pages/farmer/Withdrawals.vue";
import FarmerProfile from "../pages/farmer/Profile.vue";

// Buyer Imports
import BuyerMarketplace from "../pages/buyer/Marketplace.vue";
import BuyerOrders from "../pages/buyer/Orders.vue";
import BuyerOrderDetails from "../pages/buyer/OrderDetails.vue";
import BuyerCheckout from "../pages/buyer/Checkout.vue";

// Service Provider Imports
import ProviderProfile from "../pages/provider/Profile.vue";
import EditProviderProfile from "../pages/provider/EditProfile.vue";

import MachineryIndex from "../pages/Machinery/Index.vue";
import MachineryShow from "../pages/Machinery/Show.vue";

// Admin Imports
import AdminDashboard from "../pages/admin/Dashboard.vue";
import AdminUsers from "../pages/admin/Users.vue";
import AdminPayments from "../pages/admin/Payments.vue";
import AdminListings from "../pages/admin/Listings.vue";
import AdminOrders from "../pages/admin/Orders.vue";
import AdminWithdrawals from "../pages/admin/Withdrawals.vue";
import AdminReports from "../pages/admin/Reports.vue";
import AdminLogistics from "../pages/admin/Logistics.vue";

const routes = [
  // 1. Public/Main Routes (No sidebar/dashboard layout)
  {
    path: "/",
    component: () => import("../pages/Home.vue")
  },
  {
    path: "/login",
    component: () => import("../pages/Login.vue")
  },
  {
    path: "/register",
    name: "register",
    component: () => import("../pages/Register.vue")
  },
  {
    path: "/listings",
    component: () => import("../pages/Listings.vue")
  },
  {
    path: "/marketplace",
    name: "Marketplace",
    component: Marketplace
  },
  {
    path: "/livestock/:id",
    name: "livestock-details",
    component: () => import("../pages/livestock/LivestockDetails.vue")
  },
  {
    path: "/transporter/trucks",
    component: () => import("../pages/transporter/MyTrucks.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/payments/pesapal/return",
    component: () => import("../pages/payments/PesapalReturn.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/finance/subscriptions",
    component: () => import("../pages/finance/Subscriptions.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/buyer/orders/:orderId/transport",
    component: () => import("../pages/buyer/TransportQuotes.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/finance/dossier",
    component: () => import("../pages/finance/FinanceDossier.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/finance/nmb",
    component: () => import("../pages/finance/NmbBank.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/finance/institution",
    component: () => import("../pages/finance/InstitutionPortal.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/knowledge/my-learning",
    component: () => import("../pages/knowledge/MyLearning.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/knowledge/courses",
    component: () => import("../pages/knowledge/Courses.vue"),
  },
  {
    path: "/knowledge/courses/:id/pay",
    component: () => import("../pages/knowledge/CoursePay.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/knowledge/courses/:id",
    component: () => import("../pages/knowledge/CourseShow.vue"),
  },
  {
    path: "/knowledge/educator",
    component: () => import("../pages/knowledge/EducatorPortal.vue"),
    meta: { requiresAuth: true },
  },
  {
    path: "/services",
    name: "services-index",
    component: ServicesIndex
  },
  {
    path: "/services/:id",
    name: "service-show",
    component: ServiceShow,
    props: true
  },
  {
    path: "/machinery",
    name: "machinery",
    component: MachineryIndex
  },
  {
    path: "/machinery/:id",
    name: "machinery.show",
    component: MachineryShow
  },

  // 2. Farmer Dashboard Routes
  {
    path: "/farmer",
    component: DashboardLayout,
    children: [
      {
        path: "",
        component: FarmerDashboard
      },
      {
        path: "listings",
        component: FarmerListings
      },
      {
        path: "listings/create",
        component: FarmerCreateListing
      },
      {
        path: "listings/:id/edit",
        component: FarmerEditListing
      },
      {
        path: "orders",
        component: FarmerOrders
      },
      {
        path: "logistics",
        component: FarmerLogistics
      },
      {
        path: "wallet",
        component: FarmerWallet
      },
      {
        path: "withdrawals",
        component: FarmerWithdrawals
      },
      {
        path: "profile",
        component: FarmerProfile
      },
      // Farm ERP
      { path: "farms", component: () => import("../pages/farm/Index.vue") },
      { path: "farms/:id", component: () => import("../pages/farm/Show.vue") },
      { path: "activities", component: () => import("../pages/farm/Activities.vue") },
      { path: "inventory", component: () => import("../pages/farm/Inventory.vue") },
      { path: "farms/:farmId/cycles/new", component: () => import("../pages/farm/CreateCycle.vue") },
      { path: "cycles/new", component: () => import("../pages/farm/CreateCycle.vue") },
      { path: "cycles/:id", component: () => import("../pages/farm/CycleShow.vue") },
      { path: "machinery", component: () => import("../pages/farm/MyMachinery.vue") },
      // Finance
      { path: "loans", component: () => import("../pages/finance/MyLoans.vue") },
      { path: "institution", component: () => import("../pages/finance/InstitutionPortal.vue") },
      { path: "insurance", component: () => import("../pages/finance/InsuranceMarketplace.vue") },
      { path: "loans/products", component: () => import("../pages/finance/LoanProducts.vue") },
      { path: "loans/apply", component: () => import("../pages/finance/ApplyLoan.vue") },
      // Knowledge
      { path: "knowledge", component: () => import("../pages/knowledge/Hub.vue") },
      { path: "ai-advisor", component: () => import("../pages/knowledge/AIAdvisor.vue") },
      // Government
      { path: "announcements", component: () => import("../pages/government/Announcements.vue") },
      { path: "subsidies", component: () => import("../pages/government/Subsidies.vue") },
      { path: "registrations", component: () => import("../pages/government/RegisterAgri.vue") },
      // Analytics
      { path: "analytics", component: () => import("../pages/analytics/FarmerAnalytics.vue") },
    ]
  },

  // Knowledge (shared layout for courses)
  {
    path: "/knowledge",
    component: DashboardLayout,
    children: [
      { path: "", component: () => import("../pages/knowledge/Hub.vue") },
      { path: "courses", component: () => import("../pages/knowledge/Courses.vue") },
      { path: "courses/:id", component: () => import("../pages/knowledge/CourseShow.vue") },
    ]
  },

  // 3. Buyer Dashboard Routes
  {
    path: "/buyer",
    component: DashboardLayout,
    children: [
      {
        path: "",
        component: BuyerMarketplace
      },
      {
        path: "orders",
        component: BuyerOrders
      },
      {
        path: "orders/:id",
        component: BuyerOrderDetails
      },
      {
        path: "checkout/:id",
        component: BuyerCheckout
      }
    ]
  },

  // 4. Service Provider Routes
  {
    path: "/provider",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", component: () => import("../pages/provider/Dashboard.vue") },
      { path: "services", component: () => import("../pages/provider/MyServices.vue") },
      { path: "machinery", component: () => import("../pages/provider/MyMachinery.vue") },
      {
        path: "profile",
        name: "provider-profile",
        component: ProviderProfile
      },
      {
        path: "profile/edit",
        name: "edit-provider-profile",
        component: EditProviderProfile
      }
    ]
  },
  {
    path: "/agrodealer",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", component: () => import("../pages/agrodealer/Dashboard.vue") },
      { path: "inputs", component: () => import("../pages/agrodealer/MyInputs.vue") },
    ]
  },
  {
    path: "/processor",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", component: () => import("../pages/processor/Dashboard.vue") },
      { path: "orders", component: () => import("../pages/processor/Orders.vue") },
      { path: "sales", component: () => import("../pages/processor/Sales.vue") },
      { path: "listings", component: () => import("../pages/processor/Listings.vue") },
      { path: "listings/create", component: () => import("../pages/farmer/CreateListing.vue") },
      { path: "wallet", component: () => import("../pages/farmer/Wallet.vue") },
    ]
  },
  {
    path: "/transporter",
    component: DashboardLayout,
    meta: { requiresAuth: true },
    children: [
      { path: "", component: () => import("../pages/transporter/Dashboard.vue") },
    ]
  },

  // 5. Admin Dashboard Routes
  {
    path: "/admin",
    component: AdminLayout,
    children: [
      {
        path: "",
        component: AdminDashboard
      },
      {
        path: "subscriptions",
        component: () => import("../pages/admin/Subscriptions.vue"),
      },
      {
        path: "users",
        component: AdminUsers
      },
      {
        path: "listings",
        component: AdminListings
      },
      {
        path: "orders",
        component: AdminOrders
      },
      {
        path: "payments",
        component: AdminPayments
      },
      {
        path: "withdrawals",
        component: AdminWithdrawals
      },
      {
        path: "reports",
        component: AdminReports
      },
      {
        path: "analytics",
        component: () => import("../pages/analytics/PlatformAnalytics.vue")
      },
      {
        path: "government-review",
        component: () => import("../pages/admin/GovernmentReview.vue")
      },
      { path: "moderation", component: () => import("../pages/admin/Moderation.vue") },
      { path: "catalog", component: () => import("../pages/admin/Catalog.vue")
      },
      {
        path: "logistics",
        component: AdminLogistics
      }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
