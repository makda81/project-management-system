<script setup>
// Projects list: project cards for everyone, and a "Create a project" form for managers.
// Props from Laravel: projects[] { id, name, description, start_date, due_date, tasks_count }
// Request: POST /projects { name, description, start_date, due_date }
// The page uses BrandLayout (colorful header + footer) instead of the starter kit sidebar.
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import BrandLayout from '@/components/BrandLayout.vue';
import { formatDate } from '@/lib/projectHelpers';
import '@/styles/pm.css';

defineOptions({ layout: BrandLayout });

defineProps({
    projects: { type: Array, default: () => [] },
});

const page = usePage();
const isManager = computed(() => Boolean(page.props.auth?.isManager));

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
});

function createProject() {
    // Empty strings become null so Laravel sees "no value".
    form.transform((data) => ({
        ...data,
        description: data.description || null,
        start_date: data.start_date || null,
        due_date: data.due_date || null,
    })).post('/projects', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

// Trello-style colored tiles: every project gets one of these gradients.
const TILES = [
    'linear-gradient(135deg, #7c3aed, #4f46e5)',
    'linear-gradient(135deg, #0ea5e9, #2563eb)',
    'linear-gradient(135deg, #10b981, #0d9488)',
    'linear-gradient(135deg, #f59e0b, #ea580c)',
    'linear-gradient(135deg, #ec4899, #be185d)',
    'linear-gradient(135deg, #ef4444, #b91c1c)',
];

function tileColor(project) {
    return TILES[Number(project.id) % TILES.length];
}

function taskLabel(count) {
    const n = Number(count ?? 0);

    return `${n} ${n === 1 ? 'task' : 'tasks'}`;
}
</script>

<template>
    <Head title="Projects" />

    <div class="projects-page">
        <header>
            <h1 class="page-title">Projects</h1>
            <p class="pm-muted">
                Open a project to see its board, members and progress.
            </p>
        </header>

        <section v-if="isManager" class="pm-surface create-card">
            <h2 class="create-title">Create a project</h2>

            <form @submit.prevent="createProject">
                <div class="pm-field">
                    <label for="new-project-name">Project name</label>
                    <input
                        id="new-project-name"
                        v-model="form.name"
                        class="pm-input"
                        type="text"
                        maxlength="255"
                    />
                    <p v-if="form.errors.name" class="pm-error">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="pm-field">
                    <label for="new-project-description">Description</label>
                    <textarea
                        id="new-project-description"
                        v-model="form.description"
                        class="pm-input"
                        rows="3"
                    />
                    <p v-if="form.errors.description" class="pm-error">
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="row">
                    <div class="pm-field">
                        <label for="new-project-start">Start date</label>
                        <input
                            id="new-project-start"
                            v-model="form.start_date"
                            class="pm-input"
                            type="date"
                        />
                        <p v-if="form.errors.start_date" class="pm-error">
                            {{ form.errors.start_date }}
                        </p>
                    </div>

                    <div class="pm-field">
                        <label for="new-project-due">Due date</label>
                        <input
                            id="new-project-due"
                            v-model="form.due_date"
                            class="pm-input"
                            type="date"
                        />
                        <p v-if="form.errors.due_date" class="pm-error">
                            {{ form.errors.due_date }}
                        </p>
                    </div>
                </div>

                <button
                    type="submit"
                    class="pm-btn pm-btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Creating...' : 'Create project' }}
                </button>
            </form>
        </section>

        <p v-if="projects.length === 0" class="empty pm-muted">
            No projects to show yet.<template v-if="isManager">
                Create the first one above.</template
            >
        </p>

        <div v-else class="grid">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="`/projects/${project.id}`"
                class="project-card"
                :style="{ background: tileColor(project) }"
            >
                <strong class="project-name">{{ project.name }}</strong>
                <p v-if="project.description" class="project-description">
                    {{ project.description }}
                </p>
                <p class="pm-muted dates">
                    {{ formatDate(project.start_date) }} &rarr;
                    {{ formatDate(project.due_date) }}
                </p>
                <span class="pm-badge tasks">{{
                    taskLabel(project.tasks_count)
                }}</span>
            </Link>
        </div>
    </div>
</template>

<style scoped>
.projects-page {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.5rem 1.25rem;
}

.page-title {
    margin: 0 0 0.25rem;
    font-size: 1.6rem;
}

.create-card {
    max-width: 40rem;
}

.create-title {
    margin: 0 0 1rem;
    font-size: 1.1rem;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.empty {
    padding: 2rem 0;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(16rem, 1fr));
    gap: 1rem;
}

.project-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.4rem;
    min-height: 7.5rem;
    padding: 1rem;
    border-radius: 0.9rem;
    box-sizing: border-box;
    color: #ffffff;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.3);
    transition:
        box-shadow 0.15s ease,
        transform 0.15s ease;
}

.project-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    transform: translateY(-1px);
}

.project-name {
    font-size: 1.05rem;
    overflow-wrap: anywhere;
}

.project-description {
    margin: 0;
    font-size: 0.88rem;
    opacity: 0.8;
    overflow-wrap: anywhere;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.dates {
    margin: 0;
}

.tasks {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

@media (max-width: 520px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>
