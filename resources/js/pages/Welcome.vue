<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import ThemeToggle from '@/components/ThemeToggle.vue';
import { dashboard, login, register } from '@/routes';

const steps = [
    {
        label: 'To do',
        note: 'The manager creates the task and assigns it',
        color: '#6366f1',
    },
    { label: 'In progress', note: 'The member picks it up', color: '#f59e0b' },
    { label: 'Finished', note: 'The member marks it done', color: '#22c55e' },
    {
        label: 'Approved',
        note: 'The manager reviews and approves it',
        color: '#0ea5e9',
    },
];

const features = [
    {
        title: 'Projects and teams',
        text: 'Create projects, add members and keep every task in one place.',
    },
    {
        title: 'Drag-and-drop board',
        text: 'Members move their own tasks across the board as work progresses.',
    },
    {
        title: 'Manager review',
        text: 'Approve finished work, or send it back with a clear comment.',
    },
];

function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Welcome" />

    <div class="home">
        <header class="home-nav">
            <div class="home-brand">
                <svg viewBox="0 0 32 32" aria-hidden="true" class="home-logo">
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
            </div>
            <nav class="home-actions">
                <ThemeToggle />
                <template v-if="$page.props.auth.user">
                    <Link :href="dashboard()" class="pm-btn pm-btn-primary"
                        >Open my projects</Link
                    >
                    <button type="button" class="pm-btn" @click="logout">
                        Log out
                    </button>
                </template>
                <template v-else>
                    <Link :href="login()" class="pm-btn">Log in</Link>
                    <Link :href="register()" class="pm-btn pm-btn-primary"
                        >Register</Link
                    >
                </template>
            </nav>
        </header>

        <main class="home-main">
            <section class="home-hero">
                <span class="home-pill">Built for internship teams</span>
                <h1>
                    Plan the work.<br />Track it to
                    <span class="home-grad">done</span>.
                </h1>
                <p class="home-lead">
                    Managers create projects and assign tasks. Members move them
                    across the board. Finished work is reviewed and approved,
                    all in one simple workspace.
                </p>
                <div class="home-cta">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="dashboard()"
                        class="pm-btn pm-btn-primary home-big"
                    >
                        Go to my projects
                    </Link>
                    <template v-else>
                        <Link
                            :href="register()"
                            class="pm-btn pm-btn-primary home-big"
                            >Get started</Link
                        >
                        <Link :href="login()" class="pm-btn home-big"
                            >Log in</Link
                        >
                    </template>
                </div>
            </section>

            <section class="home-flow" aria-label="Task workflow">
                <div v-for="(s, i) in steps" :key="s.label" class="home-step">
                    <span class="home-dot" :style="{ background: s.color }">{{
                        i + 1
                    }}</span>
                    <strong>{{ s.label }}</strong>
                    <small>{{ s.note }}</small>
                </div>
            </section>

            <section class="home-features">
                <article v-for="f in features" :key="f.title" class="home-card">
                    <h3>{{ f.title }}</h3>
                    <p>{{ f.text }}</p>
                </article>
            </section>
        </main>

        <footer class="home-foot">
            Project Management System · Internship project
        </footer>
    </div>
</template>

<style scoped>
.home {
    min-height: 100svh;
    display: flex;
    flex-direction: column;
    background: var(--pm-page-bg);
    background-attachment: fixed;
    color: var(--foreground);
}
.home-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    padding: 1rem 1.5rem;
    max-width: 72rem;
    width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
}
.home-brand {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-weight: 700;
}
.home-logo {
    width: 2rem;
    height: 2rem;
    box-shadow: var(--pm-shadow);
    border-radius: 0.5rem;
}
.home-actions {
    display: flex;
    align-items: center;
    gap: 0.6rem;
}
.home-actions a,
.home-cta a {
    text-decoration: none;
}

.home-main {
    flex: 1;
    width: 100%;
    max-width: 72rem;
    margin: 0 auto;
    padding: 2rem 1.5rem 3rem;
    box-sizing: border-box;
}
.home-hero {
    text-align: center;
    max-width: 42rem;
    margin: 2rem auto 3rem;
}
.home-pill {
    display: inline-block;
    padding: 0.25rem 0.8rem;
    border-radius: 999px;
    background: var(--pm-soft);
    color: var(--accent-foreground);
    font-size: 0.8rem;
    font-weight: 600;
}
.home-hero h1 {
    margin: 1rem 0;
    font-size: clamp(2.2rem, 6vw, 3.6rem);
    line-height: 1.1;
    letter-spacing: -0.02em;
}
.home-grad {
    background: linear-gradient(135deg, #7c3aed, #2563eb);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.home-lead {
    color: var(--muted-foreground);
    font-size: 1.05rem;
    line-height: 1.6;
    margin: 0 0 1.5rem;
}
.home-cta {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.home-big {
    padding: 0.7rem 1.4rem;
    font-size: 1rem;
}

.home-flow {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(10rem, 1fr));
    gap: 1rem;
    margin-bottom: 2.5rem;
}
.home-step,
.home-card {
    background: var(--card);
    color: var(--card-foreground);
    border: 1px solid var(--border);
    border-radius: 1rem;
    padding: 1.1rem;
    box-shadow: var(--pm-shadow);
}
.home-step {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}
.home-step small {
    color: var(--muted-foreground);
}
.home-dot {
    width: 1.8rem;
    height: 1.8rem;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
    font-size: 0.85rem;
    display: grid;
    place-items: center;
}

.home-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr));
    gap: 1rem;
}
.home-card h3 {
    margin: 0 0 0.4rem;
    font-size: 1.05rem;
}
.home-card p {
    margin: 0;
    color: var(--muted-foreground);
    line-height: 1.5;
}

.home-foot {
    text-align: center;
    padding: 1.25rem;
    color: var(--muted-foreground);
    font-size: 0.85rem;
}
</style>
