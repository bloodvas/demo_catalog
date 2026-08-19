<template>
    <li class="list-group-item group-tree-item" :class="{ active: selectedId === group.id }">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <!-- Стрелка раскрытия -->
                <i
                    v-if="group.children?.length"
                    class="bi"
                    :class="isExpanded ? 'bi-chevron-down' : 'bi-chevron-right'"
                    style="font-size: 0.75rem; color: #999; cursor: pointer;"
                    @click.stop="toggleExpand"
                ></i>
                <span v-else style="width: 16px; display: inline-block;"></span>

                <!-- Иконка группы -->
                <i class="bi" :class="selectedId === group.id ? 'bi-folder-fill text-warning' : 'bi-folder text-muted'"></i>

                <span class="fw-semibold text-dark" style="cursor: pointer;" @click="selectGroup">
                    {{ group.name }}
                </span>
            </div>

            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ group.product_count ?? '-' }}</span>
        </div>

        <!-- Рекурсивно: вложенные группы -->
        <ul v-if="group.children?.length && isExpanded" class="list-group ms-3 mt-1">
            <GroupTreeNode
                v-for="child in group.children"
                :key="child.id"
                :group="child"
                :selected-id="selectedId"
                @select="onSelectChild"
            />
        </ul>
    </li>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

const props = defineProps({
    group: { type: Object, required: true },
    selectedId: { type: [Number, null], default: null }
});

const emit = defineEmits(['select']);

const isExpanded = ref(false);

// Автоматически раскрываем родителей выбранной группы
const shouldBeExpanded = computed(() => {
    if (!props.selectedId) return false;
    return props.group.id === props.selectedId;
});

watch(shouldBeExpanded, (shouldExpand) => {
    isExpanded.value = shouldExpand;
}, { immediate: true });

function toggleExpand() {
    if (props.group.children?.length) {
        isExpanded.value = !isExpanded.value;
    }
}

function selectGroup() {
    emit('select', {
        id: props.group.id,
        name: props.group.name,
        product_count: props.group.product_count
    });
}

function onSelectChild(event) {
    emit('select', event);
}
</script>

<style scoped>
.group-tree-item {
    cursor: pointer;
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    border-radius: 8px !important;
    margin-bottom: 2px;
    padding: 0.5rem 0.75rem !important;
}

.group-tree-item:hover {
    background: #f1f5f9;
}

.group-tree-item.active {
    background: #e0e7ff;
    border-left-color: #6366f1;
}

.list-group {
    padding-left: 0;
    border: none;
}

.list-group > .list-group-item {
    background: transparent;
    border: none;
    font-size: 0.875rem;
}
</style>
