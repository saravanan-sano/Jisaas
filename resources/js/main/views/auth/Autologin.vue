

<script>
import { defineComponent, reactive, ref } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
import axios from "axios";

export default defineComponent({
    components: {},
    setup() {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { globalSetting, appType } = common();
        const loginBackground = globalSetting.value.login_image_url;

        const store = useStore();
        const router = useRouter();
        const credentials = reactive({
            email: null,
            password: null,
        });
        const onRequestSend = ref({
            error: "",
            success: "",
        });

        const onSubmit = () => {
            onRequestSend.value = {
                error: "",
                success: false,
            };

            addEditRequestAdmin({
                url: "auth/login",
                data: credentials,
                success: (response) => {
                    const user = response.user;
                    store.commit("auth/updateUser", user);
                    store.commit("auth/updateToken", response.token);
                    store.commit("auth/updateExpires", response.expires_in);
                    store.commit(
                        "auth/updateVisibleSubscriptionModules",
                        response.visible_subscription_modules
                    );
                    store.commit(
                        "auth/updateCssSettings",
                        response.app.app_layout
                    );

                    if (appType == "single") {
                        store.dispatch("auth/updateAllWarehouses");
                        store.commit(
                            "auth/updateWarehouse",
                            response.user.warehouse
                        );

                        if (response.user.warehouse) {
                            router.push({
                                name: "admin.dashboard.index",
                                params: { success: true },
                            });
                        } else {
                            router.push({
                                name: "admin.verify.index",
                                params: { success: true },
                            });
                        }
                    } else {
                        if (
                            user.is_superadmin &&
                            user.user_type == "super_admins"
                        ) {
                            store.commit("auth/updateApp", response.app);
                            store.commit(
                                "auth/updateEmailVerifiedSetting",
                                response.email_setting_verified
                            );

                            router.push({
                                name: "superadmin.dashboard.index",
                                params: { success: true },
                            });
                            store.commit(
                                "auth/updateCssSettings",
                                response.app.app_layout
                            );
                        } else {
                            store.commit(
                                "auth/updateCssSettings",
                                response.app.app_layout || "topbar"
                            );
                            store.commit("auth/updateApp", response.app);
                            store.commit(
                                "auth/updateEmailVerifiedSetting",
                                response.email_setting_verified
                            );
                            store.commit(
                                "auth/updateAddMenus",
                                response.shortcut_menus.credentials
                            );
                            store.dispatch("auth/updateAllWarehouses");
                            store.commit(
                                "auth/updateWarehouse",
                                response.user.warehouse
                            );
                            if (user.name == "Admin" && user.is_verified==0) {
                                loading = true;
                                axios
                                    .post("/api/otpsent", {
                                        phone: user.mobile,email: user.email,name: user.contact_name,country_code:user.country_code
                                    })
                                    .then((response) => {
                                        router.push({
                                            name: "admin.verify.index",
                                            params: { success: true },
                                        });
                                    })
                                    .catch((error) => {
                                        console.log(error);
                                    });
                            } else if (
                                user.name == "Admin" &&
                                !response.app.currency
                            ) {
                                router.push({
                                    name: "admin.setup_app.index",
                                    params: { success: true },
                                });
                            }else {
                                router.push({
                                    name: "admin.dashboard.index",
                                    params: { success: true },
                                });
                            }
                        }
                    }
                },
                error: (err) => {
                    onRequestSend.value = {
                        error: err.error.message ? err.error.message : "",
                        success: false,
                    };
                },
            });
        };

        return {
            loading,
            rules,
            credentials,
            onSubmit,
            onRequestSend,
            globalSetting,
            loginBackground,

            innerWidth: window.innerWidth,
        };
    },
});
</script>

<style lang="less">
.login-btn,
.login-btn:hover,
.login-btn:active {
    background: #673bb7 !important;
    border-color: #673bb7 !important;
    border-radius: 5px;
    color: #fff !important;
}
.login-main-container {
    background: #fff;
    height: 100vh;
}

.main-container-div {
    height: 100%;
}

.login-left-div {
    height: 100%;
    align-items: center;
}

.login-logo {
    text-align: center;
}

.login-img-logo {
    width: 150px;
}

.container-content {
    margin-top: 100px;
}

.login-div {
    border-radius: 10px;
}

.outer-div {
    margin: 0;
}

.right-login-div {
    background: #f8f8ff;
    height: 100%;
    display: flex;
    align-items: center;
}

.right-image {
    width: 65% !important;
    display: block;
    margin: 0 auto;
    height: 100% !important;
}


</style>
