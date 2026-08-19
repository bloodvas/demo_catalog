<template>
    <div class="filter-bar">
        <h6 class="fw-bold mb-3 text-uppercase text-muted small">
            <i class="bi bi-funnel me-2"></i>Фильтры
        </h6>

        <!-- Сортировка -->
        <div class="mb-3">
            <label class="form-label fw-semibold small text-muted"
                >Сортировка</label
            >
            <select
                :value="sort.field + '-' + sort.direction"
                @change="handleSortUpdate"
            >
                <option value="price-desc">Цена: по убыванию</option>
                <option value="price-asc">Цена: по возрастанию</option>
                <option value="name-asc">Название: А-Я</option>
                <option value="name-desc">Название: Я-А</option>
            </select>
        </div>

        <!-- Количество товаров на странице -->
        <div class="mb-3">
            <label class="form-label fw-semibold small text-muted"
                >Товаров на странице</label
            >
            <select
                v-model="perPage"
                class="form-select"
                @change="updateFilter('perPage', perPage)"
            >
                <option :value="6">6</option>
                <option :value="12">12</option>
                <option :value="18">18</option>
            </select>
        </div>

        <!-- Текущая страница -->
        <div class="mb-3">
            <label class="form-label fw-semibold small text-muted"
                >Страница</label
            >
            <div class="form-control text-center fw-bold bg-light">
                {{ currentPage }} / {{ lastPage }}
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    sort: {
        // Было: sortBy (строка)
        type: Object, // Стало: объект { field, direction }
        default: () => ({
            field: "price",
            direction: "desc",
        }),
    },
    perPage: { type: Number, default: 12 },
    currentPage: { type: Number, default: 1 },
    lastPage: { type: Number, default: 1 },
});

const emit = defineEmits(["update:sort", "update:perPage", "pageChanged"]);

const sort = ref({ ...props.sort });
const perPage = ref(props.perPage);

function handleSortUpdate(event) {
    const [field, direction] = event.target.value.split("-");
    sort.value = { field, direction };
    emit("update:sort", sort.value);
}

function updateFilter(type, value) {
    if (type === "perPage") {
        emit("update:perPage", value);
    }
}
</script>

<style scoped>
.filter-bar .form-label {
    margin-bottom: 0.25rem;
}

.filter-bar .form-select,
.filter-bar .form-control {
    font-size: 0.875rem;
}
</style>
