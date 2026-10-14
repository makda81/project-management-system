<script setup>
// Task dialog: create a task (task = null) or edit one (task = {...}). Managers only.
// Requests: POST /projects/{id}/tasks      PUT /tasks/{id}      DELETE /tasks/{id}
import { router, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { PRIORITIES, toDateInput } from '@/lib/projectHelpers';

const props = defineProps({
    open: { type: Boolean, default: false },
    projectId: { type: Number, required: true },
    members: { type: Array, default: () => [] },
    task: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const isEdit = computed(() => props.task !== null);

const form = useForm({
    title: '',
    description: '',
    assigned_to: '',
    priority: 'medium',
    start_date: '',
    due_date: '',
});

// Fill the form every time the dialog opens (empty for create, task values for edit).
watch(
    () => [props.open, props.task],
    () => {
        if (!props.open) {
            return;
        }

        form.clearErrors();
        form.title = props.task?.title ?? '';
        form.description = props.task?.description ?? '';
        form.assigned_to = props.task?.assigned_to ?? '';
        form.priority = props.task?.priority ?? 'medium';
        form.start_date = toDateInput(props.task?.start_date);
        form.due_date = toDateInput(props.task?.due_date);
    },
    { immediate: true },
);

function submit() {
    // Empty strings become null so the backend sees "no value".
    form.transform((data) => ({
        ...data,
        assigned_to: data.assigned_to === '' ? null : data.assigned_to,
        start_date: data.start_date || null,
        due_date: data.due_date || null,
        description: data.description || null,
    }));

    const options = {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    };

    if (isEdit.value) {
        form.put(`/tasks/${props.task.id}`, options);
    } else {
        form.post(`/projects/${props.projectId}/tasks`, options);
    }
}

function removeTask() {
    if (!window.confirm('Delete this task? This cannot be undone.')) {
        return;
    }

    router.delete(`/tasks/${props.task.id}`, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <div v-if="open" class="overlay" @click.self="emit('close')">
        <form class="dialog" @submit.prevent="submit">
            <h2 class="dialog-title">
                {{ isEdit ? 'Edit task' : 'New task' }}
            </h2>

            <div class="pm-field">
                <label for="task-title">Title</label>
                <input
                    id="task-title"
                    v-model="form.title"
                    class="pm-input"
                    type="text"
                    maxlength="255"
                />
                <p v-if="form.errors.title" class="pm-error">
                    {{ form.errors.title }}
                </p>
            </div>

            <div class="pm-field">
                <label for="task-description">Description</label>
                <textarea
                    id="task-description"
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
                    <label for="task-assignee">Assigned to</label>
                    <select
                        id="task-assignee"
                        v-model="form.assigned_to"
                        class="pm-input"
                    >
                        <option value="">Unassigned</option>
                        <option
                            v-for="member in members"
                            :key="member.id"
                            :value="member.id"
                        >
                            {{ member.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.assigned_to" class="pm-error">
                        {{ form.errors.assigned_to }}
                    </p>
                </div>

                <div class="pm-field">
                    <label for="task-priority">Priority</label>
                    <select
                        id="task-priority"
                        v-model="form.priority"
                        class="pm-input"
                    >
                        <option
                            v-for="p in PRIORITIES"
                            :key="p.value"
                            :value="p.value"
                        >
                            {{ p.label }}
                        </option>
                    </select>
                    <p v-if="form.errors.priority" class="pm-error">
                        {{ form.errors.priority }}
                    </p>
                </div>
            </div>

            <div class="row">
                <div class="pm-field">
                    <label for="task-start">Start date</label>
                    <input
                        id="task-start"
                        v-model="form.start_date"
                        class="pm-input"
                        type="date"
                    />
                    <p v-if="form.errors.start_date" class="pm-error">
                        {{ form.errors.start_date }}
                    </p>
                </div>

                <div class="pm-field">
                    <label for="task-due">Due date</label>
                    <input
                        id="task-due"
                        v-model="form.due_date"
                        class="pm-input"
                        type="date"
                    />
                    <p v-if="form.errors.due_date" class="pm-error">
                        {{ form.errors.due_date }}
                    </p>
                </div>
            </div>

            <div class="actions">
                <button
                    v-if="isEdit"
                    type="button"
                    class="pm-btn pm-btn-danger"
                    :disabled="form.processing"
                    @click="removeTask"
                >
                    Delete
                </button>
                <span class="spacer" />
                <button type="button" class="pm-btn" @click="emit('close')">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="pm-btn pm-btn-primary"
                    :disabled="form.processing"
                >
                    {{
                        form.processing
                            ? 'Saving...'
                            : isEdit
                              ? 'Save changes'
                              : 'Create task'
                    }}
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
.overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    background: rgba(0, 0, 0, 0.5);
}

.dialog {
    width: 100%;
    max-width: 34rem;
    max-height: 90vh;
    overflow-y: auto;
    box-sizing: border-box;
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(127, 127, 127, 0.4);
    background: var(--card, Canvas);
    color: var(--card-foreground, CanvasText);
}

.dialog-title {
    margin: 0 0 1rem;
    font-size: 1.15rem;
}

.row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

.actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.spacer {
    flex: 1;
}

@media (max-width: 520px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>
