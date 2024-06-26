<template>
    <router-view v-slot="{ Component, route }">
        <suspense>
            <template #default>
                <a-config-provider :direction="appSetting.rtl ? 'rtl' : 'ltr'">
                    <div class="theme-container">
                        <div
                            class="maintain-version"
                            style="text-align: center"
                        >
                            <!--
                                ------------------------------------------
                                ENTER THE NOTE BEFORE CHANGING THE VERSION
                                ------------------------------------------
                                * Added Product Clone option.
                                * Fixed Subtotal 0 save in Billing.
                                * Added max_mobile_digit in Companies Table and based on that validated the customer Mobile Number.
                                * Organized All the available Invoice in the Invoice-Template Folder.
                                * Added Specific Button for exporting Invoice as PDF while Viewing.
                             -->
                            <a-tag>Version 1.2.1</a-tag>
                        </div>
                        <ThemeProvider :theme="{ ...theme }">
                            <LoadingApp v-if="appChecking" />
                            <component
                                v-else
                                :is="Component"
                                :key="route.name"
                            />
                        </ThemeProvider>
                    </div>
                </a-config-provider>
            </template>
            <template #fallback> Loading... </template>
        </suspense>
    </router-view>
</template>

<script>
import { watch, onMounted, computed } from "vue";
import { ThemeProvider } from "vue3-styled-components";
import { theme } from "../config/theme/themeVariables";
import { changeAntdTheme } from "mini-dynamic-antd-theme";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import common from "../../common/composable/common";
import LoadingApp from "./LoadingApp.vue";

export default {
    name: "App",
    components: {
        ThemeProvider,
        LoadingApp,
    },
    setup() {
        const route = useRoute();
        const router = useRouter();
        const store = useStore();
        const darkTheme = "dark";
        const { updatePageTitle, appSetting, frontWarehouse, appType } =
            common();
        changeAntdTheme(appSetting.value.primary_color);
        const appChecking = computed(() => store.state.auth.appChecking);

        onMounted(() => {
            setInterval(() => {
                store.dispatch("auth/refreshToken");
            }, 5 * 60 * 1000);
        });

        watch(route, (newVal, oldVal) => {
            // router.push({
            //     name: "admin.setup_app.index",
            // });

            const menuKey =
                typeof newVal.meta.menuKey == "function"
                    ? newVal.meta.menuKey(newVal)
                    : newVal.meta.menuKey;

            updatePageTitle(menuKey.replace("-", "_"));

            // Redirecting if plan is expired
            if (
                appType == "multiple" &&
                appSetting.value.is_global == 0 &&
                appSetting.value.status == "license_expired" &&
                newVal &&
                newVal.meta &&
                !(
                    newVal.meta.menuParent == "subscription" ||
                    newVal.name == "admin.login" ||
                    newVal.name == "verify.main"
                )
            ) {
                router.push({
                    name: "admin.subscription.current_plan",
                });
            }
        });

        watch(frontWarehouse, (newVal, oldVal) => {
            if (newVal && newVal.slug) {
                store.dispatch("front/updateApp", newVal.slug);
            }
        });

        return {
            theme,
            darkTheme,
            appChecking,
            appSetting,
        };
    },
};
</script>

<style>
body {
    background: #f0f2f5 !important;
}
.theme-container {
    position: relative;
}
.maintain-version > span {
    font-size: 8px !important;
    background: transparent !important;
    color: #fff !important;
    border: none !important;
    padding: 0 !important;
    user-select: none;
}
.maintain-version {
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 9999;
}
</style>
