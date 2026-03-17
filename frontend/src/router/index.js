// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";

// Pages (lazy loaded)
const Login = () => import("@/pages/auth/Login.vue");
const Dashboard = () => import("@/pages/Dashboard.vue");
const Layout = () => import("@/layouts/Layout.vue");
const AdminUserManagement = () => import("@/pages/Admin/AdminUserManagement.vue");
const CaseCategories = () => import("@/pages/Admin/MasterData/CaseCategories.vue");
const Courts = () => import("@/pages/Admin/MasterData/Courts.vue");
const Documents = () => import("@/pages/Admin/MasterData/Documents.vue");
const CaseMaster = () => import("@/pages/Admin/CaseMaster/CaseMaster.vue");
const AuditTrail = () => import("@/pages/Admin/AuditTrail.vue");
const Approvals = () => import("@/pages/Approvals.vue");
const AccountSetting = () => import("@/pages/AccountSetting.vue");
const Notifications = () => import("@/pages/Notifications.vue");

const routes = [
  {
    path: "/",
    name: "Login",
    component: Login,
    meta: { guest: true },
  },
  {
    path: "/",
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      { 
        path: "dashboard", 
        name: "Dashboard", 
        component: Dashboard
      },
      {
        path: "usermanagement",
        name: "AdminUserManagement",
        component: AdminUserManagement,
        meta: { roles: ["admin"] },
      },
      {
        path: "casecategories",
        name: "CaseCategories",
        component: CaseCategories,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
      {
        path: "courts",
        name: "Courts",
        component: Courts,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
      {
        path: "documents",
        name: "Documents",
        component: Documents,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
      {
        path: "casemaster",
        name: "CaseMaster",
        component: CaseMaster,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
      {
        path: "audit-trail",
        name: "AuditTrail",
        component: AuditTrail,
        meta: { roles: ["admin"] },
      },
      {
        path: "approvals",
        name: "Approvals",
        component: Approvals,
        meta: { roles: ["admin", "lawyer"] },
      },
      {
        path: "account-setting",
        name: "AccountSetting",
        component: AccountSetting,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
      {
        path: "notifications",
        name: "Notifications",
        component: Notifications,
        meta: { roles: ["admin", "lawyer", "clerk"] },
      },
    ],
  },
  {
    path: "/:catchAll(.*)",
    redirect: "/dashboard",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ── Helpers ─────────────────────────────
function isAuthenticated() {
  return !!sessionStorage.getItem("token");
}

function getUserRole() {
  try {
    const user = JSON.parse(sessionStorage.getItem("user"));
    const role = user?.role?.name ?? (typeof user?.role === "string" ? user.role : null);
    return role?.toLowerCase();
  } catch {
    return null;
  }
}

// ── Navigation Guard ─────────────────────────────
router.beforeEach((to, from) => {
  const authenticated = isAuthenticated();
  const userRole = getUserRole();

  // Guest pages - redirect to dashboard if authenticated
  if (to.meta.guest && authenticated) {
    return "/dashboard";
  }

  // Auth required pages - redirect to login if not authenticated
  if (to.meta.requiresAuth && !authenticated) {
    return "/";
  }

  // Role-based access
  if (to.meta.roles && to.meta.roles.length > 0) {
    if (!userRole || !to.meta.roles.includes(userRole)) {
      return "/dashboard";
    }
  }

  return true;
});


export default router;