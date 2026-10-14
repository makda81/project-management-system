<script setup>
// One task card on the board (Trello-style). It only displays data and tells the board what the user clicked.
import MemberAvatar from '@/components/MemberAvatar.vue';
import { formatDate, isOverdue, memberName } from '@/lib/projectHelpers';

const props = defineProps({
    task: { type: Object, required: true },
    members: { type: Array, default: () => [] },
    canDrag: { type: Boolean, default: false },
    isManager: { type: Boolean, default: false },
    dragging: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'approve', 'reject', 'dragstart', 'dragend']);

// "Due Oct 20, 2026", "Due Oct 20, 2026 (overdue)" or "No due date"
function dueText(task) {
    if (!task.due_date) {
        return 'No due date';
    }

    return `Due ${formatDate(task.due_date)}${isOverdue(task) ? ' (overdue)' : ''}`;
}

// Empty name makes the avatar show "?" for unassigned tasks.
function assigneeName(task) {
    const name = memberName(props.members, task.assigned_to);

    return name === 'Unassigned' ? '' : name;
}
</script>

<template>
    <article
        class="card"
        :class="[
            `stripe-${task.priority}`,
            {
                locked: task.status === 'approved',
                dragging,
                draggable: canDrag,
            },
        ]"
        :data-task-id="task.id"
        :draggable="canDrag"
        @dragstart="emit('dragstart', $event, task)"
        @dragend="emit('dragend', $event, task)"
    >
        <span class="pm-badge priority" :class="`pm-badge-${task.priority}`">{{
            task.priority
        }}</span>

        <strong class="card-title">{{ task.title }}</strong>

        <p v-if="task.description" class="card-description">
            {{ task.description }}
        </p>

        <p v-if="task.review_comment" class="review">
            <strong>Review comment:</strong> {{ task.review_comment }}
        </p>

        <footer class="card-bottom">
            <span class="due" :class="{ overdue: isOverdue(task) }">{{
                dueText(task)
            }}</span>
            <MemberAvatar :name="assigneeName(task)" />
        </footer>

        <p v-if="task.status === 'approved'" class="locked-note">
            Approved and locked
        </p>

        <div v-if="isManager" class="card-actions">
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
        </div>
    </article>
</template>

<style scoped>
.card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.45rem;
    padding: 0.7rem 0.8rem;
    border-radius: 0.6rem;
    background: var(--card, Canvas);
    color: var(--card-foreground, CanvasText);
    border: 1px solid rgba(127, 127, 127, 0.25);
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
    transition:
        box-shadow 0.15s ease,
        transform 0.15s ease;
}

.card.stripe-low {
    border-left: 5px solid #22c55e;
}

.card.stripe-medium {
    border-left: 5px solid #f59e0b;
}

.card.stripe-high {
    border-left: 5px solid #ef4444;
}

.card.draggable {
    cursor: grab;
}

.card.draggable:hover {
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.25);
}

.card.dragging {
    opacity: 0.45;
    transform: rotate(2deg);
}

.card.locked {
    border-style: dashed;
}

.card-title {
    font-size: 0.92rem;
    overflow-wrap: anywhere;
}

.card-description {
    margin: 0;
    font-size: 0.8rem;
    opacity: 0.75;
    overflow-wrap: anywhere;
}

.review {
    margin: 0;
    width: 100%;
    box-sizing: border-box;
    padding: 0.4rem 0.5rem;
    border-radius: 0.4rem;
    background: rgba(220, 38, 38, 0.12);
    color: #dc2626;
    font-size: 0.78rem;
    overflow-wrap: anywhere;
}

.card-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}

.due {
    font-size: 0.75rem;
    padding: 0.1rem 0.45rem;
    border-radius: 0.35rem;
    background: rgba(127, 127, 127, 0.15);
}

.due.overdue {
    background: rgba(220, 38, 38, 0.15);
    color: #dc2626;
    font-weight: 600;
}

.locked-note {
    margin: 0;
    font-size: 0.75rem;
    opacity: 0.6;
}

.card-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
}
</style>
