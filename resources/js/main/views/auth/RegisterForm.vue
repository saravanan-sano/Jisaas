<template>
    <div class="login-main-container">
        <a-row class="main-container-div">
            <a-col :xs="24" :sm="24" :md="24" :lg="12">
                <a-row class="login-left-div">
                    <a-col
                        :xs="20"
                        :sm="20"
                        :md="22"
                        :lg="22"
                        style="margin: 1rem"
                    >
                        <a-card
                            :title="null"
                            class="login-div"
                            :bordered="innerWidth <= 768 ? true : false"
                        >
                            <a-form
                                :form="form"
                                :rules="rules"
                                ref="form"
                                :model="formValues"
                                layout="vertical"
                            >
                                <div class="login-form-top">
                                    <h2>Register Now</h2>
                                    <p>
                                        Hey, Enter your details to get your own
                                        Company
                                    </p>
                                </div>
                                <a-alert
                                    v-if="onRequestSend.error != ''"
                                    :message="onRequestSend.error"
                                    type="error"
                                    show-icon
                                    class="mb-20 mt-10"
                                />
                                <a-alert
                                    v-if="onRequestSend.success != ''"
                                    :message="onRequestSend.success"
                                    type="success"
                                    show-icon
                                    class="mb-20 mt-10"
                                />
                                <a-row :gutter="16">
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item
                                            :colon="false"
                                            name="company_name"
                                            :rules="rules.company_name"
                                        >
                                            <a-input
                                                placeholder="Company Name"
                                                v-model:value="
                                                    formValues.company_name
                                                "
                                            />
                                        </a-form-item>
                                    </a-col>
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item
                                            :colon="false"
                                            name="company_email"
                                            :rules="rules.company_email"
                                        >
                                            <a-input
                                                placeholder="Email"
                                                v-model:value="
                                                    formValues.company_email
                                                "
                                            />
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <a-row :gutter="16">
                                    <a-col class="gutter-row" :span="12">
                                        <div class="phone_number_wrapper">
                                            <a-form-item
                                                :colon="false"
                                                name="countryCode"
                                                :rules="rules.countryCode"
                                            >
                                                <a-select
                                                    :allowClear="true"
                                                    v-model:value="
                                                        formValues.countryCode
                                                    "
                                                    showSearch
                                                    optionFilterProp="title"
                                                >
                                                    <a-select-option
                                                        v-for="country in Countries"
                                                        :key="country.phonecode"
                                                        :title="
                                                            country.name +
                                                            country.phonecode
                                                        "
                                                        :value="
                                                            country.phonecode
                                                        "
                                                    >
                                                        {{
                                                            `${country.iso}(+${country.phonecode})`
                                                        }}
                                                    </a-select-option>
                                                </a-select>
                                            </a-form-item>
                                            <a-form-item
                                                :colon="false"
                                                name="mobile"
                                                :rules="rules.mobile"
                                            >
                                                <a-input
                                                    placeholder="Mobile Number"
                                                    v-model:value="
                                                        formValues.mobile
                                                    "
                                                />
                                            </a-form-item>
                                        </div>
                                    </a-col>
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item
                                            :colon="false"
                                            name="profile_name"
                                            :rules="rules.profile_name"
                                        >
                                            <a-input
                                                placeholder="Contact Person"
                                                v-model:value="
                                                    formValues.profile_name
                                                "
                                            /> </a-form-item
                                    ></a-col>
                                </a-row>
                                <a-row :gutter="16">
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item
                                            :colon="false"
                                            name="password"
                                            :rules="rules.password"
                                        >
                                            <a-input
                                                placeholder="Password"
                                                type="password"
                                                v-model:value="
                                                    formValues.password
                                                "
                                            /> </a-form-item
                                    ></a-col>
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item
                                            :colon="false"
                                            name="confirm_password"
                                            :rules="rules.confirm_password"
                                        >
                                            <a-input
                                                placeholder="Confirm Password"
                                                type="password"
                                                v-model:value="
                                                    formValues.confirm_password
                                                "
                                            /> </a-form-item
                                    ></a-col>
                                </a-row>
                                <a-row :gutter="16">
                                    <a-col class="gutter-row" :span="12">
                                        <a-form-item :colon="false">
                                            <a-form-item
                                                :colon="false"
                                                name="grecaptcharesponse"
                                                :rules="
                                                    rules.grecaptcharesponse
                                                "
                                            >
                                                <div
                                                    class="g-recaptcha"
                                                    id="recaptcha"
                                                ></div>
                                            </a-form-item>
                                        </a-form-item>
                                    </a-col>
                                </a-row>
                                <a-form-item>
                                    <a-button
                                        class="login-btn"
                                        type="primary"
                                        block
                                        :loading="loading"
                                        @click="handleSubmit"
                                        >Register</a-button
                                    >
                                </a-form-item>
                            </a-form>

                            <p style="text-align: center">Or Register with</p>
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
                            <div class="login-form-bottom-2">
                                <p>
                                    Have an account?
                                    <a
                                        class="text-gray-900 hover:text-gray-700"
                                        href="/admin/login"
                                        >Log in</a
                                    >
                                </p>
                            </div>
                        </a-card>
                    </a-col>
                </a-row>
            </a-col>
            <a-col :xs="0" :sm="0" :md="24" :lg="12">
                <div class="right-login-div">
                    <img class="right-image" :src="loginBackground" />
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, onMounted, ref } from "vue";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
import axios from "axios";
import { googleTokenLogin } from "vue3-google-login";
export default defineComponent({
    setup() {
        const googleData = ref(true);
        const grecaptchaResponse = ref("");
        const { globalSetting, appType, countryCode } = common();
        const loginBackground = globalSetting.value.login_image_url;
        const store = useStore();
        const router = useRouter();
        const currectSide = ref(true);
        const { addEditRequestAdmin } = apiAdmin();
        const onRequestSend = ref({
            error: "",
            success: "",
        });
        const loading = ref(false);
        const Countries = ref([]);
        const form = ref(null);
        const recaptchaId = ref(null);
        const formValues = ref({
            company_name: "",
            company_email: "",
            mobile: "",
            profile_name: "",
            password: "",
            confirm_password: "",
            countryCode: "",
        });

        const rules = {
            company_name: [
                {
                    required: true,
                    message: "Please enter the company name",
                    trigger: "blur",
                },
            ],
            company_email: [
                {
                    required: true,
                    message: "Please enter the email address",
                    trigger: "blur",
                },
                {
                    type: "email",
                    message: "Please enter a valid email address",
                    trigger: ["blur", "change"],
                },
            ],
            mobile: [
                {
                    required: true,
                    message: "Please enter the mobile number",
                    trigger: "blur",
                },
                {
                    pattern: /^\d{8,12}$/,
                    message: "Please enter a valid mobile number",
                    trigger: ["blur"],
                },
            ],
            profile_name: [
                {
                    required: true,
                    message: "Please enter the contact person",
                    trigger: "blur",
                },
            ],
            countryCode: [
                {
                    required: true,
                    message: "Please enter the country code",
                    trigger: "blur",
                },
            ],
            password: [
                {
                    required: true,
                    message: "Please enter the password",
                    trigger: "blur",
                },
                {
                    min: 6,
                    message: "Password must be at least 6 characters",
                    trigger: "blur",
                },
                {
                    pattern: /^(?=.*[0-9])(?=.*[A-Z])(?=.*[@#$%^&+=!]).*$/,
                    message:
                        "Password must include at least one special character, one capital letter, and one number",
                    trigger: "blur",
                },
            ],
            confirm_password: [
                {
                    required: true,
                    message: "Please confirm the password",
                    trigger: "blur",
                },
                {
                    validator: (_, value) => {
                        if (value !== formValues.value.password) {
                            return Promise.reject(
                                new Error("The passwords do not match")
                            );
                        } else {
                            return Promise.resolve();
                        }
                    },
                    trigger: "blur",
                },
            ],
            grecaptcharesponse: [
                {
                    required: true,
                    validator: (rule, value) => {
                        return new Promise((resolve, reject) => {
                            if (grecaptchaResponse.value != "") {
                                resolve();
                            } else {
                                reject("Please complete the reCAPTCHA");
                            }
                        });
                    },
                    trigger: "change",
                },
            ],
        };
        const handleSubmit = () => {
            loading.value = true;
            form.value
                .validate()
                .then((response) => {
                    let data = {
                        company_name: response.company_name,
                        company_email: response.company_email,
                        password: response.password,
                        confirm_password: response.confirm_password,
                        mobile: response.mobile,
                        contact_name: response.profile_name,
                        countryCode: `+${formValues.value.countryCode}`,
                        condition: true,
                        grecaptcharesponse: grecaptchaResponse.value,
                    };
                    axios
                        .post("/save-register", data)
                        .then((response) => {
                            if (response.status == 200) {
                                onRequestSend.value.success =
                                    response.data.message;
                                const credentials = {
                                    email: data.company_email,
                                    password: data.password,
                                };
                                addEditRequestAdmin({
                                    url: "auth/login",
                                    data: credentials,
                                    success: (response) => {
                                        const user = response.user;
                                        localStorage.setItem(
                                            "helpvideos",
                                            JSON.stringify(response.helpvideos)
                                        );
                                        localStorage.setItem(
                                            "pos_type",
                                            response.pos_type
                                        );

                                        store.commit("auth/updateUser", user);
                                        store.commit(
                                            "auth/updateToken",
                                            response.token
                                        );
                                        store.commit(
                                            "auth/updateExpires",
                                            response.expires_in
                                        );
                                        store.commit(
                                            "auth/updateVisibleSubscriptionModules",
                                            response.visible_subscription_modules
                                        );
                                        store.commit(
                                            "auth/updateCssSettings",
                                            response.app.app_layout
                                        );

                                        if (appType == "single") {
                                            store.dispatch(
                                                "auth/updateAllWarehouses"
                                            );
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
                                                store.commit(
                                                    "auth/updateApp",
                                                    response.app
                                                );
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
                                                    response.app.app_layout ||
                                                        "topbar"
                                                );
                                                store.commit(
                                                    "auth/updateApp",
                                                    response.app
                                                );
                                                store.commit(
                                                    "auth/updateEmailVerifiedSetting",
                                                    response.email_setting_verified
                                                );
                                                store.commit(
                                                    "auth/updateAddMenus",
                                                    response.shortcut_menus
                                                        .credentials
                                                );
                                                store.dispatch(
                                                    "auth/updateAllWarehouses"
                                                );
                                                store.commit(
                                                    "auth/updateWarehouse",
                                                    response.user.warehouse
                                                );
                                                if (
                                                    user.name == "Admin" &&
                                                    user.is_verified == 0
                                                ) {
                                                    axios
                                                        .post("/api/otpsent", {
                                                            phone: user.mobile,
                                                            email: user.email,
                                                            name: user.contact_name,
                                                            country_code:
                                                                user.country_code,
                                                        })
                                                        .then((response) => {
                                                            router.push({
                                                                name: "admin.verify.index",
                                                                params: {
                                                                    success: true,
                                                                },
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
                                                        params: {
                                                            success: true,
                                                        },
                                                    });
                                                } else {
                                                    router.push({
                                                        name: "admin.dashboard.index",
                                                        params: {
                                                            success: true,
                                                        },
                                                    });
                                                }
                                            }
                                        }
                                    },
                                    error: (err) => {
                                        onRequestSend.value = {
                                            error: err.error.message
                                                ? err.error.message
                                                : "",
                                            success: false,
                                        };
                                    },
                                });
                            }
                        })
                        .catch((error) => {
                            loading.value = false;
                            grecaptcha.reset(recaptchaId.value);
                            grecaptchaResponse.value = "";

                            onRequestSend.value.error =
                                error.response.data.errors.company_email[0];
                        });
                })
                .catch((error) => {
                    grecaptcha.reset(recaptchaId.value);
                    grecaptchaResponse.value = "";
                    loading.value = false;
                });
        };

        const ChangeLayout = () => {
            router.push({
                name: "admin.login",
                params: { success: true },
            });
        };

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
        const loadcaptcha = async () => {
            const script = document.createElement("script");
            script.src =
                "https://www.google.com/recaptcha/api.js?onload=onRecaptchaLoad&render=explicit&hl=en"; // Update with your site key and language if needed

            script.async = true;
            script.defer = true;
            document.head.appendChild(script);

            // Create a promise that resolves when the reCAPTCHA script is loaded
            const recaptchaScriptLoaded = new Promise((resolve) => {
                window.onRecaptchaLoad = () => {
                    resolve();
                };
            });

            await recaptchaScriptLoaded;
            recaptchaId.value = grecaptcha.render("recaptcha", {
                sitekey: "6Lf3icEmAAAAAKvPB8CVHqcAEiCDzx4iOlv83Lr1",
                callback: function (response) {
                    grecaptchaResponse.value = response;
                },
            });
        };
        onMounted(async () => {
            loadcaptcha();
            axios.get("/api/get_countries").then((response) => {
                Countries.value = response.data.data;
                const filterCounter = response.data.data.find((item) => {
                    return item.iso == countryCode;
                });
                formValues.value.countryCode =
                    filterCounter && filterCounter.phonecode
                        ? filterCounter.phonecode
                        : "";
            });

            // .map((item) => {
            //         return {
            //             label: item.iso + "(+" + item.phonecode + ")",
            //             value: item.phonecode,
            //         };
            //     });
            // console.log(Countries);
        });
        return {
            loadcaptcha,
            login,
            loading,
            rules,
            form,
            formValues,
            handleSubmit,
            onRequestSend,
            globalSetting,
            currectSide,
            ChangeLayout,
            googleData,
            loginBackground,
            Countries,
            countryCode,
            innerWidth: window.innerWidth,
            recaptchaId
        };
    },
});
</script>
<style>
.phone_number_wrapper {
    display: flex;
    gap: 2%;
    align-items: flex-start;
}

.phone_number_wrapper > div:nth-child(1) {
    width: 30% !important;
}
.phone_number_wrapper > div:nth-child(2) {
    width: 68% !important;
}

.phone_number_wrapper .ant-select-selector {
    width: 100% !important;
}

.phone_number_wrapper #form_item_mobile {
    width: 100%;
}
</style>
