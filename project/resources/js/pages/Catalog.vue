<template>
    <AppLayout>
        <template #sidebar>
            <h5 class="mb-3">📂 Категории</h5>
            <div class="px-2">
                <ul class="list-group">
                    <li
                        v-for="group in groups"
                        :key="group.id"
                        class="list-group-item catalog-group-item"
                        :class="{ active: selectedGroupId === group.id }"
                        @click="selectGroup(group)"
                    >
                        <span class="fw-semibold">{{ group.name }}</span>
                        <span class="badge rounded-pill">
                            {{ group.product_count }}
                        </span>

                        <!-- Рекурсивно: вложенные группы -->
                        <ul
                            v-if="group.children?.length"
                            class="list-group mt-2"
                        >
                            <li
                                v-for="child in group.children"
                                :key="child.id"
                                class="list-group-item catalog-group-item"
                                :class="{
                                    active: selectedGroupId === child.id,
                                }"
                                @click="selectGroup(child)"
                            >
                                <span class="fw-semibold">{{
                                    child.name
                                }}</span>
                                <span class="badge rounded-pill">
                                    {{ child.product_count }}
                                </span>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </template>

        <div>
            <!-- Заголовок -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">
                    <span v-if="selectedGroupId">{{
                        group?.name || "Товары"
                    }}</span>
                    <span v-else>Все товары</span>
                </h2>
                <div class="text-muted">
                    Всего товаров: {{ pagination.total }}
                </div>
            </div>

            <!-- Фильтры -->
            <div class="catalog-filters">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">
                            Сортировка
                        </label>
                        <select
                            v-model="sortBy"
                            class="form-select"
                            @change="fetchProducts"
                        >
                            <option value="price-desc">Цена ↓</option>
                            <option value="price-asc">Цена ↑</option>
                            <option value="name-asc">Название А-Я</option>
                            <option value="name-desc">Название Я-А</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">
                            Товаров на странице
                        </label>
                        <select
                            v-model="perPage"
                            class="form-select"
                            @change="fetchProducts"
                        >
                            <option :value="6">6</option>
                            <option :value="12">12</option>
                            <option :value="18">18</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-muted">
                            Текущая страница
                        </label>
                        <input
                            type="text"
                            class="form-control"
                            :value="`${pagination.current_page} / ${pagination.last_page}`"
                            readonly
                        />
                    </div>
                </div>
            </div>

            <!-- Список товаров -->
            <div v-if="loading" class="loading-overlay">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
            </div>

            <div v-else-if="products.length === 0" class="empty-state">
                <svg
                    width="64"
                    height="64"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                >
                    <path
                        d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l1.313 7h7.176l1.313-7H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"
                    />
                </svg>
                <h5>Товары не найдены</h5>
                <p class="text-muted">
                    Попробуйте изменить параметры фильтрации
                </p>
            </div>

            <div v-else class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <div v-for="product in products" :key="product.id" class="col">
                    <div class="card h-100 catalog-product-card">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold mb-2">
                                {{ product.name }}
                            </h5>
                            <p class="card-text text-muted small mb-3">
                                {{ product.group?.name || "Без категории" }}
                            </p>
                            <div class="mt-auto">
                                <p class="catalog-product-price mb-0">
                                    {{ formatPrice(product.price?.price) }} ₽
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Пагинация -->
            <nav
                v-if="pagination.last_page > 1"
                class="mt-4 d-flex justify-content-center"
            >
                <ul class="pagination catalog-pagination">
                    <li
                        class="page-item"
                        :class="{ disabled: pagination.current_page === 1 }"
                    >
                        <a
                            class="page-link"
                            href="#"
                            @click.prevent="
                                fetchProducts(pagination.current_page - 1)
                            "
                        >
                            ← Назад
                        </a>
                    </li>

                    <li
                        v-for="page in pagination.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === pagination.current_page }"
                    >
                        <a
                            class="page-link"
                            href="#"
                            @click.prevent="fetchProducts(page)"
                        >
                            {{ page }}
                        </a>
                    </li>

                    <li
                        class="page-item"
                        :class="{
                            disabled:
                                pagination.current_page ===
                                pagination.last_page,
                        }"
                    >
                        <a
                            class="page-link"
                            href="#"
                            @click.prevent="
                                fetchProducts(pagination.current_page + 1)
                            "
                        >
                            Вперёд →
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import axios from "axios";

// Реактивные состояния
const groups = ref([]);
const products = ref([]);
const selectedGroupId = ref(null);
const sortBy = ref("price-desc");
const perPage = ref(12);
const currentPage = ref(1);
const loading = ref(false);

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0,
});

const group = ref(null);

// Загрузка групп при старте
onMounted(async () => {
    await fetchGroups();
    await fetchProducts();
});

// Получаем группы
async function fetchGroups() {
    try {
        const { data } = await axios.get("/api/groups");
        groups.value = data;
    } catch (error) {
        console.error("Ошибка загрузки групп:", error);
    }
}

// Получаем товары
async function fetchProducts(page = 1) {
    loading.value = true;
    currentPage.value = page;

    const params = {
        page,
        per_page: perPage.value,
        sort: sortBy.value.split("-")[0],
        order: sortBy.value.split("-")[1] || "desc",
    };

    // Если выбрана группа — фильтруем по ней
    if (selectedGroupId.value) {
        params.group_id = selectedGroupId.value;
    }

    try {
        const { data } = await axios.post("/api/products", params);
        products.value = data.data || [];
        pagination.current_page = data.current_page;
        pagination.last_page = data.last_page;
        pagination.per_page = data.per_page;
        pagination.total = data.total;
    } catch (error) {
        console.error("Ошибка загрузки товаров:", error);
    } finally {
        loading.value = false;
    }
}

// Выбор группы
function selectGroup(groupItem) {
    selectedGroupId.value = groupItem.id;
    group.value = groupItem;
    fetchProducts(1); // сброс на 1 страницу
}

// Форматирование цены
function formatPrice(price) {
    if (!price) return "0,00";
    return Number(price).toLocaleString("ru-RU", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}
</script>
