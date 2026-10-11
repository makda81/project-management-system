<script setup>
// Members panel: shows who is in the project. Managers can add and remove members.
// Data in:  project.members [{ id, name, email }], availableUsers [{ id, name, email }]
// Requests: POST /projects/{id}/members { user_id }   DELETE /projects/{id}/members/{userId}
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { firstError } from '@/lib/projectHelpers';

const props = defineProps({
    project: { type: Object, required: true },
    availableUsers: { type: Array, default: () => [] },
    isManager: { type: Boolean, default: false },
});

const form = useForm({ user_id: '' });

// Do not offer people who are already members.
const choices = computed(() => {
    const memberIds = new Set(
        (props.project.members || []).map((m) => Number(m.id)),
    );

    return (props.availableUsers || []).filter(
        (u) => !memberIds.has(Number(u.id)),
    );
});

function addMember() {
    form.post(`/projects/${props.project.id}/members`, {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}

function removeMember(member) {
    if (!window.confirm(`Remove ${member.name} from this project?`)) {
        return;
    }

    router.delete(`/projects/${props.project.id}/members/${member.id}`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <section class="pm-panel members">
        <h2 class="members-title">
            Members ({{ project.members?.length ?? 0 }})
        </h2>

        <p
            v-if="!project.members || project.members.length === 0"
            class="pm-muted"
        >
            No members yet.
        </p>

        <ul v-else class="members-list">
            <li
                v-for="member in project.members"
                :key="member.id"
                class="member"
            >
                <div>
                    <strong>{{ member.name }}</strong>
                    <div class="pm-muted">{{ member.email }}</div>
                </div>
                <button
                    v-if="isManager"
                    type="button"
                    class="pm-btn pm-btn-small pm-btn-danger"
                    @click="removeMember(member)"
                >
                    Remove
                </button>
            </li>
        </ul>

        <form v-if="isManager" class="add-form" @submit.prevent="addMember">
            <div class="pm-field">
                <label for="member-select">Add a member</label>
                <select
                    id="member-select"
                    v-model="form.user_id"
                    class="pm-input"
                    :disabled="choices.length === 0"
                >
                    <option value="" disabled>
                        {{
                            choices.length === 0
                                ? 'Everyone is already a member'
                                : 'Choose a user'
                        }}
                    </option>
                    <option
                        v-for="user in choices"
                        :key="user.id"
                        :value="user.id"
                    >
                        {{ user.name }} ({{ user.email }})
                    </option>
                </select>
                <p v-if="form.errors.user_id" class="pm-error">
                    {{ form.errors.user_id }}
                </p>
                <p v-else-if="firstError(form.errors)" class="pm-error">
                    {{ firstError(form.errors) }}
                </p>
            </div>
            <button
                type="submit"
                class="pm-btn pm-btn-primary"
                :disabled="form.processing || !form.user_id"
            >
                {{ form.processing ? 'Adding...' : 'Add member' }}
            </button>
        </form>
    </section>
</template>

<style scoped>
.members-title {
    margin: 0 0 0.75rem;
    font-size: 1.05rem;
}

.members-list {
    list-style: none;
    margin: 0 0 1rem;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}

.member {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
}

.add-form {
    border-top: 1px solid rgba(127, 127, 127, 0.3);
    padding-top: 1rem;
}
</style>
