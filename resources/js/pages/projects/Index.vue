<script setup>
// Inertia prop: projects is an array of
// { id, name, description, start_date, due_date, tasks_count }.
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projects',
                href: '/projects',
            },
        ],
    },
});

defineProps({
    projects: {
        type: Array,
        required: true,
    },
});

const page = usePage();

const isManager = computed(() => page.props.auth.isManager);

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
});

function createProject() {
    form.post('/projects');
}
</script>

<template>
    <Head title="Projects" />

    <main class="projects-page">
        <header class="page-header">
            <h1>Projects</h1>
            <p>
                View the projects you are part of and their current task totals.
            </p>
        </header>

        <section v-if="isManager" class="create-project-panel">
            <div class="panel-heading">
                <h2>Create a project</h2>
                <p>
                    Add the project details before assigning members and tasks.
                </p>
            </div>
            <form class="project-form" @submit.prevent="createProject">
                <div class="form-field form-field-wide">
                    <label for="name">Project name</label>
                    <input
                        id="name"
                        v-model="form.name"
                        required
                        autocomplete="off"
                        placeholder="Website redesign"
                    />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="form-field form-field-wide">
                    <label for="description">Description</label>
                    <textarea
                        id="description"
                        v-model="form.description"
                        placeholder="What is this project for?"
                    ></textarea>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="form-field">
                    <label for="start_date">Start date</label>
                    <input
                        id="start_date"
                        v-model="form.start_date"
                        type="date"
                        required
                    />
                    <InputError :message="form.errors.start_date" />
                </div>

                <div class="form-field">
                    <label for="due_date">Due date</label>
                    <input id="due_date" v-model="form.due_date" type="date" />
                    <InputError :message="form.errors.due_date" />
                </div>

                <div class="form-actions form-field-wide">
                    <button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? 'Creating project...'
                                : 'Create project'
                        }}
                    </button>
                </div>
            </form>
        </section>

        <section
            v-if="projects.length"
            class="project-list"
            aria-label="Projects"
        >
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="`/projects/${project.id}`"
                class="project-card"
            >
                <h2>{{ project.name }}</h2>
                <p class="project-description">
                    {{ project.description || 'No description provided.' }}
                </p>
                <dl class="project-details">
                    <div>
                        <dt>Start</dt>
                        <dd>{{ project.start_date }}</dd>
                    </div>
                    <div>
                        <dt>Due</dt>
                        <dd>{{ project.due_date || 'No due date' }}</dd>
                    </div>
                    <div>
                        <dt>Tasks</dt>
                        <dd>{{ project.tasks_count }}</dd>
                    </div>
                </dl>
            </Link>
        </section>

        <p v-else class="empty-message">There are no projects to show yet.</p>
    </main>
</template>

<style scoped>
.projects-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px;
}
.page-header,
.panel-heading {
    margin-bottom: 20px;
}
h1,
h2,
p {
    margin-top: 0;
}
h1 {
    font-size: 1.8rem;
}
h2 {
    font-size: 1.2rem;
}
.page-header p,
.panel-heading p,
.project-description,
.empty-message {
    color: #5f6368;
}
.create-project-panel,
.project-card,
.empty-message {
    border: 1px solid #d8dde3;
    border-radius: 10px;
    background: #fff;
}
.create-project-panel {
    margin-bottom: 24px;
    padding: 20px;
}
.project-form {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}
.form-field {
    display: grid;
    gap: 6px;
}
.form-field-wide {
    grid-column: 1 / -1;
}
label {
    font-weight: 600;
}
input,
textarea {
    box-sizing: border-box;
    width: 100%;
    border: 1px solid #aeb7c2;
    border-radius: 6px;
    padding: 9px;
    font: inherit;
}
textarea {
    min-height: 96px;
    resize: vertical;
}
button {
    border: 0;
    border-radius: 6px;
    padding: 10px 14px;
    background: #1d4ed8;
    color: #fff;
    font: inherit;
    cursor: pointer;
}
button:disabled {
    cursor: wait;
    opacity: 0.65;
}
.project-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}
.project-card {
    display: block;
    padding: 20px;
    color: inherit;
    text-decoration: none;
}
.project-card:hover,
.project-card:focus {
    border-color: #1d4ed8;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
}
.project-details {
    display: grid;
    gap: 8px;
    margin: 18px 0 0;
}
.project-details div {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}
dt {
    font-weight: 600;
}
dd {
    margin: 0;
    color: #5f6368;
}
.empty-message {
    padding: 32px;
    text-align: center;
}
@media (max-width: 640px) {
    .projects-page {
        padding: 16px;
    }
    .project-form {
        grid-template-columns: 1fr;
    }
    .form-field-wide {
        grid-column: auto;
    }
}
</style>
