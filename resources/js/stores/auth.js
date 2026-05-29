import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user:  JSON.parse(localStorage.getItem('user') || 'null'),
        token: localStorage.getItem('token') || null,
    }),

    getters: {
        isLoggedIn: (s) => !!s.token,
        isAdmin:    (s) => s.user?.roles?.some((r) => r.name === 'admin'),
        isManager:  (s) => s.user?.roles?.some((r) => r.name === 'manager' || r.name === 'admin'),
    },

    actions: {
        async login(email, password) {
            const { data } = await axios.post('/api/login', { email, password });
            this.setSession(data);
        },

        async register(name, email, password, password_confirmation) {
            const { data } = await axios.post('/api/register', { name, email, password, password_confirmation });
            this.setSession(data);
        },

        async logout() {
            await axios.post('/api/logout').catch(() => {});
            this.clearSession();
        },

        async fetchMe() {
            const { data } = await axios.get('/api/me');
            this.user = data;
            localStorage.setItem('user', JSON.stringify(data));
        },

        setSession({ token, user }) {
            this.token = token;
            this.user  = user;
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(user));
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },

        clearSession() {
            this.token = null;
            this.user  = null;
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            delete axios.defaults.headers.common['Authorization'];
        },
    },
});
