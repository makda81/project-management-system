// Small helpers for the project pages (plain JavaScript).
// Data shapes (from the contract):
//   project: { id, name, description, start_date, due_date, members[{id,name,email}], tasks[] }
//   task:    { id, project_id, assigned_to, title, description, status, priority,
//              start_date, due_date, position, finished_at, reviewed_by, approved_at, review_comment }
//   status:  todo | in_progress | finished | approved
//   priority: low | medium | high

export const STATUS_COLUMNS = [
    { status: 'todo', title: 'To do', acceptsDrops: true },
    { status: 'in_progress', title: 'In progress', acceptsDrops: true },
    { status: 'finished', title: 'Finished', acceptsDrops: true },
    { status: 'approved', title: 'Approved', acceptsDrops: false },
];

export const PRIORITIES = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
];

// "2026-10-20" or "2026-10-20T00:00:00Z" -> "Oct 20, 2026"
export function formatDate(value) {
    if (!value) {
        return 'No date';
    }

    const date = new Date(String(value).slice(0, 10) + 'T00:00:00');

    if (Number.isNaN(date.getTime())) {
        return String(value);
    }

    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
}

// Value for <input type="date"> (needs YYYY-MM-DD).
export function toDateInput(value) {
    return value ? String(value).slice(0, 10) : '';
}

// A task is overdue if its due date is before today and it is not finished yet.
export function isOverdue(task) {
    if (
        !task ||
        !task.due_date ||
        task.status === 'finished' ||
        task.status === 'approved'
    ) {
        return false;
    }

    const today = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time

    return String(task.due_date).slice(0, 10) < today;
}

// Laravel validation errors arrive as { field: "message" }. Return the first message.
export function firstError(errors) {
    const values = Object.values(errors || {});

    return values.length ? String(values[0]) : '';
}

// Look up the assignee's name in project.members.
export function memberName(members, id) {
    if (id === null || id === undefined || id === '') {
        return 'Unassigned';
    }

    const member = (members || []).find((m) => Number(m.id) === Number(id));

    return member ? member.name : 'Unassigned';
}

// Filter tasks for the board's filter bar.
// filters: { query: string, assignee: '' | 'unassigned' | <member id>, priority: '' | low | medium | high }
export function filterTasks(tasks, filters) {
    const query = String(filters?.query ?? '')
        .trim()
        .toLowerCase();
    const assignee = filters?.assignee ?? '';
    const priority = filters?.priority ?? '';

    return (tasks || []).filter((task) => {
        if (query) {
            const text =
                `${task.title ?? ''} ${task.description ?? ''}`.toLowerCase();

            if (!text.includes(query)) {
                return false;
            }
        }

        if (assignee === 'unassigned') {
            if (
                task.assigned_to !== null &&
                task.assigned_to !== undefined &&
                task.assigned_to !== ''
            ) {
                return false;
            }
        } else if (
            assignee !== '' &&
            Number(task.assigned_to) !== Number(assignee)
        ) {
            return false;
        }

        if (priority !== '' && task.priority !== priority) {
            return false;
        }

        return true;
    });
}

// True when any filter is switched on.
export function hasActiveFilters(filters) {
    return Boolean(
        String(filters?.query ?? '').trim() !== '' ||
        (filters?.assignee ?? '') !== '' ||
        (filters?.priority ?? '') !== '',
    );
}

// Split a flat tasks[] into the four board columns, ordered by position.
export function groupTasks(tasks) {
    const groups = { todo: [], in_progress: [], finished: [], approved: [] };

    for (const task of tasks || []) {
        (groups[task.status] ?? groups.todo).push({ ...task });
    }

    for (const key of Object.keys(groups)) {
        groups[key].sort(
            (a, b) => (a.position ?? 0) - (b.position ?? 0) || a.id - b.id,
        );
    }

    return groups;
}

