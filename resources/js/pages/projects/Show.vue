<script setup>
// Inertia props: project includes members [{ id, name, email }] and tasks.
// Each task has the contract fields: id, assigned_to, title, description,
// status, priority, start_date, due_date, position, and review fields.
import { Head } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects', href: '/projects' },
            { title: 'Project details', href: '/projects' },
        ],
    },
});

defineProps({
    project: {
        type: Object,
        required: true,
    },
    availableUsers: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head :title="project.name" />

    <main class="project-page">
        <header class="project-header">
            <p class="eyebrow">Project</p>
            <h1>{{ project.name }}</h1>
            <p>{{ project.description || 'No description provided.' }}</p>
            <dl class="project-dates">
                <div>
                    <dt>Start date</dt>
                    <dd>{{ project.start_date }}</dd>
                </div>
                <div>
                    <dt>Due date</dt>
                    <dd>{{ project.due_date || 'No due date' }}</dd>
                </div>
            </dl>
        </header>

        <section class="tasks-section" aria-labelledby="tasks-heading">
            <div class="section-heading">
                <h2 id="tasks-heading">Tasks</h2>
                <p>
                    Tasks are read-only for now. Creating and editing comes
                    next.
                </p>
            </div>

            <div v-if="project.tasks.length" class="task-list">
                <article
                    v-for="task in project.tasks"
                    :key="task.id"
                    class="task-card"
                >
                    <div>
                        <h3>{{ task.title }}</h3>
                        <p>
                            {{ task.description || 'No description provided.' }}
                        </p>
                    </div>
                    <dl class="task-details">
                        <div>
                            <dt>Status</dt>
                            <dd>{{ task.status }}</dd>
                        </div>
                        <div>
                            <dt>Priority</dt>
                            <dd>{{ task.priority }}</dd>
                        </div>
                        <div>
                            <dt>Due date</dt>
                            <dd>{{ task.due_date || 'No due date' }}</dd>
                        </div>
                    </dl>
                </article>
            </div>

            <p v-else class="empty-message">
                This project does not have any tasks yet.
            </p>
        </section>
    </main>
</template>

<style scoped>
.project-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 24px;
}
.project-header,
.tasks-section {
    border: 1px solid #d8dde3;
    border-radius: 10px;
    background: #fff;
    padding: 24px;
}
.tasks-section {
    margin-top: 24px;
}
.eyebrow {
    margin: 0 0 6px;
    color: #1d4ed8;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.75rem;
}
h1,
h2,
h3,
p {
    margin-top: 0;
}
h1 {
    margin-bottom: 10px;
    font-size: 1.8rem;
}
h2 {
    margin-bottom: 4px;
    font-size: 1.25rem;
}
h3 {
    margin-bottom: 6px;
    font-size: 1rem;
}
.project-header > p:not(.eyebrow),
.section-heading p,
.task-card p,
dd {
    color: #5f6368;
}
.project-dates,
.task-details {
    display: flex;
    flex-wrap: wrap;
    gap: 16px 32px;
    margin: 20px 0 0;
}
.project-dates div,
.task-details div {
    display: grid;
    gap: 3px;
}
dt {
    font-size: 0.8rem;
    font-weight: 700;
}
dd {
    margin: 0;
}
.section-heading {
    margin-bottom: 16px;
}
.task-list {
    display: grid;
    gap: 12px;
}
.task-card {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    border: 1px solid #d8dde3;
    border-radius: 8px;
    padding: 16px;
}
.task-details {
    margin: 0;
    justify-content: flex-end;
}
.empty-message {
    margin: 0;
    border: 1px dashed #aeb7c2;
    border-radius: 8px;
    padding: 24px;
    color: #5f6368;
    text-align: center;
}
@media (max-width: 640px) {
    .project-page {
        padding: 16px;
    }
    .project-header,
    .tasks-section {
        padding: 18px;
    }
    .task-card {
        display: block;
    }
    .task-details {
        margin-top: 16px;
        justify-content: flex-start;
    }
}
</style>
