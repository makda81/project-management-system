<script setup>
// The four-column board with drag and drop.
//
// Drag and drop flow:
//   1. The user drags a card and drops it in a column (browser drag events).
//   2. We move the card on screen right away (optimistic update) and remember a snapshot.
//   3. Inertia sends PATCH /tasks/{id}/move { status, position } to Laravel.
//   4. Laravel checks permission and saves. It redirects back with fresh tasks.
//   5. If Laravel says no (error), we restore the snapshot (rollback) and show the message.
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TaskCard from '@/components/TaskCard.vue';
import {
    STATUS_COLUMNS,
    applyMove,
    firstError,
    groupTasks,
} from '@/lib/projectHelpers';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
    authUserId: { type: Number, default: null },
    isManager: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'reject']);

const columns = ref(groupTasks(props.tasks));
const draggingId = ref(null);
const overStatus = ref(null);
const errorMessage = ref('');
const busy = ref(false);

// When Laravel sends fresh tasks (after any request), rebuild the board.
watch(
    () => props.tasks,
    (tasks) => {
        columns.value = groupTasks(tasks);
    },
    { deep: true },
);

// Managers can drag any unlocked task. Members only their own. Approved tasks are locked.
function canDrag(task) {
    if (busy.value || task.status === 'approved') {
        return false;
    }

    return (
        props.isManager || Number(task.assigned_to) === Number(props.authUserId)
    );
}

function onDragStart(event, task) {
    draggingId.value = task.id;
    errorMessage.value = '';
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(task.id));
}

function onDragEnd() {
    draggingId.value = null;
    overStatus.value = null;
}

function onDragOver(event, column) {
    if (!column.acceptsDrops || draggingId.value === null) {
        return;
    }

    event.preventDefault(); // tells the browser "dropping here is allowed"
    event.dataTransfer.dropEffect = 'move';
    overStatus.value = column.status;
}

// Which slot in the column was the card dropped on? Count the cards whose middle is above the mouse.
function dropIndex(event, columnEl) {
    const cards = [...columnEl.querySelectorAll('[data-task-id]')];
    let index = 0;

    for (const card of cards) {
        const box = card.getBoundingClientRect();

        if (event.clientY > box.top + box.height / 2) {
            index += 1;
        }
    }

    return index;
}

function onDrop(event, column) {
    event.preventDefault();

    const taskId = draggingId.value;

    draggingId.value = null;
    overStatus.value = null;

    if (taskId === null || !column.acceptsDrops) {
        return;
    }

    const snapshot = JSON.parse(JSON.stringify(columns.value));
    const index = dropIndex(event, event.currentTarget);
    const result = applyMove(columns.value, taskId, column.status, index);

    if (result === null) {
        return; // dropped back where it was
    }

    busy.value = true;
    errorMessage.value = '';

    router.patch(
        `/tasks/${taskId}/move`,
        { status: column.status, position: result.position },
        {
            preserveScroll: true,
            onError: (errors) => {
                columns.value = snapshot; // rollback
                errorMessage.value =
                    firstError(errors) || 'The task could not be moved.';
            },
            onFinish: () => {
                busy.value = false;
            },
        },
    );
}

// Approve is a plain button, no drag needed.
function approve(task) {
    errorMessage.value = '';
    busy.value = true;

    router.post(
        `/tasks/${task.id}/approve`,
        {},
        {
            preserveScroll: true,
            onError: (errors) => {
                errorMessage.value =
                    firstError(errors) || 'The task could not be approved.';
            },
            onFinish: () => {
                busy.value = false;
            },
        },
    );
}
</script>

<template>
    <div>
        <p v-if="errorMessage" class="board-error" role="alert">
            {{ errorMessage }}
            <button
                type="button"
                class="pm-btn pm-btn-small"
                @click="errorMessage = ''"
            >
                Dismiss
            </button>
        </p>

        <div class="board">
            <section
                v-for="column in STATUS_COLUMNS"
                :key="column.status"
                class="column"
                :class="{
                    over: overStatus === column.status,
                    readonly: !column.acceptsDrops,
                }"
                @dragover="onDragOver($event, column)"
                @dragleave="overStatus = null"
                @drop="onDrop($event, column)"
            >
                <h3 class="column-title">
                    {{ column.title }}
                    <span class="count">{{
                        columns[column.status].length
                    }}</span>
                </h3>

                <div class="column-body">
                    <TaskCard
                        v-for="task in columns[column.status]"
                        :key="task.id"
                        :task="task"
                        :members="members"
                        :can-drag="canDrag(task)"
                        :is-manager="isManager"
                        :dragging="draggingId === task.id"
                        @dragstart="onDragStart"
                        @dragend="onDragEnd"
                        @edit="emit('edit', $event)"
                        @approve="approve"
                        @reject="emit('reject', $event)"
                    />

                    <p v-if="columns[column.status].length === 0" class="empty">
                        No tasks
                    </p>
                </div>
            </section>
        </div>
    </div>
</template>

<style scoped>
.board {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.9rem;
    align-items: start;
}

.column {
    border: 1px solid rgba(127, 127, 127, 0.3);
    border-radius: 0.75rem;
    padding: 0.75rem;
    min-height: 8rem;
    background: rgba(127, 127, 127, 0.04);
}

.column.over {
    outline: 2px dashed #2563eb;
    outline-offset: -2px;
}

.column.readonly {
    background: rgba(22, 163, 74, 0.05);
}

.column-title {
    margin: 0 0 0.7rem;
    font-size: 0.95rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.count {
    font-size: 0.75rem;
    padding: 0.05rem 0.5rem;
    border-radius: 999px;
    background: rgba(127, 127, 127, 0.2);
}

.column-body {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    min-height: 4rem;
}

.empty {
    margin: 0;
    padding: 1rem 0;
    text-align: center;
    font-size: 0.8rem;
    opacity: 0.55;
}

.board-error {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    margin: 0 0 0.9rem;
    padding: 0.6rem 0.8rem;
    border-radius: 0.5rem;
    background: rgba(220, 38, 38, 0.14);
    color: #dc2626;
    font-size: 0.875rem;
}

@media (max-width: 1100px) {
    .board {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 640px) {
    .board {
        grid-template-columns: 1fr;
    }
}
</style>
