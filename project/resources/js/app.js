import { createApp, h, render } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";

// Импортируем стили
import "../css/app.css";

// Создаём Inertia-приложение (SPA-обёртка)
createInertiaApp({
    // Определяем, где лежат страницы Vue
    resolve: (name) => {
        // import.meta.glob lazy (без eager!) загружает страницу только когда нужна
        const pages = import.meta.glob("./pages/**/*.vue");
        // Возвращаем функцию-импорт для каждой страницы
        return pages[`./pages/${name}.vue`]();
    },

    //解决: передаём решение URL
    setup({ el, App, props, plugin }) {
        // Создаём Vue-приложение
        createApp({ render: () => h(App, props) })
            .use(plugin) // Встраиваем Inertia plugin
            .mount(el); // Монтируем на #app
    },
});
