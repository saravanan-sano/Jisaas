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
                            <a-form
                                :model="formState"
                                layout="vertical"
                                autocomplete="off"
                                @finish="onSubmit"
                            >
                                <div class="login-logo mb-30">
                                   <a href="/"> <img
                                        class="login-img-logo"
                                        :src="globalSetting.dark_logo_url"
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
                                    :message="onRequestSend.success"
                                    type="success"
                                    show-icon
                                    class="mb-20 mt-10"
                                />
                                <a-form-item name="email">
                                    <div
                                        id="label"
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: end;
                                        "
                                    >
                                        {{ $t("user.email") }}
                                        <a-button
                                            v-if="editEmail"
                                            type="link"
                                            @click="toggleEditEmail"
                                            ><EditOutlined /> Edit</a-button
                                        >
                                        <a-button
                                            v-else
                                            type="link"
                                            @click="updateUser"
                                            ><EditOutlined /> Done</a-button
                                        >
                                    </div>
                                    <a-input
                                        v-model:value="formState['email']"
                                        @pressEnter="onSubmit"
                                        :disabled="editEmail"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.email_phone')]
                                            )
                                        "
                                    />
                                </a-form-item>
                                <a-form-item name="mobile">
                                    <div
                                        id="label"
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: end;
                                        "
                                    >
                                        {{ $t("user.phone") }}
                                        <a-button
                                            v-if="editMobile"
                                            type="link"
                                            @click="toggleEditMobile"
                                            ><EditOutlined /> Edit</a-button
                                        >
                                        <a-button
                                            v-else
                                            type="link"
                                            @click="updateUser"
                                            ><EditOutlined /> Done</a-button
                                        >
                                    </div>
                                    <a-input
                                        v-model:value="formState['mobile']"
                                        @pressEnter="onSubmit"
                                        :disabled="editMobile"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.mobile')]
                                            )
                                        "
                                    />
                                </a-form-item>

                                <a-form-item
                                    label="OTP"
                                    name="otp"
                                    :rules="[
                                        {
                                            required: true,
                                            message: 'Please Enter A valid OTP',
                                        },
                                    ]"
                                    validateStatus="true"
                                >
                                    <a-input
                                        v-model:value="formState.otp"
                                        @pressEnter="onSubmit"
                                        maxLength="6"
                                        min="6"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                ['OTP']
                                            )
                                        "
                                    />
                                </a-form-item>

                                <a-form-item class="mt-30">
                                    <div
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                        "
                                    >
                                        <a-button
                                            :loading="resendLoading"
                                            type="primary"
                                            @click="resendOtp"
                                            :disabled="
                                                !editEmail ||
                                                !editMobile ||
                                                formState.otp.length == 6
                                            "
                                        >
                                            Re-send OTP
                                        </a-button>
                                        <a-button
                                            :loading="sendLoading"
                                            html-type="submit"
                                            type="primary"
                                            :disabled="formState.otp.length < 6"
                                        >
                                            Verify
                                        </a-button>
                                    </div>
                                </a-form-item>
                            </a-form>
                        </a-card>
                    </a-col>
                </a-row>
            </a-col>
            <a-col :xs="0" :sm="0" :md="24" :lg="16">
                <div class="right-login-div pad-30">
                    <!-- <img class="right-image" :src="loginBackground" /> -->
                    <div>
						<h2>Thank you for making your first login.</h2>
                        <p>
                           <b> Hello and
                            welcome to the JnanaERP family.</b> We have sent you the
                            one-time password through SMS, Whatsapp, and email
                            for your convenience. This is a complete Self Start
                            application. After verifying your OTP, you must
                            complete the following steps to begin your business.
                        </p>

                        <ul>
                            <li>1. Establish your first store or warehouse.</li>
                            <li>2. Company Formation</li>
                        </ul>

                        <p>
                            The above four stages should take no more than three
                            minutes to accomplish. If you have any questions,
                            please contact us. We will be pleased to assist you
                            with the setup and training if necessary.

                            Need Help please call us or WhatsApp : +91 7871234509
                        </p>
                    </div>
                </div>
            </a-col>
        </a-row>
    </div>
</template>

