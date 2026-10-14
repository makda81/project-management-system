<script setup>
// A small round avatar with the person's initials. Same name -> same color.
import { avatarColor, initials } from '@/lib/projectHelpers';

defineProps({
    name: { type: String, default: '' },
    size: { type: Number, default: 26 },
});
</script>

<template>
    <span
        class="avatar"
        :style="{
            background: avatarColor(name),
            width: `${size}px`,
            height: `${size}px`,
            fontSize: `${size * 0.42}px`,
        }"
        :title="name || 'Unassigned'"
    >
        {{ initials(name) }}
    </span>
</template>

<style scoped>
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
    line-height: 1;
    user-select: none;
}
</style>
