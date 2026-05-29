import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    { path: '/',          component: () => import('../pages/Home.vue') },
    { path: '/login',     component: () => import('../pages/Login.vue'),    meta: { guest: true } },
    { path: '/register',  component: () => import('../pages/Register.vue'), meta: { guest: true } },
    { path: '/recursos',  component: () => import('../pages/Resources.vue') },
    { path: '/calendario', component: () => import('../pages/Calendar.vue'), meta: { auth: true } },
    {
        path: '/mis-reservas',
        component: () => import('../pages/MyReservations.vue'),
        meta: { auth: true },
    },
    {
        path: '/admin',
        component: () => import('../pages/admin/AdminLayout.vue'),
        meta: { auth: true, roles: ['admin', 'manager'] },
        children: [
            { path: '',          component: () => import('../pages/admin/Dashboard.vue') },
            { path: 'recursos',  component: () => import('../pages/admin/Resources.vue') },
            { path: 'categorias', component: () => import('../pages/admin/Categories.vue') },
            { path: 'usuarios',  component: () => import('../pages/admin/Users.vue') },
            { path: 'reservas',  component: () => import('../pages/admin/Reservations.vue') },
            { path: 'ajustes',   component: () => import('../pages/admin/Settings.vue'), meta: { roles: ['admin'] } },
        ],
    },
    { path: '/:pathMatch(.*)*', component: () => import('../pages/NotFound.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();

    if (to.meta.auth && !auth.isLoggedIn) {
        return next('/login');
    }

    if (to.meta.guest && auth.isLoggedIn) {
        return next('/');
    }

    if (to.meta.roles) {
        const hasRole = to.meta.roles.some((r) => auth.user?.roles?.some((ur) => ur.name === r));
        if (!hasRole) return next('/');
    }

    next();
});

export default router;