<script>
import { defineComponent, onMounted, reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";
import common from "../../../common/composable/common";
import apiAdmin from "../../../common/composable/apiAdmin";
import { EditOutlined, CheckOutlined } from "@ant-design/icons-vue";
import axios from "axios";

export default defineComponent({
    components: { EditOutlined, CheckOutlined },
    setup() {
        const { addEditRequestAdmin,loading, rules } = apiAdmin();
        const { globalSetting } = common();
        const loginBackground = globalSetting.value.login_image_url;
        const router = useRouter();
        const store = useStore();
        const onRequestSend = ref({
            error: "",
            success: "",
        });

        const disabled = ref(true);
       // let user = JSON.parse(localStorage.getItem("auth_user"));
        let user =JSON.parse(sessionStorage.getItem('loginData'));
       if (sessionStorage.getItem('loginData')==null)
       {

         user = JSON.parse(localStorage.getItem("auth_user"));
       }
        const email = ref("");
        const mobile = ref("");
        const editEmail = ref(true);
        const editMobile = ref(true);
        const formState = reactive({
            email: email,
            mobile: mobile,
            otp: "",
        });
        const resendLoading = ref(false);
        const sendLoading = ref(false);

        const toggleEditEmail = () => {
            editEmail.value = false;
        };
        const toggleEditMobile = () => {
            editMobile.value = false;
        };
        const updateUser = () => {
            axiosAdmin
                .post("updatecontact", {
                    email: formState.email,
                    mobile: formState.mobile,
                })
                .then((response) => {
                    onRequestSend.value.success = response.message;
                    editEmail.value = true;
                    editMobile.value = true;
                })
                .catch((error) => {
                    editEmail.value = true;
                    editMobile.value = true;
                });
        };

        const onSubmit = (values) => {
            sendLoading.value = true;
            axiosAdmin
                .post("verifyotp", values)
                .then((response) => {
                    if (response.message == "Invalid OTP.") {
                        onRequestSend.value.error = response.message;
                        sendLoading.value = false;
                    } else {
                        onRequestSend.value.success = response.message;
                        sendLoading.value = false;
            if (sessionStorage.getItem('loginData')==null)
            {
                router.push({
                            name: "admin.setup_app.index",
                            params: { success: true },
                        });
            }
            else
            {
                const credentials =JSON.parse(sessionStorage.getItem('loginData'));
                addEditRequestAdmin({
                url: "auth/login",
                data: credentials,
                success: (response) => {

                    const user = response.user;
                    localStorage.setItem("helpvideos", JSON.stringify(response.helpvideos))
                    localStorage.setItem("pos_type", response.pos_type)
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
                },
                error: (err) => {
                    onRequestSend.value = {
                        error: err.error.message ? err.error.message : "",
                        success: false,
                    };
                },
            });
            }

                        // router.push({
                        //     name: "admin.setup_app.index",
                        //     params: { success: true },
                        // });
                    }
                })
                .catch((error) => {
                    sendLoading.value = true;
                });



            // return


            // axiosAdmin
            //     .post("verifyotp", values)
            //     .then((response) => {
            //         if (response.message == "Invalid OTP.") {
            //             onRequestSend.value.error = response.message;
            //             sendLoading.value = false;
            //         } else {
            //             onRequestSend.value.success = response.message;
            //             sendLoading.value = false;


            //             router.push({
            //                 name: "admin.setup_app.index",
            //                 params: { success: true },
            //             });
            //         }
            //     })
            //     .catch((error) => {
            //         sendLoading.value = true;
            //     });
        };

        const resendOtp = () => {
            resendLoading.value = true;
            axios
                .post("/api/otpsent", {
                    phone: formState.mobile,email: user.email,name: user.contact_name,country_code:user.country_code
                })
                .then((response) => {
                    onRequestSend.value.success = response.data.message;
                    resendLoading.value = false;
                })
                .catch((error) => {
                    console.log(error);
                    resendLoading.value = false;
                });
        };



        onMounted(() => {

            mobile.value = user.mobile;
            email.value = user.email;
        });

        return {
            loading,
            rules,
            onRequestSend,
            globalSetting,
            loginBackground,
            formState,
            onSubmit,
            resendOtp,
            disabled,
            editEmail,
            editMobile,
            toggleEditEmail,
            toggleEditMobile,
            updateUser,
            resendLoading,
            sendLoading,

            innerWidth: window.innerWidth,
        };
    },
});
</script>

<style lang="less">
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
    background: #673bb7;
    height: 100%;
    display: flex;
    align-items: center;
    background-repeat: no-repeat;
    background-size: inherit;
    background-blend-mode: color-burn;
    padding: 50px;
}
.right-login-div > div > p {
    font-size: 16px;
    letter-spacing: 1px;
    word-spacing: 2px;
    line-height: 26px;
	color:#fff;
}

.right-login-div > div > ul {
    list-style: none;
}

.right-login-div > div > ul > li {
    font-size: 16px;
    line-height: 28px;
	color:#fff;
}

.right-image {
    width: 100%;
    display: block;
    margin: 0 auto;
    height: calc(100vh);
}

.login-btn,
.login-btn:hover,
.login-btn:active {
    background: #673bb7 !important;
    border-color: #673bb7 !important;
    border-radius: 5px;
    color: #fff !important;
}
.right-login-div h2 {
	color: #fff;
	font-size: 28px;
}
</style>
