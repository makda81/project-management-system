<script setup>
// Project page: header, members panel, task board, and the dialogs.
// Props from Laravel (via Inertia): project { ..., members[], tasks[] } and availableUsers[].
// The page uses the default layout (same sidebar and header as the dashboard) automatically.
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MembersPanel from '@/components/MembersPanel.vue';
import RejectDialog from '@/components/RejectDialog.vue';
import TaskBoard from '@/components/TaskBoard.vue';
import TaskDialog from '@/components/TaskDialog.vue';
import { formatDate } from '@/lib/projectHelpers';
import '@/styles/pm.css';

const props = defineProps({
    project: { type: Object, required: true },
    availableUsers: { type: Array, default: () => [] },
});

const page = usePage();
const isManager = computed(() => Boolean(page.props.auth?.isManager));
const authUserId = computed(() => page.props.auth?.user?.id ?? null);

const dialogOpen = ref(false);
const editingTask = ref(null);
const rejectingTask = ref(null);

function openCreate() {
    editingTask.value = null;
    dialogOpen.value = true;
}

function openEdit(task) {
    editingTask.value = task;
    dialogOpen.value = true;
}

function closeDialog() {
    dialogOpen.value = false;
    editingTask.value = null;
}

function deleteProject() {
    if (
        !window.confirm(
            `Delete the project "${props.project.name}" and all its tasks?`,
        )
    ) {
        return;
    }

    router.delete(`/projects/${props.project.id}`);
}
</script>

<template>
    <Head :title="project.name" />

    <div class="project-page">
        <Link href="/projects" class="back">&larr; All projects</Link>

        <header class="project-header">
            <div>
                <h1 class="project-name">{{ project.name }}</h1>
                <p v-if="project.description" class="project-description">
                    {{ project.description }}
                </p>
                <p class="pm-muted">
                    {{ formatDate(project.start_date) }} &rarr;
                    {{ formatDate(project.due_date) }}
                </p>
            </div>

            <div v-if="isManager" class="header-actions">
                <button
                    type="button"
                    class="pm-btn pm-btn-primary"
                    @click="openCreate"
                >
                    New task
                </button>
                <button
                    type="button"
                    class="pm-btn pm-btn-danger"
                    @click="deleteProject"
                >
                    Delete project
                </button>
            </div>
        </header>

        <MembersPanel
            :project="project"
            :available-users="availableUsers"
            :is-manager="isManager"
        />

        <section>
            <h2 class="section-title">Task board</h2>
            <TaskBoard
                :tasks="project.tasks || []"
                :members="project.members || []"
                :auth-user-id="authUserId"
                :is-manager="isManager"
                @edit="openEdit"
                @reject="rejectingTask = $event"
            />
        </section>

        <TaskDialog
            :open="dialogOpen"
            :project-id="project.id"
            :members="project.members || []"
            :task="editingTask"
            @close="closeDialog"
        />

        <RejectDialog :task="rejectingTask" @close="rejectingTask = null" />
    </div>
</template>

<style scoped>
.project-page {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.25rem;
}

.back {
    font-size: 0.85rem;
    opacity: 0.75;
    text-decoration: none;
    color: inherit;
}

.back:hover {
    text-decoration: underline;
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.project-name {
    margin: 0;
    font-size: 1.6rem;
}

.project-description {
    margin: 0.4rem 0;
    max-width: 48rem;
    overflow-wrap: anywhere;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.section-title {
    margin: 0 0 0.75rem;
    font-size: 1.15rem;
}
</style>
