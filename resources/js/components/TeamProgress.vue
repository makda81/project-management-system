<script setup>
// Team progress: one row per member showing how many of their tasks are approved.
import { computed } from 'vue';
import MemberAvatar from '@/components/MemberAvatar.vue';
import { memberProgress } from '@/lib/projectHelpers';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    members: { type: Array, default: () => [] },
});

const rows = computed(() => memberProgress(props.tasks, props.members));
</script>

<template>
    <section class="pm-panel">
        <h2 class="title">Team progress</h2>

        <p v-if="rows.length === 0" class="pm-muted">
            Add members to see their progress.
        </p>

        <ul v-else class="rows">
            <li v-for="row in rows" :key="row.id ?? 'unassigned'" class="row">
                <MemberAvatar :name="row.id === null ? '' : row.name" />
                <div class="info">
                    <div class="line">
                        <strong>{{ row.name }}</strong>
                        <span class="pm-muted"
                            >{{ row.approved }} of
                            {{ row.total }} approved</span
                        >
                    </div>
                    <div
                        class="pm-bar small"
                        role="progressbar"
                        :aria-valuenow="row.percent"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div
                            class="pm-bar-fill"
                            :style="{ width: `${row.percent}%` }"
                        />
                    </div>
                </div>
            </li>
        </ul>
    </section>
</template>

<style scoped>
.title {
    margin: 0 0 0.75rem;
    font-size: 1.05rem;
}

.rows {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.row {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.info {
    flex: 1;
    min-width: 0;
}

.line {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
    font-size: 0.88rem;
}

.small {
    height: 0.4rem;
}
</style>
