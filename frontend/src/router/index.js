// src/router/index.js
import { createRouter, createWebHistory } from "vue-router";
import Login from "@/pages/auth/Login.vue";
import Dashboard from "@/pages/Dashboard.vue";
import Layout from "@/layouts/Layout.vue";
import AdminUserManagement from "@/pages/Admin/AdminUserManagement.vue";
import CaseCategories from "@/pages/Admin/MasterData/CaseCategories.vue";
import Courts from "@/pages/Admin/MasterData/Courts.vue";
import Documents from "@/pages/Admin/MasterData/Documents.vue";
import CaseMaster from "@/pages/Admin/CaseMaster/CaseMaster.vue";
import AuditTrail from "@/pages/Admin/AuditTrail.vue";
import Approvals from "@/pages/Approvals.vue";
import AccountSetting from "@/pages/AccountSetting.vue";
import Notifications from "@/pages/Notifications.vue";



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
        component: Dashboard,
      },
      {
        path: "/usermanagement",
        name: "AdminUserManagement",
        component: AdminUserManagement,
        meta: { 
          requiresAuth: true,
          roles: ['admin'] // Only admin can access
        },
      },
      {
        path: "/casecategories",
        name: "CaseCategories",
        component: CaseCategories,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },
      {
        path: "/courts",
        name: "Courts",
        component: Courts,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },
      {
        path: "/documents",
        name: "Documents",
        component: Documents,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },
      {
        path: "/casemaster",
        name: "CaseMaster",
        component: CaseMaster,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },
      {
        path: "/audit-trail",
        name: "AuditTrail",
        component: AuditTrail,
        meta: { 
          requiresAuth: true,
          roles: ['admin'] 
        },
      },
      {
        path: "/approvals",
        name: "Approvals",
        component: Approvals,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer'] 
        },
      },
      {
        path: "/account-setting",
        name: "AccountSetting",
        component: AccountSetting,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },
      {
        path: "/notifications",
        name: "Notifications",
        component: Notifications,
        meta: { 
          requiresAuth: true,
          roles: ['admin','lawyer','clerk'] 
        },
      },



    ],
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// ── Helpers ───────────────────────────────────────────────────────
function isAuthenticated() {
  return !!sessionStorage.getItem("token");
}

function getUserRole() {
  try {
    const user = JSON.parse(sessionStorage.getItem("user"));
    // Handle both { role: { name: "admin" } } and { role: "Admin" }
    const role = user?.role?.name ?? (typeof user?.role === "string" ? user.role.toLowerCase() : null);
    return role?.toLowerCase();
  } catch {
    return null;
  }
}

// ── Navigation Guard ──────────────────────────────────────────────
router.beforeEach((to, from, next) => {
  const authenticated = isAuthenticated();
  const userRole = getUserRole();

  // Debug logging (remove in production)
  console.log('Navigation to:', to.path);
  console.log('Authenticated:', authenticated);
  console.log('User role:', userRole);
  console.log('Route meta:', to.meta);

  // Not logged in → redirect to login
  if (to.meta.requiresAuth && !authenticated) {
    return next("/");
  }

  // Already logged in → don't show login page
  if (to.meta.guest && authenticated) {
    return next("/dashboard");
  }

  // Check role-based access
  if (to.meta.roles && to.meta.roles.length > 0) {
    if (!userRole || !to.meta.roles.includes(userRole)) {
      console.warn(`Access denied: User role "${userRole}" not in allowed roles:`, to.meta.roles);
      
      // Show unauthorized message (optional)
      // You could redirect to a 403 page or just dashboard
      return next("/dashboard");
    }
  }

  next();
});

export default router;