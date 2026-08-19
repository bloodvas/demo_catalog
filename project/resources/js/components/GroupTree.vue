<template>
    <div class="group-tree">
        <!-- Кнопка "Все категории" -->
        <button
            class="btn btn-sm w-100 mb-2"
            :class="!selectedId ? 'btn-primary' : 'btn-outline-secondary'"
            @click="selectNull"
        >
            <i class="bi bi-grid-3x3-gap me-1"></i>
            Все категории
        </button>

        <ul class="list-group">
            <GroupTreeNode
                v-for="group in groups"
                :key="group.id"
                :group="group"
                :selected-id="selectedId"
                @select="onSelect"
            />
        </ul>
    </div>
</template>

<script setup>
import GroupTreeNode from './GroupTreeNode.vue';

defineProps({
    groups: { type: Array, required: true },
    selectedId: { type: Number, default: null }
});

const emit = defineEmits(['select']);

function selectNull() {
    emit('select', { id: null, name: 'Все категории', product_count: null });
}

function onSelect(event) {
    emit('select', event);
}
</script>

<style scoped>
.group-tree .list-group {
    padding-left: 0;
}

.group-tree .btn {
    font-size: 0.85rem;
    padding: 0.5rem 0.75rem;
    border-radius: 8px;
}
</style>
