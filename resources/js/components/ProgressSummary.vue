<script setup>
// Project progress: one big bar (approved / all tasks) plus a tile for each status and for overdue tasks.
import { computed } from 'vue';
import { progressStats } from '@/lib/projectHelpers';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
});

const stats = computed(() => progressStats(props.tasks));

const tiles = computed(() => [
    {
        key: 'todo',
        label: 'To do',
        value: stats.value.counts.todo,
        color: '#6366f1',
    },
    {
        key: 'in_progress',
        label: 'In progress',
        value: stats.value.counts.in_progress,
        color: '#d97706',
    },
    {
        key: 'finished',
        label: 'Finished',
        value: stats.value.counts.finished,
        color: '#0891b2',
    },
    {
        key: 'approved',
        label: 'Approved',
        value: stats.value.counts.approved,
        color: '#16a34a',
    },
    {
        key: 'overdue',
        label: 'Overdue',
        value: stats.value.overdue,
        color: '#dc2626',
    },
]);
</script>

<template>
    <section class="pm-panel">
        <div class="head">
            <h2 class="title">Project progress</h2>
            <strong class="percent">{{ stats.percent }}%</strong>
        </div>

        <div
            class="pm-bar"
            role="progressbar"
            :aria-valuenow="stats.percent"
            aria-valuemin="0"
            aria-valuemax="100"
        >
            <div class="pm-bar-fill" :style="{ width: `${stats.percent}%` }" />
        </div>
        <p class="pm-muted caption">
            {{ stats.counts.approved }} of {{ stats.total }} tasks approved
        </p>

        <div class="tiles">
            <div
                v-for="tile in tiles"
                :key="tile.key"
                class="tile"
                :style="{ '--tile': tile.color }"
            >
                <span class="tile-value">{{ tile.value }}</span>
                <span class="tile-label">{{ tile.label }}</span>
            </div>
        </div>
    </section>
</template>

<style scoped>
.head {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 0.5rem;
}

.title {
    margin: 0;
    font-size: 1.05rem;
}

.percent {
    font-size: 1.4rem;
}

.caption {
    margin: 0.4rem 0 0.9rem;
}

.tiles {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(7rem, 1fr));
    gap: 0.6rem;
}

.tile {
    display: flex;
    flex-direction: column;
    gap: 0.1rem;
    padding: 0.6rem 0.75rem;
    border-radius: 0.6rem;
    border-left: 4px solid var(--tile);
    background: rgba(127, 127, 127, 0.1);
}

.tile-value {
    font-size: 1.3rem;
    font-weight: 700;
}

.tile-label {
    font-size: 0.78rem;
    opacity: 0.75;
}
</style>
