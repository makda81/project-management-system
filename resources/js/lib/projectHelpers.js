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
