<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';

const { appearance, updateAppearance } = useAppearance();

function toggle() {
    updateAppearance(appearance.value === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <button
        type="button"
        class="pm-btn"
        aria-label="Toggle theme"
        @click="toggle"
    >
        {{ appearance === 'dark' ? '☀️ Light' : '🌙 Dark' }}
    </button>
</template>
