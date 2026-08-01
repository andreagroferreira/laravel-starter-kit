import type { NavigationMenuItem } from '@nuxt/ui';

// Single source of truth for backoffice navigation.
// Consumed by AppLayout (sidebar + command palette) and SettingsLayout (tabs).

export const mainNavigation: NavigationMenuItem[] = [
    {
        label: 'Dashboard',
        icon: 'i-lucide-layout-dashboard',
        to: '/',
        exact: true,
    },
    {
        label: 'Sites',
        icon: 'i-lucide-globe',
        to: '/sites',
    },
    {
        label: 'Media',
        icon: 'i-lucide-image',
        to: '/media',
    },
    {
        label: 'Leads',
        icon: 'i-lucide-inbox',
        to: '/leads',
    },
    {
        label: 'AI Copilot',
        icon: 'i-lucide-sparkles',
        to: '/ai',
    },
];

export const settingsNavigation: NavigationMenuItem[] = [
    { label: 'Geral', to: '/settings', exact: true },
    { label: 'Membros', to: '/settings/members' },
    { label: 'Brand voice', to: '/settings/brand' },
    { label: 'API & MCP', to: '/settings/api' },
    { label: 'Segurança', to: '/settings/security' },
    { label: 'Faturação', to: '/settings/billing' },
    { label: 'Audit log', to: '/settings/audit' },
];
