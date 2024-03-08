/**
 * importing createApp from VueJs
 */
import { createApp } from "vue";
/**
 * Importing pinia from state management
 */
import { createPinia } from "pinia";
/**
 * Importing vue-toast for notification system
 */
import Toast from "vue-toastification";
/**
 * Importing css for vue-toast for notification
 */
import "vue-toastification/dist/index.css";
/**
 * Importing main component file
 */
import Main from "./components/main.vue";
/**
 * creating the main vue app with imported Main component
 */
const app = createApp(Main);
/**
 * creating pinia
 */
const pinia = createPinia();
/**
 * using pinia in vue app
 */
app.use(pinia);

/**
 * using vue-toast in vue app
 */
const toastOption = {
    transition: "Vue-Toastification__fade",
    maxToasts: 20,
    timeout: false,
    hideProgressBar: true,
    newestOnTop: true,
    position: "bottom-right",
    toastClassName: "awraq-toast",
};
app.use(Toast, toastOption);
app.mount("#respacio_houzez_root");