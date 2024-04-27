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
                                 <div  v-if="!showsuccess">
                                <a-form-item name="email" >
                                    <div
                                        id="label"
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: end;
                                        "
                                    >
                                        {{ $t("user.email") }}
                                        <!-- <a-button
                                            v-if="editEmail"
                                            type="link"
                                            @click="toggleEditEmail"
                                            ><EditOutlined /> Edit</a-button
                                        > -->
                                        <a-button

                                            type="link"
                                            @click="resetpassword"
                                            ><EditOutlined /> Send OTP</a-button
                                        >
                                    </div>
                                    <a-input
                                        v-model:value="formState['email']"
                                        @pressEnter="resetpassword"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                [$t('user.email')]
                                            )
                                        "
                                    />
                                </a-form-item>


                                <a-form-item
                                    v-if="!showpassword"
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
                                        :disabled="editMobile"
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

                                <a-form-item class="mt-30" v-if="!showpassword">
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



                                 <a-form-item
                                  v-if="showpassword"
                                    label="Password"
                                    name="password"
                                    :rules="[
                                        {
                                            required: true,
                                            message: 'Please Enter password',
                                        },
                                    ]"
                                    validateStatus="true"

                                >

                                    <a-input-password
                                        v-model:value="formState.password"
                                        @pressEnter="onSubmit"
                                        :disabled="editMobile"
                                        maxLength="15"
                                        min="8"
                                        :placeholder="
                                            $t(
                                                'common.placeholder_default_text',
                                                ['password']
                                            )
                                        "
                                    />
                                </a-form-item>

                                  <a-form-item class="mt-30"  v-if="showpassword">
                                    <div
                                        style="
                                            display: flex;
                                            justify-content: space-between;
                                            align-items: center;
                                        "
                                    >

                                        <a-button
                                            :loading="sendLoading"
                                            html-type="submit"
                                            type="primary"
                                            :disabled="formState.password.length < 7"
                                        >
                                            Submit
                                        </a-button>




                                    </div>
                                </a-form-item>
                            </div>
                            </a-form>
    <div v-if="showsuccess">
        Please <a class="text-gray-900 hover:text-gray-700"
                                                href="/admin/login">click here</a> to login
        </div>

                        </a-card>
                    </a-col>
                </a-row>
            </a-col>
            <a-col :xs="0" :sm="0" :md="24" :lg="16">
                <div class="right-login-div pad-30">
                    <!-- <img class="right-image" :src="loginBackground" /> -->
                    <div>
						<p>For security purposes, we have sent a One-Time Password (OTP) to both your mobile number via SMS and your WhatsApp account. Please check your mobile device and WhatsApp messages for the OTP. If you do not receive the OTP within a few minutes, please click on the "Resend OTP" button to generate a new OTP.</p>
                        <p>
                          To maintain the security of your account, we recommend following these best practices:
                        </p>

                        <ul>
                            <li>Create a strong and unique password that combines uppercase and lowercase letters, numbers, and special characters.</li>
                            <li>Avoid using easily guessable information such as names, birthdates, or common words.</li>
                            <li>Regularly update your password every few months to enhance security.</li>
                            <li>Never share your password with anyone or write it down in an easily accessible place.</li>
                        </ul>


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
        const editEmail = ref(false);
        const showpassword = ref(false);
        const showsuccess = ref(false);
        const editMobile = ref(true);
        const formState = reactive({
            email: email,
            mobile: mobile,
            otp: "",
            password: "",
        });
        const resendLoading = ref(false);
        const sendLoading = ref(false);

        const toggleEditEmail = () => {
            editEmail.value = true;
        };
        const toggleEditMobile = () => {
            editMobile.value = false;
        };
        const resetpassword = () => {
            axiosAdmin
                .post("resetpassword", {
                    email: formState.email
                })
                .then((response) => {
                    if (response.status=="success")
                    {
                    onRequestSend.value.success = response.message;
                    }
                    else
                    {
                    onRequestSend.value.error = response.message;
                    }

                    editMobile.value = false;
                })
                .catch((error) => {
                  editMobile.value = true;
                });
        };



        const onSubmit = (values) => {
            sendLoading.value = true;

            if (values.password==undefined)
            {
            axiosAdmin
                .post("verifyotp_resetpassword", values)
                .then((response) => {
                    if (response.message == "Invalid OTP.") {
                        onRequestSend.value.error = response.message;
                        sendLoading.value = false;
                    } else {
                        onRequestSend.value.success = response.message;
                        sendLoading.value = false;
                        showpassword.value = true;
                    }
                })
                .catch((error) => {
                    sendLoading.value = true;
                });
            }
            else
            {
            axiosAdmin
                .post("resetpassword_save", values)
                .then((response) => {
                    if (response.message == "Invalid.") {
                        onRequestSend.value.error = response.message;
                        sendLoading.value = false;
                    } else {
                        onRequestSend.value.success = response.message;
                        sendLoading.value = false;
                        showsuccess.value = true;



                        // router.push({
                        //     name: "admin.setup_app.index",
                        //     params: { success: true },
                        // });
                    }
                })
                .catch((error) => {
                    sendLoading.value = true;
                });
            }





        };

        const resendOtp = () => {
            resendLoading.value = true;
            axios
                .post("/api/otpsent", {
                   email: user.email,name: user.contact_name,country_code:user.country_code
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
   showpassword.value = false;
   showsuccess.value = false;
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
            resendLoading,
            sendLoading,
            resetpassword,
            showpassword,
            showsuccess,


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
