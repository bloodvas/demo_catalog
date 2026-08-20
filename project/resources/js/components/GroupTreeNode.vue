<template>
    <li class="list-group-item group-tree-item" :class="{ active: selectedId === group.id }">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2 flex-grow-1">
                <!-- Стрелка раскрытия: показываем если есть дети -->
                <i
                    v-if="group.children?.length"
                    class="bi"
                    :class="isExpanded ? 'bi-chevron-down' : 'bi-chevron-right'"
                    style="font-size: 0.75rem; color: #999; cursor: pointer;"
                    @click.stop="toggleExpand"
                ></i>
                <span v-else style="width: 16px; display: inline-block;"></span>

                <!-- Иконка группы: заполненная если выбрана -->
                <i class="bi" :class="selectedId === group.id ? 'bi-folder-fill text-warning' : 'bi-folder text-muted'"></i>

                <span class="fw-semibold text-dark" style="cursor: pointer;" @click="selectGroup">
                    {{ group.name }}
                </span>
            </div>

            <!-- Бейдж с количеством товаров -->
            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">{{ group.product_count ?? '-' }}</span>
        </div>

        <!-- Рекурсивно: вложенные группы (показываем только если раскрыто) -->
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
import { ref, watch } from 'vue';

// ---------- props ----------
const props = defineProps({
    // Текущая группа (id, name, product_count, children)
    group: { type: Object, required: true },
    // ID выбранной группы (null = ничего не выбрано)
    selectedId: { type: [Number, null], default: null }
});

// ---------- events ----------
const emit = defineEmits(['select']);

// ---------- state ----------
// Состояние раскрытия (раскрываем/сворачиваем по клику)
const isExpanded = ref(false);

// ---------- helpers ----------

/**
 * Рекурсивно проверяем, является ли текущий узел родителем выбранной группы.
 * Например: если выбран "Бюджетные" (id=5), то "Смартфоны" (id=2) и "Электроника" (id=1) должны быть раскрыты.
 *
 * @param {Array|null} children - массив дочерних групп
 * @param {number|null} targetId - ID выбранной группы
 * @return {boolean} true если среди потомков есть нужная группа
 */
function isAncestorOfSelected(children, targetId) {
    // Если нет детей или дети не массив — дальше искать негде
    if (!children || !Array.isArray(children)) return false;

    for (const child of children) {
        // Проверка: этот ребёнок — именно выбранная группа?
        if (child.id === targetId) return true;

        // Рекурсивная проверка: выбрана ли группа глубже (внуки, правнуки и т.д.)?
        if (isAncestorOfSelected(child.children, targetId)) return true;
    }

    // Ничего не нашли среди потомков
    return false;
}

// ---------- watch ----------

/**
 * Отслеживаем изменения selectedId (когда пользователь кликает на группу).
 *
 * Логика:
 *  1. Если selectedId = null → сворачиваем всё
 *  2. Если текущий узел == выбранной → раскрываем
 *  3. Если выбранный узел где-то внутри этого узла → раскрываем (чтобы дотянуться до него)
 *  4. Иначе → сворачиваем (этот узел не связан с выбранным)
 */
watch(
    () => props.selectedId,  // зависимостью является selectedId
    (newSelectedId) => {     // newSelectedId — новое значение
        if (!newSelectedId) {
            // Выбрано "Все категории" — сворачиваем этот узел
            isExpanded.value = false;
            return;
        }

        // Сценарий 2: текущий узел — именно выбранный
        if (props.group.id === newSelectedId) {
            isExpanded.value = true;
            return;
        }

        // Сценарий 3: выбранный узел находится где-то ниже (предок)
        isExpanded.value = isAncestorOfSelected(props.group.children, newSelectedId);
    },
    { immediate: true }  // immediate: выполняем сразу при монтировании компонента
);

// ---------- methods ----------

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
