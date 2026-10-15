<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { home } from '@/routes';
import '@/styles/pm.css';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="pm-auth-root">
        <div class="pm-auth-theme">
            <ThemeToggle />
        </div>

        <div class="pm-auth-card">
            <Link :href="home()" class="pm-auth-logo">
                <svg
                    class="pm-logo-mark"
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
                <span>Project Management System</span>
            </Link>

            <div v-if="title || description" class="pm-auth-header">
                <h1 v-if="title">{{ title }}</h1>
                <p v-if="description">{{ description }}</p>
            </div>

            <div class="pm-auth-body">
                <slot />
            </div>
        </div>
    </div>
</template>

<style scoped>
.pm-auth-root {
    position: relative;
    min-height: 100svh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    background: var(--pm-page-bg);
    background-attachment: fixed;
    box-sizing: border-box;
}

.pm-auth-theme {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.pm-auth-card {
    width: 100%;
    max-width: 26rem;
    background: var(--card);
    color: var(--card-foreground);
    border: 1px solid var(--border);
    border-radius: 1rem;
    padding: 2rem;
    box-shadow: var(--pm-shadow);
    box-sizing: border-box;
}

.pm-auth-logo {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    color: inherit;
    text-decoration: none;
    font-weight: 700;
    margin-bottom: 1.25rem;
}

.pm-logo-mark {
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    box-shadow: var(--pm-shadow);
}

.pm-auth-header {
    margin-bottom: 1.25rem;
}
.pm-auth-header h1 {
    margin: 0 0 0.25rem;
    font-size: 1.3rem;
}
.pm-auth-header p {
    margin: 0;
    color: var(--muted-foreground);
    font-size: 0.9rem;
}
</style>
