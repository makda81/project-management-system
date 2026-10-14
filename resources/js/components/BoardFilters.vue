<script setup>
// Filter bar above the board: search by title/description, filter by assignee and priority.
// It only changes the `modelValue` object; the page does the actual filtering.
import { hasActiveFilters, PRIORITIES } from '@/lib/projectHelpers';

const props = defineProps({
    modelValue: { type: Object, required: true }, // { query, assignee, priority }
    members: { type: Array, default: () => [] },
    shown: { type: Number, default: 0 },
    total: { type: Number, default: 0 },
});

const emit = defineEmits(['update:modelValue']);

function update(field, value) {
    emit('update:modelValue', { ...props.modelValue, [field]: value });
}

function clearAll() {
    emit('update:modelValue', { query: '', assignee: '', priority: '' });
}
</script>

<template>
    <div class="filters">
        <input
            class="pm-input search"
            type="search"
            placeholder="Search tasks..."
            aria-label="Search tasks"
            :value="modelValue.query"
            @input="update('query', $event.target.value)"
        />

        <select
            class="pm-input"
            aria-label="Filter by assignee"
            :value="modelValue.assignee"
            @change="update('assignee', $event.target.value)"
        >
            <option value="">All assignees</option>
            <option value="unassigned">Unassigned</option>
            <option
                v-for="member in members"
                :key="member.id"
                :value="String(member.id)"
            >
                {{ member.name }}
            </option>
        </select>

        <select
            class="pm-input"
            aria-label="Filter by priority"
            :value="modelValue.priority"
            @change="update('priority', $event.target.value)"
        >
            <option value="">All priorities</option>
            <option v-for="p in PRIORITIES" :key="p.value" :value="p.value">
                {{ p.label }}
            </option>
        </select>

        <button
            v-if="hasActiveFilters(modelValue)"
            type="button"
            class="pm-btn pm-btn-small"
            @click="clearAll"
        >
            Clear filters
        </button>

        <span class="pm-muted count"
            >Showing {{ shown }} of {{ total }} tasks</span
        >
    </div>
</template>

<style scoped>
.filters {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.6rem;
    margin-bottom: 0.9rem;
}

.filters .pm-input {
    width: auto;
}

.search {
    min-width: 12rem;
    flex: 1 1 12rem;
    max-width: 20rem;
}

.count {
    margin-left: auto;
}
</style>
