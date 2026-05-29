import { defineStore } from 'pinia';
import axios from 'axios';

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        settings: {},
        loaded: false,
    }),

    getters: {
        siteName:       (s) => s.settings.site_name        ?? 'Sistema de Reservas',
        primaryColor:   (s) => s.settings.primary_color    ?? '#3B82F6',
        secondaryColor: (s) => s.settings.secondary_color  ?? '#10B981',
        fontFamily:     (s) => s.settings.font_family       ?? 'Inter',
        logoUrl:        (s) => s.settings.logo_url          ?? '',
    },

    actions: {
        async fetch() {
            if (this.loaded) return;
            const { data } = await axios.get('/api/settings/general');
            const appearance = await axios.get('/api/settings/appearance').then((r) => r.data);
            this.settings = { ...data, ...appearance };
            this.loaded = true;
            this.applyTheme();
        },

        applyTheme() {
            const root = document.documentElement;
            root.style.setProperty('--app-primary',   this.primaryColor);
            root.style.setProperty('--app-secondary',  this.secondaryColor);
            root.style.setProperty('--font-family',    this.fontFamily);
            document.title = this.siteName;
        },

        async save(settingsArray) {
            await axios.post('/api/settings', { settings: settingsArray });
            this.loaded = false;
            await this.fetch();
        },
    },
});
