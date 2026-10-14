<script setup>
// Edit project dialog. Managers only.
// Request: PUT /projects/{id} { name, description, start_date, due_date }
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { toDateInput } from '@/lib/projectHelpers';

const props = defineProps({
    open: { type: Boolean, default: false },
    project: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    description: '',
    start_date: '',
    due_date: '',
});

// Fill the form with the current project values every time the dialog opens.
watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.clearErrors();
        form.name = props.project.name ?? '';
        form.description = props.project.description ?? '';
        form.start_date = toDateInput(props.project.start_date);
        form.due_date = toDateInput(props.project.due_date);
    },
    { immediate: true },
);

function submit() {
    form.transform((data) => ({
        ...data,
        description: data.description || null,
        start_date: data.start_date || null,
        due_date: data.due_date || null,
    }));

    form.put(`/projects/${props.project.id}`, {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    });
}
</script>

<template>
    <div v-if="open" class="overlay" @click.self="emit('close')">
        <form class="dialog" @submit.prevent="submit">
            <h2 class="dialog-title">Edit project</h2>

            <div class="pm-field">
                <label for="project-name">Name</label>
                <input
                    id="project-name"
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
                <label for="project-description">Description</label>
                <textarea
                    id="project-description"
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
                    <label for="project-start">Start date</label>
                    <input
                        id="project-start"
                        v-model="form.start_date"
                        class="pm-input"
                        type="date"
                    />
                    <p v-if="form.errors.start_date" class="pm-error">
                        {{ form.errors.start_date }}
                    </p>
                </div>

                <div class="pm-field">
                    <label for="project-due">Due date</label>
                    <input
                        id="project-due"
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
                <button type="button" class="pm-btn" @click="emit('close')">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="pm-btn pm-btn-primary"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Saving...' : 'Save changes' }}
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
    max-width: 32rem;
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
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

@media (max-width: 520px) {
    .row {
        grid-template-columns: 1fr;
    }
}
</style>
