<script setup>
// Colorful page frame used by the Dashboard, Projects and Project pages:
// top header (logo + links + user) -> page content (slot) -> footer.
// A page opts in with:  defineOptions({ layout: BrandLayout })
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import '@/styles/pm.css';

// Change these names in one place.
const APP_NAME = 'Project Management System';
const TEAM = [
    { name: 'Eldana', role: 'Frontend' },
    { name: 'Hawi', role: 'Backend' },
];

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const roleLabel = computed(() =>
    page.props.auth?.isManager ? 'Project Manager' : 'Member',
);
const year = new Date().getFullYear();

// Highlight the link of the page we are on.
const path = computed(() => (page.url || '').split('?')[0]);
const onProjects = computed(() => path.value.startsWith('/projects'));
const onDashboard = computed(() => path.value.startsWith('/dashboard'));

function logout() {
    router.post('/logout');
}
</script>

<template>
    <div class="brand-root">
        <header class="topbar">
            <Link href="/dashboard" class="logo">
                <svg class="logo-mark" viewBox="0 0 32 32" aria-hidden="true">
                    <rect width="32" height="32" rx="8" fill="#ffffff" />
                    <rect
                        x="5"
                        y="6"
                        width="6"
                        height="20"
                        rx="2"
                        fill="#6366f1"
                    />
                    <rect
                        x="13"
                        y="6"
                        width="6"
                        height="13"
                        rx="2"
                        fill="#f59e0b"
                    />
                    <rect
                        x="21"
                        y="6"
                        width="6"
                        height="8"
                        rx="2"
                        fill="#22c55e"
                    />
                </svg>
                <span class="logo-text">{{ APP_NAME }}</span>
            </Link>

            <nav class="links">
                <Link
                    href="/dashboard"
                    class="link"
                    :class="{ active: onDashboard }"
                    >Dashboard</Link
                >
                <Link
                    href="/projects"
                    class="link"
                    :class="{ active: onProjects }"
                    >Projects</Link
                >
            </nav>

            <div v-if="user" class="user">
                <span class="avatar">{{
                    (user.name || '?').charAt(0).toUpperCase()
                }}</span>
                <span class="who">
                    <strong>{{ user.name }}</strong>
                    <small>{{ roleLabel }}</small>
                </span>
                <Link href="/settings/profile" class="link small">Profile</Link>
                <button type="button" class="link small logout" @click="logout">
                    Log out
                </button>
            </div>
        </header>

        <main class="content">
            <slot />
        </main>

        <footer class="footer">
            <div class="footer-brand">
                <svg
                    class="logo-mark small"
                    viewBox="0 0 32 32"
                    aria-hidden="true"
                >
                    <rect width="32" height="32" rx="8" fill="#ffffff" />
                    <rect
                        x="5"
                        y="6"
                        width="6"
                        height="20"
                        rx="2"
                        fill="#6366f1"
                    />
                    <rect
                        x="13"
                        y="6"
                        width="6"
                        height="13"
                        rx="2"
                        fill="#f59e0b"
                    />
                    <rect
                        x="21"
                        y="6"
                        width="6"
                        height="8"
                        rx="2"
                        fill="#22c55e"
                    />
                </svg>
                <span>{{ APP_NAME }} &copy; {{ year }}</span>
            </div>

            <div class="team">
                <span v-for="member in TEAM" :key="member.name" class="chip">
                    {{ member.name }} &middot; {{ member.role }}
                </span>
            </div>

            <div class="stack">
                <span class="chip tech">Laravel</span>
                <span class="chip tech">Vue</span>
                <span class="chip tech">Inertia</span>
                <span class="chip tech">MySQL</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/*
 * Fixed light "card" colors for everything inside this layout, so panels, dialogs and cards
 * are always dark-text-on-white, in both the light and the dark theme.
 */
.brand-root {
    --card: #ffffff;
    --card-foreground: #1f2937;

    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: #ffffff;
    background: linear-gradient(135deg, #4c1d95 0%, #1d4ed8 55%, #0e7490 100%);
    background-attachment: fixed;
}

.topbar {
    position: sticky;
    top: 0;
    z-index: 40;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem 1.25rem;
    padding: 0.65rem 1.25rem;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.15);
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
}

.logo-mark {
    width: 2rem;
    height: 2rem;
}

.logo-mark.small {
    width: 1.4rem;
    height: 1.4rem;
}

.logo-text {
    font-size: 1.05rem;
}

.links {
    display: flex;
    gap: 0.35rem;
    margin-right: auto;
}

.link {
    padding: 0.35rem 0.8rem;
    border: none;
    border-radius: 0.5rem;
    background: transparent;
    color: rgba(255, 255, 255, 0.85);
    font: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.link:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}

.link.active {
    background: rgba(255, 255, 255, 0.28);
    color: #ffffff;
}

.link.small {
    font-size: 0.8rem;
    padding: 0.25rem 0.6rem;
}

.user {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #f59e0b, #ef4444);
    font-weight: 700;
}

.who {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.who small {
    opacity: 0.8;
    font-size: 0.72rem;
}

.content {
    flex: 1;
    width: 100%;
    max-width: 96rem;
    margin: 0 auto;
    box-sizing: border-box;
}

.footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.75rem 1.5rem;
    padding: 0.9rem 1.25rem;
    background: rgba(15, 23, 42, 0.55);
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 0.82rem;
}

.footer-brand,
.team,
.stack {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.chip {
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.18);
    font-size: 0.75rem;
    font-weight: 600;
}

.chip.tech:nth-child(1) {
    background: #ef4444;
}

.chip.tech:nth-child(2) {
    background: #22c55e;
}

.chip.tech:nth-child(3) {
    background: #8b5cf6;
}

.chip.tech:nth-child(4) {
    background: #0891b2;
}
</style>