// Move a task inside the local board state (used before the request is sent).
// `index` is the slot in the target column where the card was dropped, counted
// before the card is removed from its old place.
// Returns { position } (0-based index in the target column), or null if nothing changed.
export function applyMove(columns, taskId, status, index) {
    let fromStatus = null;
    let oldIndex = -1;

    for (const key of Object.keys(columns)) {
        const found = columns[key].findIndex((t) => t.id === taskId);

        if (found !== -1) {
            fromStatus = key;
            oldIndex = found;
            break;
        }
    }

    if (fromStatus === null || !columns[status]) {
        return null;
    }

    const sameColumn = fromStatus === status;
    let target = index;

    if (sameColumn && oldIndex < index) {
        target -= 1;
    }

    target = Math.max(
        0,
        Math.min(target, columns[status].length - (sameColumn ? 1 : 0)),
    );

    if (sameColumn && target === oldIndex) {
        return null;
    }

    const [task] = columns[fromStatus].splice(oldIndex, 1);
    task.status = status;
    columns[status].splice(target, 0, task);

    return { position: target };
}

// ---------- Progress helpers (all computed from the tasks already on the page) ----------

// Round a ratio to a whole percent. 0 tasks -> 0 (never NaN).
export function percent(part, total) {
    return total > 0 ? Math.round((part / total) * 100) : 0;
}

// Whole-project numbers: counts per status, overdue count, and % of tasks that are approved.
export function progressStats(tasks) {
    const counts = { todo: 0, in_progress: 0, finished: 0, approved: 0 };
    let overdue = 0;

    for (const task of tasks || []) {
        counts[task.status in counts ? task.status : 'todo'] += 1;

        if (isOverdue(task)) {
            overdue += 1;
        }
    }

    const total =
        counts.todo + counts.in_progress + counts.finished + counts.approved;

    return { total, counts, overdue, percent: percent(counts.approved, total) };
}

// One row per member (plus "Unassigned" when needed): how many of their tasks are approved.
export function memberProgress(tasks, members) {
    const rows = (members || []).map((m) => ({
        id: m.id,
        name: m.name,
        total: 0,
        approved: 0,
        percent: 0,
    }));
    const unassigned = {
        id: null,
        name: 'Unassigned',
        total: 0,
        approved: 0,
        percent: 0,
    };

    for (const task of tasks || []) {
        const row =
            rows.find(
                (r) =>
                    task.assigned_to !== null &&
                    task.assigned_to !== undefined &&
                    Number(r.id) === Number(task.assigned_to),
            ) ?? unassigned;

        row.total += 1;

        if (task.status === 'approved') {
            row.approved += 1;
        }
    }

    if (unassigned.total > 0) {
        rows.push(unassigned);
    }

    for (const row of rows) {
        row.percent = percent(row.approved, row.total);
    }

    return rows;
}

// The logged-in user's own tasks: counts per status and the next task that is due.
export function myTasks(tasks, userId) {
    const mine = (tasks || []).filter(
        (t) => userId !== null && Number(t.assigned_to) === Number(userId),
    );
    const stats = progressStats(mine);
    const open = mine
        .filter((t) => t.status === 'todo' || t.status === 'in_progress')
        .filter((t) => t.due_date)
        .sort((a, b) =>
            String(a.due_date)
                .slice(0, 10)
                .localeCompare(String(b.due_date).slice(0, 10)),
        );

    return { ...stats, next: open[0] ?? null };
}

// "Abel Tesfaye" -> "AT", "beza" -> "B", "" -> "?"
export function initials(name) {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    return (
        parts[0][0] + (parts.length > 1 ? parts[parts.length - 1][0] : '')
    ).toUpperCase();
}

// A stable color per person (same name -> same color).
export function avatarColor(name) {
    let hash = 0;

    for (const ch of String(name ?? '')) {
        hash = (hash * 31 + ch.charCodeAt(0)) % 360;
    }

    return `hsl(${hash} 55% 42%)`;
}
