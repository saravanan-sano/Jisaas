<template>
    <div class="login-main-container">
        <a-row class="main-container-div">
            <a-col :xs="24" :sm="24" :md="24" :lg="8">
                <a-row class="login-left-div">
                    <a-col
                        :xs="{ span: 20, offset: 2 }"
                        :sm="{ span: 20, offset: 2 }"
                        :md="{ span: 16, offset: 4 }"
                        :lg="{ span: 16, offset: 4 }"
                    >
                        <a-card
                            :title="null"
                            class="login-div"
                            :bordered="innerWidth <= 768 ? true : false"
                        >
                            <a-form layout="vertical">
                                <div class="login-logo mb-30">
                                    <a href="/">
                                        <img
                                            class="login-img-logo"
                                            :src="globalSetting.light_logo_url"
                                    /></a>
                                </div>
                                <a-alert
                                    v-if="onRequestSend.error != ''"
                                    :message="onRequestSend.error"
                                    type="error"
                                    show-icon
                                    class="mb-20 mt-10"
                                />
                                <a-alert
                                    v-if="onRequestSend.success"
                                    :message="$t('messages.login_success')"
                                    type="success"
                                    show-icon
                                    class="mb-20 mt-10"
                                />
                                <a-form-item
                                    :label="$t('user.email_phone')"
                                    name="email"
                                    :help="
                                        rules.email ? rules.email.message : null
                                    "
                                    :validateStatus="
                                        rules.email ? 'error' : null
                                    "
                                >
                                    <a-input
                                        v-model:value="credentials.email"
                                        @pressEnter="onSubmit"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.email_phone')]
                                            )
                                        "
                                    />
                                </a-form-item>

                                <a-form-item
                                    :label="$t('user.password')"
                                    name="password"
                                    :help="
                                        rules.password
                                            ? rules.password.message
                                            : null
                                    "
                                    :validateStatus="
                                        rules.password ? 'error' : null
                                    "
                                >
                                    <a-input-password
                                        v-model:value="credentials.password"
                                        @pressEnter="onSubmit"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.password')]
                                            )
                                        "
                                    />
                                </a-form-item>

                                <a-form-item1 class="mt-30">
                                    <a-button
                                        :loading="loading"
                                        @click="onSubmit"
                                        class="login-btn"
                                        block
                                    >
                                        {{ $t("menu.login") }}
                                    </a-button>
                                </a-form-item1>
                                <a-form-item>
                                    <a
                                        class="text-gray-900 hover:text-gray-700"
                                        href="/admin/resetpassword"
                                        >Fotgot Password?</a
                                    >
                                </a-form-item>
                            </a-form>
                            <div class="google-button">
                                <button
                                    class="google_button"
                                    @click="login"
                                    v-if="googleData"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        preserveAspectRatio="xMidYMid"
                                        viewBox="0 0 256 262"
                                    >
                                        <path
                                            fill="#4285F4"
                                            d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622 38.755 30.023 2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"
                                        ></path>
                                        <path
                                            fill="#34A853"
                                            d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055-34.523 0-63.824-22.773-74.269-54.25l-1.531.13-40.298 31.187-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"
                                        ></path>
                                        <path
                                            fill="#FBBC05"
                                            d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82 0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602l42.356-32.782"
                                        ></path>
                                        <path
                                            fill="#EB4335"
                                            d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0 79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"
                                        ></path>
                                    </svg>
                                    Continue With Google
                                </button>
                            </div>
                            <a-form-item class="mt-30">
                                Don't have a account
                                <a
                                    class="text-gray-900 hover:text-gray-700"
                                    href="/register"
                                    >click here</a
                                >
                                to register.
                            </a-form-item>
                        </a-card>
                    </a-col>
                </a-row>
            </a-col>
            <a-col :xs="0" :sm="0" :md="24" :lg="16">
                <div class="right-login-div">
                    <img class="right-image" :src="loginBackground" />
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, reactive, ref } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
import { googleTokenLogin } from "vue3-google-login";
import axios from "axios";

