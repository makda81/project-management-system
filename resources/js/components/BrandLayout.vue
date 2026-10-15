<script setup>
// Page frame used by the Projects and Project pages:
// top header (logo + links + user) -> page content (slot) -> footer.
// A page opts in with:  defineOptions({ layout: BrandLayout })
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import ThemeToggle from '@/components/ThemeToggle.vue';
import '@/styles/pm.css';

const APP_NAME = 'Project Management System';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const roleLabel = computed(() =>
    page.props.auth?.isManager ? 'Project Manager' : 'Member',
);
const year = new Date().getFullYear();

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
                <ThemeToggle />
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
            <svg class="logo-mark small" viewBox="0 0 32 32" aria-hidden="true">
                <rect width="32" height="32" rx="8" fill="#ffffff" />
                <rect x="5" y="6" width="6" height="20" rx="2" fill="#6366f1" />
                <rect
                    x="13"
                    y="6"
                    width="6"
                    height="13"
                    rx="2"
                    fill="#f59e0b"
                />
                <rect x="21" y="6" width="6" height="8" rx="2" fill="#22c55e" />
            </svg>
            <span>{{ APP_NAME }} &copy; {{ year }}</span>
        </footer>
    </div>
</template>

<style scoped>
.brand-root {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    color: var(--foreground);
    background: var(--pm-page-bg);
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
    background: color-mix(in srgb, var(--card) 88%, transparent);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid var(--border);
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: var(--foreground);
    text-decoration: none;
    font-weight: 700;
}

.logo-mark {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    box-shadow:
        0 0 0 1px var(--border),
        var(--pm-shadow);
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
    color: var(--muted-foreground);
    font: inherit;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.link:hover,
.link.active {
    background: var(--accent);
    color: var(--accent-foreground);
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
    background: linear-gradient(135deg, #7c3aed, #2563eb);
    color: #ffffff;
    font-weight: 700;
}

.who {
    display: flex;
    flex-direction: column;
    line-height: 1.15;
}

.who small {
    color: var(--muted-foreground);
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
    justify-content: center;
    gap: 0.6rem;
    padding: 0.9rem 1.25rem;
    background: var(--card);
    border-top: 1px solid var(--border);
    color: var(--muted-foreground);
    font-size: 0.82rem;
}
</style>
