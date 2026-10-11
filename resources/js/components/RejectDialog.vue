<script setup>
// Reject dialog: a manager rejects a Finished task and must write a comment.
// Request: POST /tasks/{id}/reject { comment }
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps({
    task: { type: Object, default: null },
});

const emit = defineEmits(['close']);

const form = useForm({ comment: '' });

watch(
    () => props.task,
    () => {
        form.reset();
        form.clearErrors();
    },
);

function submit() {
    if (form.comment.trim() === '') {
        form.setError(
            'comment',
            'Please write a comment explaining the rejection.',
        );

        return;
    }

    form.post(`/tasks/${props.task.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
}
</script>

<template>
    <div v-if="task" class="overlay" @click.self="emit('close')">
        <form class="dialog" @submit.prevent="submit">
            <h2 class="dialog-title">Reject task</h2>
            <p class="pm-muted">
                "{{ task.title }}" will go back to the member with your comment.
            </p>

            <div class="pm-field">
                <label for="reject-comment">Comment (required)</label>
                <textarea
                    id="reject-comment"
                    v-model="form.comment"
                    class="pm-input"
                    rows="4"
                />
                <p v-if="form.errors.comment" class="pm-error">
                    {{ form.errors.comment }}
                </p>
            </div>

            <div class="actions">
                <button type="button" class="pm-btn" @click="emit('close')">
                    Cancel
                </button>
                <button
                    type="submit"
                    class="pm-btn pm-btn-danger"
                    :disabled="form.processing"
                >
                    {{ form.processing ? 'Rejecting...' : 'Reject task' }}
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
    max-width: 28rem;
    box-sizing: border-box;
    padding: 1.25rem;
    border-radius: 0.75rem;
    border: 1px solid rgba(127, 127, 127, 0.4);
    background: Canvas;
    color: CanvasText;
}

.dialog-title {
    margin: 0 0 0.5rem;
    font-size: 1.15rem;
}

.actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>
