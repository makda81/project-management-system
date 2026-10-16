<script setup>
// Team page (manager only): add a new user and see every user in the system.
// Props from Laravel: users[] { id, name, email, role, created_at }
// Request: POST /users { name, email, password, role }
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import BrandLayout from '@/components/BrandLayout.vue';
import '@/styles/pm.css';

defineOptions({ layout: BrandLayout });

defineProps({
    users: { type: Array, default: () => [] },
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: 'member',
});

const showPassword = ref(false);
const justAdded = ref('');

function generatePassword() {
    const chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    const bytes = new Uint32Array(12);

    crypto.getRandomValues(bytes);

    form.password = Array.from(bytes, (n) => chars[n % chars.length]).join('');
    showPassword.value = true;
}

function addUser() {
    const name = form.name;

    justAdded.value = '';

    form.post('/users', {
        preserveScroll: true,
        onSuccess: () => {
            justAdded.value = name;
            form.reset();
            showPassword.value = false;
        },
    });
}
</script>

<template>
    <Head title="Team" />

    <div class="team-page">
        <header>
            <h1 class="page-title">Team</h1>
            <p class="pm-muted">
                Add people to the system. New members can log in right away and
                be added to projects.
            </p>
        </header>

        <div class="layout">
            <section class="pm-surface add-card">
                <h2 class="card-title">Add a user</h2>

                <p v-if="justAdded" class="added" role="status">
                    {{ justAdded }} was added. Share the password with them
                    securely.
                </p>

                <form @submit.prevent="addUser">
                    <div class="pm-field">
                        <label for="user-name">Full name</label>
                        <input
                            id="user-name"
                            v-model="form.name"
                            class="pm-input"
                            type="text"
                            maxlength="255"
                            autocomplete="off"
                        />
                        <p v-if="form.errors.name" class="pm-error">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="pm-field">
                        <label for="user-email">Email</label>
                        <input
                            id="user-email"
                            v-model="form.email"
                            class="pm-input"
                            type="email"
                            maxlength="255"
                            autocomplete="off"
                        />
                        <p v-if="form.errors.email" class="pm-error">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="pm-field">
                        <label for="user-password">Password</label>
                        <input
                            id="user-password"
                            v-model="form.password"
                            class="pm-input"
                            :type="showPassword ? 'text' : 'password'"
                            autocomplete="new-password"
                        />
                        <div class="pw-actions">
                            <button
                                type="button"
                                class="pm-btn pm-btn-small"
                                @click="showPassword = !showPassword"
                            >
                                {{ showPassword ? 'Hide' : 'Show' }}
                            </button>
                            <button
                                type="button"
                                class="pm-btn pm-btn-small"
                                @click="generatePassword"
                            >
                                Generate
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="pm-error">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="pm-field">
                        <label for="user-role">Role</label>
                        <select
                            id="user-role"
                            v-model="form.role"
                            class="pm-input"
                        >
                            <option value="member">Member</option>
                            <option value="manager">Project manager</option>
                        </select>
                        <p v-if="form.errors.role" class="pm-error">
                            {{ form.errors.role }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="pm-btn pm-btn-primary"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Adding...' : 'Add user' }}
                    </button>
                </form>
            </section>

            <section class="pm-surface list-card">
                <h2 class="card-title">
                    All users
                    <span class="count">{{ users.length }}</span>
                </h2>

                <ul class="user-list">
                    <li v-for="u in users" :key="u.id" class="user-row">
                        <span class="avatar">{{
                            (u.name || '?').charAt(0).toUpperCase()
                        }}</span>
                        <span class="info">
                            <strong>{{ u.name }}</strong>
                            <small class="pm-muted">{{ u.email }}</small>
                        </span>
                        <span
                            class="role"
                            :class="{ manager: u.role === 'manager' }"
                            >{{
                                u.role === 'manager' ? 'Manager' : 'Member'
                            }}</span
                        >
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>

<style scoped>
.team-page {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    padding: 1.5rem 1.25rem;
}

.page-title {
    margin: 0 0 0.25rem;
    font-size: 1.6rem;
}

.layout {
    display: grid;
    grid-template-columns: minmax(18rem, 24rem) minmax(0, 1fr);
    gap: 1.25rem;
    align-items: start;
}

.card-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 0 1rem;
    font-size: 1.1rem;
}

.count {
    padding: 0.05rem 0.55rem;
    border-radius: 999px;
    background: var(--muted);
    color: var(--muted-foreground);
    font-size: 0.75rem;
    font-weight: 600;
}

.added {
    margin: 0 0 1rem;
    padding: 0.55rem 0.75rem;
    border-radius: 0.5rem;
    background: color-mix(in srgb, #16a34a 14%, transparent);
    color: #16a34a;
    font-size: 0.85rem;
}

.pw-actions {
    display: flex;
    gap: 0.4rem;
}

.user-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
}

.user-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.7rem 0;
    border-bottom: 1px solid var(--border);
}

.user-row:last-child {
    border-bottom: none;
}

.avatar {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.2rem;
    height: 2.2rem;
    border-radius: 999px;
    background: linear-gradient(135deg, #7c3aed, #2563eb);
    color: #ffffff;
    font-weight: 700;
}

.info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

.info strong,
.info small {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.role {
    padding: 0.15rem 0.6rem;
    border-radius: 999px;
    background: var(--muted);
    color: var(--muted-foreground);
    font-size: 0.75rem;
    font-weight: 600;
}

.role.manager {
    background: var(--accent);
    color: var(--accent-foreground);
}

@media (max-width: 860px) {
    .layout {
        grid-template-columns: 1fr;
    }
}
</style>
