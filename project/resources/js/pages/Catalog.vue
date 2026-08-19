<template>
    <AppLayout>
        <template #sidebar>
            <!-- Группы -->
            <h6 class="fw-bold mb-3 text-uppercase text-muted small">
                <i class="bi bi-folder2-open me-2"></i>Категории
            </h6>
            <GroupTree
                :groups="groups"
                :selected-id="selectedGroupId"
                @select="selectGroup"
            />

            <hr class="my-3" />

            <!-- Фильтры -->
            <FilterBar
                :sort="sort"
                :per-page="perPage"
                :current-page="pagination.current_page"
                :last-page="pagination.last_page"
                @update:sort="handleSortUpdate"
                @update:perPage="handlePerPageUpdate"
            />
        </template>

        <!-- Хлебные крошки -->
        <Breadcrumbs
            v-if="breadcrumbs.length > 0"
            :breadcrumbs="breadcrumbs"
            @click="handleBreadcrumbClick"
        />

        <!-- Заголовок -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0 fw-bold">
                <span v-if="selectedGroup">{{ selectedGroup.name }}</span>
                <span v-else>Все товары</span>
            </h2>
            <div class="text-muted small">
                Найдено: <strong>{{ pagination.total }}</strong> товар(ов)
            </div>
        </div>

        <!-- Список товаров -->
        <div v-if="loading" class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3 text-muted">Загрузка товаров...</p>
        </div>

        <div
            v-else-if="products.length === 0"
            class="text-center py-5 bg-white rounded-3 shadow-sm"
        >
            <i class="bi bi-box-seam fs-1 text-muted mb-3 d-block"></i>
            <h5 class="text-muted">Товары не найдены</h5>
            <p class="text-muted small">
                Попробуйте изменить параметры фильтрации
            </p>
        </div>

        <div v-else class="row g-3">
            <div
                v-for="product in products"
                :key="product.id"
                class="col-6 col-md-4 col-lg-4"
            >
                <div class="card h-100 product-card shadow-sm border-0">
                    <div class="card-body d-flex flex-column p-3">
                        <h6
                            class="card-title fw-bold mb-2 text-truncate"
                            :title="product.name"
                        >
                            {{ product.name }}
                        </h6>
                        <p class="card-text text-muted small mb-2">
                            <i class="bi bi-folder me-1"></i
                            >{{ product.group?.name || "—" }}
                        </p>
                        <div class="mt-auto">
                            <p class="price-tag mb-0 fw-bold text-primary fs-5">
                                {{ formatPrice(product.price?.price) }} ₽
                            </p>
                            <button
                                class="btn btn-outline-primary btn-sm mt-2 w-100"
                                @click.prevent="viewProduct(product)"
                            >
                                <i class="bi bi-eye me-1"></i>Подробнее
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Пагинация -->
        <nav v-if="pagination.last_page > 1" class="mt-4">
            <ul class="pagination justify-content-center gap-1">
                <li
                    class="page-item"
                    :class="{ disabled: pagination.current_page === 1 }"
                >
                    <a
                        class="page-link rounded-pill"
                        href="#"
                        @click.prevent="goToPage(pagination.current_page - 1)"
                    >
                        <i class="bi bi-chevron-left"></i>
                    </a>
                </li>

                <template v-if="pagination.last_page <= 7">
                    <li
                        v-for="page in pagination.last_page"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === pagination.current_page }"
                    >
                        <a
                            class="page-link rounded-pill"
                            href="#"
                            @click.prevent="goToPage(page)"
                            >{{ page }}</a
                        >
                    </li>
                </template>

                <template v-else>
                    <li
                        class="page-item"
                        :class="{ active: 1 === pagination.current_page }"
                    >
                        <a
                            class="page-link rounded-pill"
                            href="#"
                            @click.prevent="goToPage(1)"
                            >1</a
                        >
                    </li>
                    <li
                        v-if="pagination.current_page > 3"
                        class="page-item disabled"
                    >
                        <span class="page-link">...</span>
                    </li>
                    <li
                        v-for="page in middlePages"
                        :key="page"
                        class="page-item"
                        :class="{ active: page === pagination.current_page }"
                    >
                        <a
                            class="page-link rounded-pill"
                            href="#"
                            @click.prevent="goToPage(page)"
                            >{{ page }}</a
                        >
                    </li>
                    <li
                        v-if="
                            pagination.current_page < pagination.last_page - 2
                        "
                        class="page-item disabled"
                    >
                        <span class="page-link">...</span>
                    </li>
                    <li
                        class="page-item"
                        :class="{
                            active:
                                pagination.last_page ===
                                pagination.current_page,
                        }"
                    >
                        <a
                            class="page-link rounded-pill"
                            href="#"
                            @click.prevent="goToPage(pagination.last_page)"
                            >{{ pagination.last_page }}</a
                        >
                    </li>
                </template>

                <li
                    class="page-item"
                    :class="{
                        disabled:
                            pagination.current_page === pagination.last_page,
                    }"
                >
                    <a
                        class="page-link rounded-pill"
                        href="#"
                        @click.prevent="goToPage(pagination.current_page + 1)"
                    >
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import AppLayout from "@/layouts/AppLayout.vue";
import GroupTree from "@/components/GroupTree.vue";
import FilterBar from "@/components/FilterBar.vue";
import Breadcrumbs from "@/components/Breadcrumbs.vue";

// --- Состояние ---
const groups = ref([]);
const products = ref([]);
const selectedGroupId = ref(null);
const selectedGroup = ref(null);
const sort = ref({ field: "price", direction: "desc" });
const perPage = ref(12);
const loading = ref(false);

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 12,
    total: 0,
});

const breadcrumbs = ref([]);

// --- Пагинация: умный показ страниц ---
const middlePages = computed(() => {
    const current = pagination.current_page;
    const last = pagination.last_page;
    const pages = [];

    if (last <= 7) return pages;

    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    return pages;
});

// --- API запросы ---
async function fetchGroups() {
    try {
        const { data } = await axios.get("/api/groups");
        groups.value = data;
    } catch (error) {
        console.error("Ошибка загрузки групп:", error);
    }
}

async function fetchProducts(page = 1) {
    loading.value = true;

    const params = {
        page,
        per_page: perPage.value,
        sort: {
            field: sort.value.field,
            direction: sort.value.direction,
        },
    };

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

async function fetchBreadcrumbs(groupId) {
    try {
        const { data } = await axios.get(`/api/groups/${groupId}/breadcrumbs`);
        breadcrumbs.value = data;
    } catch (error) {
        console.error("Ошибка загрузки хлебных крошек:", error);
    }
}

// --- Действия ---
function selectGroup(group) {
    selectedGroupId.value = group.id;
    selectedGroup.value = group;
    breadcrumbs.value = [];

    if (group.id !== null) {
        fetchBreadcrumbs(group.id);
    }

    fetchProducts(1);
}

function viewProduct(product) {
    router.get(`/product/${product.id}`);
}

function handleBreadcrumbClick(item) {
    selectGroup(item);
}

function goToPage(page) {
    if (page < 1 || page > pagination.last_page) return;
    fetchProducts(page);
}

// --- Обновление фильтров ---
function handleSortUpdate(newSort) {
    sort.value = newSort;
    fetchProducts(1);
}

function handlePerPageUpdate(newPerPage) {
    perPage.value = newPerPage;
    fetchProducts(1);
}

// --- Форматирование ---
function formatPrice(price) {
    if (!price) return "0,00";
    return Number(price).toLocaleString("ru-RU", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

// ---生命周期 ---
onMounted(async () => {
    await fetchGroups();
    await fetchProducts();
});
</script>
