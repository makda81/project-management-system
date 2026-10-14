<script setup>
// The Trello-style four-column board with drag and drop.
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
    // True while a filter is on: hidden cards would make the drop position wrong, so dragging is paused.
    dragLocked: { type: Boolean, default: false },
});

const emit = defineEmits(['edit', 'reject', 'create']);

// One accent color per column (the colored strip on top of each list).
const ACCENTS = {
    todo: '#6366f1',
    in_progress: '#d97706',
    finished: '#0891b2',
    approved: '#16a34a',
};

const EMPTY_TEXT = {
    todo: 'Drop tasks here',
    in_progress: 'Drop tasks here',
    finished: 'Drop tasks here',
    approved: 'No approved tasks yet',
};

const columns = ref(groupTasks(props.tasks));
const draggingId = ref(null);
const overStatus = ref(null);
const overIndex = ref(0);
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
    if (busy.value || props.dragLocked || task.status === 'approved') {
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

// The dashed drop placeholder goes after the last card when the mouse is below all cards.
function showEndPlaceholder(status) {
    return (
        overStatus.value === status &&
        overIndex.value >= columns.value[status].length
    );
}

// Which slot in the column is the mouse over? Count the cards whose middle is above the mouse.
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

function onDragOver(event, column) {
    if (!column.acceptsDrops || draggingId.value === null) {
        return;
    }

    event.preventDefault(); // tells the browser "dropping here is allowed"
    event.dataTransfer.dropEffect = 'move';
    overStatus.value = column.status;
    overIndex.value = dropIndex(event, event.currentTarget);
}

function onDragLeave(event) {
    // Ignore moves between children of the same column.
    if (!event.currentTarget.contains(event.relatedTarget)) {
        overStatus.value = null;
    }
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

        <p v-if="dragLocked" class="pm-muted lock-note">
            Dragging is paused while filters are on. Clear the filters to move
            cards.
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
                :style="{ '--accent': ACCENTS[column.status] }"
                @dragover="onDragOver($event, column)"
                @dragleave="onDragLeave"
                @drop="onDrop($event, column)"
            >
                <h3 class="column-title">
                    <span>{{ column.title }}</span>
                    <span class="count">{{
                        columns[column.status].length
                    }}</span>
                </h3>

                <div class="column-body">
                    <template
                        v-for="(task, i) in columns[column.status]"
                        :key="task.id"
                    >
                        <div
                            v-if="
                                overStatus === column.status && overIndex === i
                            "
                            class="placeholder"
                        />
                        <TaskCard
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
                    </template>

                    <div
                        v-if="showEndPlaceholder(column.status)"
                        class="placeholder"
                    />

                    <p
                        v-if="
                            columns[column.status].length === 0 &&
                            overStatus !== column.status
                        "
                        class="empty"
                    >
                        {{ EMPTY_TEXT[column.status] }}
                    </p>
                </div>

                <button
                    v-if="isManager && column.status === 'todo'"
                    type="button"
                    class="add-card"
                    @click="emit('create')"
                >
                    + Add a task
                </button>
            </section>
        </div>
    </div>
</template>

<style scoped>
.board {
    display: flex;
    align-items: flex-start;
    gap: 0.9rem;
    overflow-x: auto;
    padding-bottom: 0.75rem;
}

.column {
    flex: 1 0 17rem;
    min-width: 17rem;
    max-width: 24rem;
    box-sizing: border-box;
    padding: 0.7rem;
    border-radius: 0.8rem;
    background: #ebecf0;
    color: #1f2937;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.25);
}

.column.over {
    box-shadow: 0 0 0 2px var(--accent);
}

.column.readonly {
    background: #e3f5e9;
}

.column-title {
    margin: 0 0 0.7rem;
    padding: 0.45rem 0.7rem;
    border-radius: 0.55rem;
    background: var(--accent);
    color: #ffffff;
    font-size: 0.9rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.count {
    min-width: 1.4rem;
    text-align: center;
    font-size: 0.75rem;
    padding: 0.05rem 0.45rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.3);
    color: #fff;
}

.column-body {
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    min-height: 4rem;
    max-height: 70vh;
    overflow-y: auto;
}

.placeholder {
    height: 3.2rem;
    flex-shrink: 0;
    border-radius: 0.6rem;
    border: 2px dashed var(--accent);
    background: rgba(127, 127, 127, 0.1);
}

.empty {
    margin: 0;
    padding: 1.2rem 0;
    text-align: center;
    font-size: 0.8rem;
    opacity: 0.55;
}

.add-card {
    width: 100%;
    margin-top: 0.6rem;
    padding: 0.45rem 0.6rem;
    border: none;
    border-radius: 0.5rem;
    background: transparent;
    color: #475569;
    font: inherit;
    font-size: 0.85rem;
    text-align: left;
    cursor: pointer;
    opacity: 0.8;
}

.add-card:hover {
    background: rgba(15, 23, 42, 0.1);
    opacity: 1;
}

.lock-note {
    margin: 0 0 0.6rem;
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
</style>