export default defineComponent({
    components: {},
    setup() {
        const googleData = ref(true);
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { globalSetting, appType } = common();
        const loginBackground = globalSetting.value.login_image_url;
        const login = () => {
            googleTokenLogin().then((response) => {
                // Access user data from the response
                const userData = response.user;
                getUserEmail(response.access_token);
                // You can perform further actions with the user data, such as saving it to a state or making API calls.
            });
        };
        const getUserEmail = (accessToken) => {
            const url = "https://www.googleapis.com/oauth2/v2/userinfo";
            const headers = { Authorization: `Bearer ${accessToken}` };
            return axios
                .get(url, { headers })
                .then((response) => {
                    const data = response.data;
                    let userData = {
                        token: accessToken,
                        password: data.id,
                        email: data.email,
                        verified_email: data.verified_email,
                        company_name: data.name,
                        profile_name: data.given_name,
                        family_name: data.family_name,
                        picture: data.picture,
                        locale: data.locale,
                    };

                    axios
                        .post("/api/googlelogin", userData)
                        .then((response) => {
                            if (response.status == 200) {
                                const user = response.data.data.user.user;
                                localStorage.setItem(
                                    "helpvideos",
                                    JSON.stringify(
                                        response.data.data.helpvideos
                                    )
                                );
                                // localStorage.setItem("pos_type", response.pos_type)

                                store.commit("auth/updateUser", user);
                                store.commit(
                                    "auth/updateToken",
                                    response.data.data.user.token
                                );
                                store.commit(
                                    "auth/updateExpires",
                                    response.data.data.user.expires_in
                                );
                                store.commit(
                                    "auth/updateVisibleSubscriptionModules",
                                    response.data.data.user
                                        .visible_subscription_modules
                                );
                                store.commit(
                                    "auth/updateCssSettings",
                                    response.data.data.app.app_layout
                                );

                                if (appType == "single") {
                                    store.dispatch("auth/updateAllWarehouses");
                                    store.commit(
                                        "auth/updateWarehouse",
                                        response.data.data.user.user.warehouse
                                    );

                                    if (
                                        response.data.data.user.user.warehouse
                                    ) {
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
                                        store.commit(
                                            "auth/updateApp",
                                            response.data.data.app
                                        );
                                        store.commit(
                                            "auth/updateEmailVerifiedSetting",
                                            response.data.data
                                                .useremail_setting_verified
                                        );

                                        router.push({
                                            name: "superadmin.dashboard.index",
                                            params: { success: true },
                                        });
                                        store.commit(
                                            "auth/updateCssSettings",
                                            response.data.data.app.app_layout
                                        );
                                    } else {
                                        store.commit(
                                            "auth/updateCssSettings",
                                            response.data.data.app.app_layout ||
                                                "topbar"
                                        );
                                        store.commit(
                                            "auth/updateApp",
                                            response.data.data.app
                                        );
                                        store.commit(
                                            "auth/updateEmailVerifiedSetting",
                                            response.data.data
                                                .email_setting_verified
                                        );
                                        store.commit(
                                            "auth/updateAddMenus",
                                            response.data.data.shortcut_menus
                                                .credentials
                                        );
                                        store.dispatch(
                                            "auth/updateAllWarehouses"
                                        );
                                        store.commit(
                                            "auth/updateWarehouse",
                                            response.data.data.user.user
                                                .warehouse
                                        );
                                        if (
                                            response.data.data.app.warehouse &&
                                            response.data.data.app.currency
                                        ) {
                                            router.push({
                                                name: "admin.dashboard.index",
                                                params: { success: true },
                                            });
                                        } else if (
                                            user.name == "Admin" &&
                                            user.is_verified == 0
                                        ) {
                                            router.push({
                                                name: "admin.setup_app.index",
                                                params: { success: true },
                                            });
                                        } else if (
                                            user.name == "Admin" &&
                                            !response.data.data.app.currency
                                        ) {
                                            router.push({
                                                name: "admin.setup_app.index",
                                                params: { success: true },
                                            });
                                        } else {
                                            router.push({
                                                name: "admin.dashboard.index",
                                                params: { success: true },
                                            });
                                        }
                                    }
                                }
                            } else {
                                onRequestSend.value = {
                                    error: err.error.message
                                        ? err.error.message
                                        : "",
                                    success: false,
                                };
                            }
                        });
                })
                .catch((error) => {
                    console.error("Error retrieving user email:", error);
                    throw error;
                });
        };
        const store = useStore();
        const router = useRouter();
        const credentials = reactive({
            email: null,
            password: null,
            type: 1,
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
                    // by Saravana, for validating the user credentials.
                    if (!user) {
                        onRequestSend.value = {
                            error: response.message ? response.message : "",
                            success: false,
                        };
                        loading.value = false;
                    }
                    //
                    localStorage.setItem(
                        "helpvideos",
                        JSON.stringify(response.helpvideos)
                    );
                    localStorage.setItem("pos_type", response.pos_type);

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
                            if (user.name == "Admin" && user.is_verified == 0) {
                                axios
                                    .post("/api/otpsent", {
                                        phone: user.mobile,
                                        email: user.email,
                                        name: user.contact_name,
                                        country_code: user.country_code,
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
                                (user.name == "Admin" &&
                                    !response.app.currency) ||
                                !response.user.warehouse
                            ) {
                                router.push({
                                    name: "admin.setup_app.index",
                                    params: { success: true },
                                });
                            } else {
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
            googleData,
            login,
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

.google-button {
    margin-bottom: 1rem;
}

.google-button > button {
    width: 100%;
    border-radius: 4px;
    justify-content: center;
    display: flex;
    padding: 0.5rem 1.4rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 700;
    text-align: center;
    text-transform: capitalize;
    align-items: center;
    border: 1px solid rgba(0, 0, 0, 0.25);
    gap: 0.75rem;
    color: rgb(65, 63, 63);
    background-color: #fff;
    cursor: pointer;
    transition: all 0.6s ease;
}

.google-button > button > svg {
    height: 24px;
}

.login-form-bottom-2 .google-button {
    margin-bottom: 1rem;
}

.google-button > button {
    width: 100%;
    border-radius: 4px;
    justify-content: center;
    display: flex;
    padding: 0.5rem 1.4rem;
    font-size: 0.875rem;
    line-height: 1.25rem;
    font-weight: 700;
    text-align: center;
    text-transform: capitalize;
    align-items: center;
    border: 1px solid rgba(0, 0, 0, 0.25);
    gap: 0.75rem;
    color: rgb(65, 63, 63);
    background-color: #fff;
    cursor: pointer;
    transition: all 0.6s ease;
}

.google-button > button > svg {
    height: 24px;
}

.ant-form-item1 {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    color: rgba(0, 0, 0, 0.85);
    font-size: 14px;
    font-variant: tabular-nums;
    line-height: 1.5715;
    list-style: none;
    font-feature-settings: "tnum";
    margin-bottom: 0px;
    vertical-align: top;
}
</style>
