import "./common/plugins";

import { createApp } from "vue";
import Antd from "ant-design-vue";
// import PerfectScrollbar from "vue3-perfect-scrollbar";
import App from "./main/views/App.vue";
import routes from "./main/router";
import store from "./main/store";
import { setupI18n, loadLocaleMessages } from "./common/i18n";
import vue3GoogleLogin from "vue3-google-login";
// import "vue3-perfect-scrollbar/dist/vue3-perfect-scrollbar.css";
import VueBarcode from "@chenfengyuan/vue-barcode";
import print from "vue3-print-nb";
import "./main/views/coupon-manager/coupon/coupon-Builder/couponBuilder.css";

async function bootstrap() {
    if (store.getters["auth/isLoggedIn"]) {
        store.dispatch("auth/updateUser");
    }

    store.dispatch("auth/updateGlobalSetting");
    store.dispatch("auth/updateApp");
    store.dispatch("auth/updateAllLangs");
    store.dispatch("auth/updateAllWarehouses");
    store.commit("auth/updateActiveModules", window.config.modules);
    store.dispatch("auth/updateVisibleSubscriptionModules");

    const app = createApp(App);
    // For Coupon Builder
    // const theme = `hsl(${Math.floor(Math.random() * 36) * 10},50%,50%)`;
    // document.body.style.setProperty("--theme", theme);

    const i18n = setupI18n({
        legacy: false,
        globalInjection: true,
        locale: store.state.auth.lang,
        warnHtmlMessage: false,
    });
    await loadLocaleMessages(i18n, store.state.auth.lang);


    // app.config.devtools = true;
    // app.config.debug = true;

    app.use(i18n);
    app.use(Antd);
    // app.use(PerfectScrollbar);
    app.use(store);
    app.use(routes);
    app.use(print);

    app.component(VueBarcode.name, VueBarcode);
    const allModules = window.config.installed_modules;
    allModules.forEach((allModule) => {
        const moduleName = allModule.verified_name;
        const moduleMenu =
            require(`../../Modules/${moduleName}/Resources/assets/js/views/menu/admin.vue`).default;

        app.component(moduleName + "Menu", moduleMenu);
    });
    app.use(vue3GoogleLogin, {
        clientId:
            "803399791668-oh279m2brfb5m0uiv8cce2sqb60gdqr8.apps.googleusercontent.com",
        scope: "email",
    });
    window.i18n = i18n;

    app.mount("#app");
}

export default bootstrap();
