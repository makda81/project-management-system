<script setup>
// One task card on the board. It only displays data and tells the board what the user clicked.
import { formatDate, isOverdue, memberName } from '@/lib/projectHelpers';

defineProps({
    task: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    canDrag: { type: Boolean, default: false },
    isManager: { type: Boolean, default: false },
    dragging: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'approve', 'reject', 'dragstart', 'dragend']);
</script>

<template>
    <article
        class="card"
        :class="{
            locked: task.status === 'approved',
            dragging,
            draggable: canDrag,
        }"
        :data-task-id="task.id"
        :draggable="canDrag"
        @dragstart="emit('dragstart', $event, task)"
        @dragend="emit('dragend', $event, task)"
    >
        <header class="card-top">
            <strong class="card-title">{{ task.title }}</strong>
            <span class="pm-badge" :class="`pm-badge-${task.priority}`">{{
                task.priority
            }}</span>
        </header>

        <p v-if="task.description" class="card-description">
            {{ task.description }}
        </p>

        <dl class="card-meta">
            <div>
                <dt>Assignee</dt>
                <dd>{{ memberName(members, task.assigned_to) }}</dd>
            </div>
            <div>
                <dt>Due</dt>
                <dd :class="{ overdue: isOverdue(task) }">
                    {{ formatDate(task.due_date)
                    }}<span v-if="isOverdue(task)"> (overdue)</span>
                </dd>
            </div>
        </dl>

        <p v-if="task.review_comment" class="review">
            <strong>Review comment:</strong> {{ task.review_comment }}
        </p>

        <p v-if="task.status === 'approved'" class="pm-muted">
            Approved and locked
        </p>

        <footer v-if="isManager" class="card-actions">
            <template v-if="task.status === 'finished'">
                <button
                    type="button"
                    class="pm-btn pm-btn-small pm-btn-success"
                    @click="emit('approve', task)"
                >
                    Approve
                </button>
                <button
                    type="button"
                    class="pm-btn pm-btn-small pm-btn-danger"
                    @click="emit('reject', task)"
                >
                    Reject
                </button>
            </template>
            <button
                v-if="task.status !== 'approved'"
                type="button"
                class="pm-btn pm-btn-small"
                @click="emit('edit', task)"
            >
                Edit
            </button>
        </footer>
    </article>
</template>

<style scoped>
.card {
    padding: 0.7rem 0.8rem;
    border-radius: 0.6rem;
    border: 1px solid rgba(127, 127, 127, 0.35);
    background: rgba(127, 127, 127, 0.07);
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.card.draggable {
    cursor: grab;
}

.card.dragging {
    opacity: 0.4;
}

.card.locked {
    border-style: dashed;
}

.card-top {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.5rem;
}

.card-title {
    overflow-wrap: anywhere;
}

.card-description {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.8;
    overflow-wrap: anywhere;
}

.card-meta {
    margin: 0;
    display: flex;
    gap: 1rem;
    font-size: 0.8rem;
}

.card-meta dt {
    opacity: 0.6;
}

.card-meta dd {
    margin: 0;
}

.overdue {
    color: #dc2626;
    font-weight: 600;
}

.review {
    margin: 0;
    padding: 0.4rem 0.5rem;
    border-radius: 0.4rem;
    background: rgba(220, 38, 38, 0.12);
    font-size: 0.8rem;
    overflow-wrap: anywhere;
}

.card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
</style>
