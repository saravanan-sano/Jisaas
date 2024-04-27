<template>
    <div class="front-login-wrp">
        <div v-if="loginForm" style="width: 100%">
            <div :style="{ textAlign: 'center' }" class="mt-10">
                <a-typography-title :level="2" :style="{ marginBottom: '0px' }">
                    {{ $t("front.login") }}
                </a-typography-title>
                <p :style="{ color: '#6b7280', fontSize: '16px' }">
                    {{ $t("front.login_using_email_password") }}
                </p>
            </div>

            <a-form layout="vertical" class="mt-30">
                <a-alert
                    v-if="errorMessage != ''"
                    :message="errorMessage"
                    type="error"
                    class="mb-10"
                    show-icon
                />

                <a-form-item
                    :label="$t('user.email_phone')"
                    name="email"
                    :help="rules.email ? rules.email.message : null"
                    :validateStatus="rules.email ? 'error' : null"
                >
                    <a-input
                        v-model:value="credentials.email"
                        @pressEnter="onLogin"
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('user.email_phone'),
                            ])
                        "
                    >
                        <template #prefix>
                            <mail-outlined />
                        </template>
                    </a-input>
                </a-form-item>

                <a-form-item
                    :label="$t('user.password')"
                    name="password"
                    :help="rules.password ? rules.password.message : null"
                    :validateStatus="rules.password ? 'error' : null"
                >
                    <a-input-password
                        v-model:value="credentials.password"
                        @pressEnter="onLogin"
                        :placeholder="
                            $t('common.placeholder_default_text', [
                                $t('user.password'),
                            ])
                        "
                        autocomplete="off"
                    >
                        <template #prefix>
                            <lock-outlined />
                        </template>
                    </a-input-password>
                </a-form-item>

                <a-form-item>
                    <a-button
                        type="primary"
                        class="mt-10"
                        @click="onLogin"
                        :loading="loading"
                        block
                    >
                        {{ $t("front.login") }}
                    </a-button>
                </a-form-item>

                <a-form-item>
                    <a-typography-text strong>
                        {{ $t("front.dont_have_account") + " " }}
                    </a-typography-text>
                    <a-button
                        @click="loginForm = false"
                        type="link"
                        class="p-0"
                    >
                        {{ $t("front.signup") }}
                    </a-button>
                </a-form-item>
            </a-form>
        </div>

        <div v-else style="width: 100%">
            <SignUp @changeLoginForm="changeLoginForm" />
        </div>
    </div>
</template>
<script>
import { defineComponent, ref, computed, watch, onMounted } from "vue";
import {
    UserOutlined,
    MailOutlined,
    LockOutlined,
    PhoneOutlined,
    BorderlessTableOutlined,
    FieldStringOutlined,
    FieldBinaryOutlined,
    UploadOutlined,
} from "@ant-design/icons-vue";
import { useStore } from "vuex";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import apiFront from "../../../common/composable/apiFront";
import common from "../../../common/composable/common";
import UploadFront from "../../../common/core/ui/file/UploadFront.vue";
import SignUp from "./SignUp.vue";
import cart from "../../../common/composable/cart";
export default defineComponent({
    props: [],
    emits: [],
    components: {
        UserOutlined,
        MailOutlined,
        LockOutlined,
        PhoneOutlined,
        BorderlessTableOutlined,
        FieldStringOutlined,
        FieldBinaryOutlined,
        UploadFront,
        UploadOutlined,
        SignUp,
    },
    setup(props, { emit }) {
        const { frontWarehouse, frontAppSetting } = common();
        const { UpdateCartItems, fetchLatestCartItems } = cart();
        const store = useStore();
        const credentials = ref({
            email: null,
            password: null,
            company_id: frontAppSetting.value.company_id,
        });

        const { t } = useI18n();
        const router = useRouter();
        const loginForm = ref(true);

        const { addEditRequestFront, loading, rules } = apiFront();

        const errorMessage = ref("");

        const onLogin = () => {
            errorMessage.value = "";

            addEditRequestFront({
                url: "front/login",
                data: credentials.value,
                successMessage: t("front.logged_in_successfully"),
                success: (res) => {
                    store.commit("front/updateUser", res.user);
                    store.commit("front/updateToken", res.token);
                    store.commit("front/updateExpires", res.expires_in);
                    let offlineCartItem = store.state.front.cartItems;

                    if (offlineCartItem.length > 0) {
                        axiosFront
                            .get(`front/self/cart-items/${res.user.xid}`)
                            .then((response) => {
                                let updatedCartItem = [];
                                let newCartItem = [];
                                response[0].cart_item.map((item) => {
                                    offlineCartItem.map((off) => {
                                        if (item.xid === off.xid) {
                                            updatedCartItem.push({
                                                ...item,
                                                cart_quantity:
                                                    off.cart_quantity +
                                                    item.cart_quantity,
                                            });
                                        } else {
                                            updatedCartItem.push(off);
                                            newCartItem.push(item);
                                        }
                                    });
                                });
                                updatedCartItem = _.uniqBy(
                                    updatedCartItem,
                                    "xid"
                                );
                                let newdata = updatedCartItem.concat(
                                    _.uniqBy(newCartItem, "xid")
                                );

                                let data = {
                                    userid: res.user.xid,
                                    cart_item: JSON.stringify(newdata),
                                };

                                axiosFront
                                    .post(`front/self/cart-items`, data)
                                    .then((response) => {
                                        fetchLatestCartItems();
                                    })
                                    .catch((error) => {
                                        console.log(error);
                                    });
                            });
                    }
                    router.push({
                        name: "front.homepage",
                        params: { warehouse: frontWarehouse.value.slug },
                    });
                },
                error: (errorRules) => {
                    console.log(errorRules);
                    if (errorRules.error_message) {
                        errorMessage.value = errorRules.error_message;
                    }
                },
            });
        };

        const changeLoginForm = () => {
            loginForm.value = true;
        };

        return {
            credentials,
            rules,
            onLogin,
            errorMessage,
            loading,
            frontWarehouse,
            user: computed(() => store.state.front.user),
            isLoggedIn: computed(() => store.getters["front/isLoggedIn"]),
            loginForm,
            frontAppSetting,
            changeLoginForm,
        };
    },
});
</script>
<style>
.front-login-wrp {
    width: 100%;
    max-width: 35%;
    margin: auto;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>
