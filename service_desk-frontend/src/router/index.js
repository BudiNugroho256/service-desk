import { createRouter, createWebHistory } from 'vue-router';

// Static imports
import Login from '@/pages/auth/Login.vue';
import Register from '@/pages/auth/Register.vue';
import ForgotPassword from '@/pages/auth/ForgotPassword.vue';
import TicketDetail from '@/pages/TicketDetail.vue';
import MasterData from '@/pages/MasterData.vue';
import UserManagement from '@/pages/UserManagement.vue';
import MasterDivisi from '@/pages/master-data/MasterDivisi.vue';
import MasterTicketPriority from '@/pages/master-data/MasterTicketPriority.vue';
import MasterRootcause from '@/pages/master-data/MasterRootcause.vue';
import MasterSolusi from '@/pages/master-data/MasterSolusi.vue';
import TicketPage from '@/pages/TicketPage.vue';
import MasterLayanan from '@/pages/master-data/MasterLayanan.vue';
import RoleManagement from '@/pages/RoleManagement.vue';
import PermissionManagement from '@/pages/PermissionManagement.vue';
import MasterPermintaan from '@/pages/master-data/MasterPermintaan.vue';
import MasterReport from '@/pages/master-data/MasterReport.vue';
import ReportDashboard from '@/pages/ReportDashboard.vue';

const routes = [
  {
    path: '/',
    redirect: '/login',
    // name: 'Home',
    // component: Home,
    meta: { requiresAuth: true }
  },
  {
    path: '/tickets',
    name: 'TicketPage',
    component: TicketPage,
    meta: { requiresAuth: true }
  },
  // {
  //   path: '/dashboard',
  //   name: 'Dashboard',
  //   component: Dashboard,
  //   meta: { requiresAuth: true }
  // },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { guestOnly: true }
  },
  // {
  //   path: '/register',
  //   name: 'Register',
  //   component: Register,
  //   meta: { guestOnly: true }
  // },
  // {
  //   path: '/forgot-password',
  //   name: 'ForgotPassword',
  //   component: ForgotPassword,
  //   meta: { guestOnly: true }
  // },
  // {
  //   path: '/password-reset/:token',
  //   name: 'PasswordReset',
  //   component: () => import('@/pages/auth/PasswordReset.vue'),
  //   props: true,
  //   meta: { guestOnly: true }
  // },
  {
    path: '/tickets/:ticketIdentifier',
    name: 'TicketDetail',
    component: TicketDetail,
    meta: { requiresAuth: true }
  },
  // {
  //   path: '/tickets/:id/edit',
  //   name: 'EditTicket',
  //   component: () => import('@/pages/Edit.vue'),
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/create',
  //   name: 'CreateTicket',
  //   component: () => import('@/pages/Create.vue'),
  //   meta: { requiresAuth: true }
  // },
  {
    path: '/master-data',
    name: 'MasterData',
    component: MasterData,
    meta: { requiresAuth: true }
  },
  {
    path: '/users',
    name: 'UserManagement',
    component: UserManagement,
    meta: { requiresAuth: true }
  },
  {
    path: '/master-data/divisi',
    name: 'MasterDivisi',
    component: MasterDivisi,
    meta: { requiresAuth: true }
  },
  // {
  //   path: '/master-data/ticket-status',
  //   name: 'MasterTicketStatus',
  //   component: MasterTicketStatus,
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/master-data/tracking-point',
  //   name: 'MasterTrackingPoint',
  //   component: MasterTrackingPoint,
  //   meta: { requiresAuth: true }
  // },
  // {
  //   path: '/master-data/ticket-type',
  //   name: 'MasterTicketType',
  //   component: MasterTicketType,
  //   meta: { requiresAuth: true }
  // },
  {
    path: '/master-data/ticket-priority',
    name: 'MasterTicketPriority',
    component: MasterTicketPriority,
    meta: { requiresAuth: true }
  },
  {
    path: '/master-data/layanan',
    name: 'MasterLayanan',
    component: MasterLayanan,
    meta: { requiresAuth: true }
  },
  {
    path:'/master-data/rootcause',
    name: 'MasterRootcause',
    component: MasterRootcause,
    meta: { requiresAuth: true }
  },
  {
    path:'/master-data/solusi',
    name: 'MasterSolusi',
    component: MasterSolusi,
    meta: { requiresAuth: true }
  },

  {
    path:'/master-data/permintaan',
    name: 'MasterPermintaan',
    component: MasterPermintaan,
    meta: { requiresAuth: true }
  },

  {
    path:'/master-data/report',
    name: 'MasterReport',
    component: MasterReport,
    meta: { requiresAuth: true }
  },
  // {
  //   path:'/master-data/rating',
  //   name: 'MasterRating',
  //   component: MasterRating,
  //   meta: { requiresAuth: true }
  // }
  {
    path:'/roles',
    name: 'RoleManagement',
    component: RoleManagement,
    meta: { requiresAuth: true }
  },
  {
    path:'/permissions',
    name: 'PermissionManagement',
    component: PermissionManagement,
    meta: { requiresAuth: true }
  },
  {
    path:'/report-dashboard',
    name: 'ReportDashboard',
    component: ReportDashboard,
    meta: { requiresAuth: true }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

// Global auth/guest middleware
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');

  if (!token && to.path !== '/login') {
    next('/login');
  } else {
    next();
  }
});



export default router;