<script setup>
// "My tasks" strip: the logged-in user's own task counts and their next due task.
import { computed } from 'vue';
import { formatDate, myTasks } from '@/lib/projectHelpers';

const props = defineProps({
    tasks: { type: Array, default: () => [] },
    userId: { type: Number, default: null },
});

const mine = computed(() => myTasks(props.tasks, props.userId));
</script>

<template>
    <section class="pm-panel strip">
        <h2 class="title">My tasks</h2>

        <p v-if="mine.total === 0" class="pm-muted">
            No tasks are assigned to you in this project.
        </p>

        <template v-else>
            <ul class="counts">
                <li>
                    <strong>{{ mine.counts.todo }}</strong> To do
                </li>
                <li>
                    <strong>{{ mine.counts.in_progress }}</strong> In progress
                </li>
                <li>
                    <strong>{{ mine.counts.finished }}</strong> Finished
                </li>
                <li>
                    <strong>{{ mine.counts.approved }}</strong> Approved
                </li>
            </ul>

            <p class="next">
                <template v-if="mine.next">
                    Next due: <strong>{{ mine.next.title }}</strong> on
                    {{ formatDate(mine.next.due_date) }}
                </template>
                <template v-else>Nothing due soon.</template>
            </p>
        </template>
    </section>
</template>

<style scoped>
.title {
    margin: 0 0 0.6rem;
    font-size: 1.05rem;
}

.counts {
    list-style: none;
    margin: 0 0 0.6rem;
    padding: 0;
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 1.25rem;
    font-size: 0.9rem;
}

.next {
    margin: 0;
    font-size: 0.88rem;
}
</style>
