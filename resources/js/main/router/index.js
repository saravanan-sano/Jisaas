import { createRouter, createWebHistory } from "vue-router";
import store from "../store";

import AuthRoutes from "./auth";
import DashboardRoutes from "./dashboard";
import VerifyRoutes from "./verify";
import ChatBotRoutes from "./chatBot";
import MasterRoutes from "./master";
import ProductRoutes from "./products";
import StockRoutes from "./stocks";
import ExpensesRoutes from "./expenses";
import UserRoutes from "./users";
import SettingRoutes from "./settings";
import ReportsRoutes from "./reports";
import SetupAppRoutes from "./setupApp";
import ResetpasswordRoutes from "./resetpassword";

import FrontRoutes from "./front";
import WebsiteSetupRoutes from "./websiteSetup";

import CouponsRoute from "./coupon";

const appType = window.config.app_type;
const allInstalledModules = window.config.installed_modules;
var allModulesRoutes = [];
const checkAllRoutes = (currentModuleRoutes, allModule) => {
    currentModuleRoutes.forEach((eachRoute) => {
        if (eachRoute.meta) {
            eachRoute.meta.appModule = allModule;
        }

        if (eachRoute.children) {
            var allChildrenRoues = eachRoute.children;

            checkAllRoutes(allChildrenRoues, allModule);
        }
    });
};

allInstalledModules.forEach((allModule) => {
    const allModuleName = allModule.verified_name;
    import(
        `../../../../Modules/${allModuleName}/Resources/assets/js/router/index.js` /* @vite-ignore */
    ).then((module) => {
        const moduleRoute = module.default;
        let currentModuleRoutes = [...moduleRoute];

        checkAllRoutes(currentModuleRoutes, allModuleName);

        allModulesRoutes.push(...currentModuleRoutes);
    });
});

// Including SuperAdmin Routes
let superAdminRoutes = [];
let subscriptionRoutes = [];
const superadminRouteFilePath = appType == "multiple" ? "superadmin" : "";
if (appType == "multiple") {
    import(
        `../../${superadminRouteFilePath}/router/index.js` /* @vite-ignore */
    ).then((module) => {
        superAdminRoutes = [...module.default];
    });

    import(
        `../../${superadminRouteFilePath}/router/admin/index.js` /* @vite-ignore */
    ).then((module) => {
        subscriptionRoutes = [...module.default];
    });
}

const isAdminCompanySetupCorrect = () => {
    var appSetting = store.state.auth.appSetting;

    if (appSetting.x_currency_id == null || appSetting.x_warehouse_id == null) {
        return false;
    }

    return true;
};

const isSuperAdminCompanySetupCorrect = () => {
    var appSetting = store.state.auth.appSetting;

    if (
        appSetting.x_currency_id == null ||
        appSetting.white_label_completed == false
    ) {
        return false;
    }

    return true;
};

const router = createRouter({
    history: createWebHistory(),
    routes: [
        ...allModulesRoutes,
        ...FrontRoutes,
        {
            path: "",
            redirect: "/admin/login",
        },
        ...WebsiteSetupRoutes,
        ...ProductRoutes,
        ...StockRoutes,
        ...ExpensesRoutes,
        ...AuthRoutes,
        ...DashboardRoutes,
        ...VerifyRoutes,
        ...ChatBotRoutes,
        ...MasterRoutes,
        ...UserRoutes,
        ...ReportsRoutes,
        ...SettingRoutes,
        ...subscriptionRoutes,
        ...superAdminRoutes,
        ...SetupAppRoutes,
        ...ResetpasswordRoutes,
        ...CouponsRoute,
        {
            path: "/:catchAll(.*)",
            redirect: "/",
        },
    ],
    scrollBehavior: () => ({ left: 0, top: 0 }),
});

export default router;
