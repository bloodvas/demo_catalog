<template>
    <nav class="breadcrumb-bar mb-4" aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
            <li v-for="(item, index) in breadcrumbs" :key="item.id" class="breadcrumb-item" :class="{ active: index === breadcrumbs.length - 1 }">
                <span v-if="index < breadcrumbs.length - 1" @click="$emit('click', item)" style="cursor: pointer; color: #667eea;">
                    {{ item.name }}
                </span>
                <span v-else class="text-muted fw-semibold">
                    {{ item.name }}
                </span>
            </li>
        </ol>
    </nav>
</template>

<script setup>
defineProps({
    breadcrumbs: {
        type: Array,
        default: () => []
    }
});

defineEmits(['click']);
</script>

<style scoped>
.breadcrumb-bar {
    background: white;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.breadcrumb {
    flex-wrap: nowrap;
    overflow-x: auto;
    padding-bottom: 4px;
}

.breadcrumb-item {
    white-space: nowrap;
}

.breadcrumb-item a {
    text-decoration: none;
    color: #667eea;
    transition: color 0.2s;
}

.breadcrumb-item a:hover {
    color: #764ba2;
    text-decoration: underline;
}

.breadcrumb-item + .breadcrumb-item::before {
    content: "/";
    color: #ccc;
}
</style>
