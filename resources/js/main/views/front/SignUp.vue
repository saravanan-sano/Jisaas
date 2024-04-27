<template>
    <div :style="{ textAlign: 'center' }" class="mt-10">
        <a-typography-title :level="2" :style="{ marginBottom: '0px' }">
            {{ $t("front.signup") }}
        </a-typography-title>
        <p :style="{ color: '#6b7280', fontSize: '16px' }">
            {{ $t("front.signup_using_email_password") }}
        </p>
    </div>

    <a-alert v-if="signupSuccess" type="success" class="mt-10" show-icon>
        <template #message>{{ $t("common.success") }}</template>
        <template #description>
            {{
                frontAppSetting.warehouse.ecom_visibility == 1
                    ? `Thanks for onboarding with Ekart Groceries. We are reviewing your details to approve you as our wholesale customer. Please do not sign up again. Incase of queries, please call us at (${frontAppSetting.warehouse.phone}).`
                    : $t("front.register_successfully")
            }}
            <a-button @click="loginForm = true" type="link" class="p-0">
                {{ $t("front.click_here_to_login") }}
            </a-button>
        </template>
    </a-alert>

    <a-form
        v-else
        layout="vertical"
        class="mt-30"
        :rules="rules"
        ref="form"
        :model="signupCredentials"
        @finish="onSignup"
    >
        <a-alert
            v-if="errorMessage != ''"
            :message="errorMessage"
            type="error"
            class="mb-10"
            show-icon
        />
        <a-form-item :label="$t('user.name')" name="name" :rules="rules.name">
            <a-input
                v-model:value="signupCredentials.name"
                :placeholder="
                    $t('common.placeholder_default_text', [$t('user.name')])
                "
            >
                <template #prefix>
                    <UserOutlined />
                </template>
            </a-input>
        </a-form-item>

        <a-form-item
            :label="$t('user.email')"
            name="email"
            :rules="rules.email"
        >
            <a-input
                v-model:value="signupCredentials.email"
                :placeholder="
                    $t('common.placeholder_default_text', [$t('user.email')])
                "
            >
                <template #prefix>
                    <MailOutlined />
                </template>
            </a-input>
        </a-form-item>

        <a-form-item
            :label="$t('user.phone')"
            name="phone"
            :rules="rules.phone"
        >
            <a-input
                v-model:value="signupCredentials.phone"
                :placeholder="
                    $t('common.placeholder_default_text', [$t('user.phone')])
                "
            >
                <template #prefix>
                    <PhoneOutlined />
                </template>
            </a-input>
        </a-form-item>

        <a-form-item
            :label="$t('user.password')"
            name="password"
            :rules="rules.password"
        >
            <a-input-password
                v-model:value="signupCredentials.password"
                :placeholder="
                    $t('common.placeholder_default_text', [$t('user.password')])
                "
                autocomplete="off"
            >
                <template #prefix>
                    <lock-outlined />
                </template>
            </a-input-password>
        </a-form-item>
        <a-form-item
            label="Confirm Password"
            :rules="rules.confirm_password"
            name="confirm_password"
        >
            <a-input-password
                v-model:value="signupCredentials.confirm_password"
                autocomplete="off"
                placeholder="Confirm Password"
                type="password"
            >
                <template #prefix>
                    <lock-outlined />
                </template>
            </a-input-password>
        </a-form-item>
        <div v-if="frontAppSetting.warehouse.is_login_document == 1">
            <a-form-item
                :label="$t('tax.tax_no')"
                name="tax_number"
                :rules="rules.tax_number"
            >
                <a-input
                    v-model:value="signupCredentials.tax_number"
                    :placeholder="
                        $t('common.placeholder_default_text', [
                            $t('tax.tax_no'),
                        ])
                    "
                    maxLength="49"
                >
                    <template #prefix>
                        <BorderlessTableOutlined />
                    </template>
                </a-input>
            </a-form-item>
            <a-form-item
                :label="$t('user.address')"
                name="address"
                :rules="rules.address"
            >
                <a-input
                    v-model:value="signupCredentials.address"
                    :placeholder="
                        $t('common.placeholder_default_text', [
                            $t('user.address'),
                        ])
                    "
                    maxLength="200"
                >
                    <template #prefix>
                        <FieldStringOutlined />
                    </template>
                </a-input>
            </a-form-item>
            <a-form-item label="Pincode" name="pincode" :rules="rules.pincode">
                <a-input
                    v-model:value="signupCredentials.pincode"
                    :placeholder="
                        $t('common.placeholder_default_text', ['Pincode'])
                    "
                    maxLength="10"
                >
                    <template #prefix>
                        <FieldBinaryOutlined />
                    </template>
                </a-input>
            </a-form-item>
            <a-form-item
                label="Additional Documents"
                name="addional_documents"
                :rules="rules.addional_documents"
            >
                <a-upload
                    list-type="picture"
                    :max-count="1"
                    :beforeUpload="handleFile"
                    accept=".jpg,.png,.jpeg"
                >
                    <a-button type="primary">
                        <UploadOutlined />
                        Upload
                    </a-button>
                </a-upload>
            </a-form-item>
        </div>
        <a-form-item>
            <a-button
                type="primary"
                class="mt-10"
                @click="onSignup"
                :loading="loading"
                block
            >
                {{ $t("front.signup") }}
            </a-button>
        </a-form-item>

        <a-form-item>
            <a-typography-text strong>
                {{ $t("front.already_have_account") + " " }}
            </a-typography-text>
            <a-button type="link" @click="handleForm" class="p-0">
                {{ $t("front.login") }}
            </a-button>
        </a-form-item>
    </a-form>
</template>

<script>
import { defineComponent, ref, computed } from "vue";
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
import common from "../../../common/composable/common";
import { useStore } from "vuex";
import { useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import apiFront from "../../../common/composable/apiFront";
export default defineComponent({
    emit: ["changeLoginForm"],
    components: {
        UserOutlined,
        MailOutlined,
        LockOutlined,
        PhoneOutlined,
        BorderlessTableOutlined,
        FieldStringOutlined,
        FieldBinaryOutlined,
        UploadOutlined,
    },
    setup(props, { emit }) {
        const { frontWarehouse, frontAppSetting, convertImageToBase64 } =
            common();
        const { addEditRequestFront, loading } = apiFront();

        const errorMessage = ref("");
        const { t } = useI18n();
        const router = useRouter();
        const store = useStore();
        const signupSuccess = ref(false);
        const signupCredentials = ref({
            name: null,
            phone: null,
            email: null,
            password: null,
            confirm_password: null,
            company_id: frontAppSetting.value.company_id,
            slug:frontWarehouse.value.slug,
            tax_number: null,
            address: null,
            pincode: null,
            addional_documents: undefined,
        });

        const handleForm = () => {
            emit("changeLoginForm");
        };

        const handleFile = (file) => {
            signupCredentials.value.addional_documents = file;
            return false;
        };

        const rules = {
            name: [
                {
                    required: true,
                    message: "Please enter your name",
                    trigger: "blur",
                },
            ],
            phone: [
                {
                    required: true,
                    message: "Please enter your phone number",
                    trigger: "blur",
                },
                {
                    pattern: /^[0-9]+$/,
                    message: "Please enter only numbers",
                    trigger: "blur",
                },
            ],
            password: [
                {
                    required: true,
                    message: "Please enter your password",
                    trigger: "blur",
                },
                {
                    min: 8,
                    message: "Password should be at least 8 characters",
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
                        if (
                            value !== signupCredentials.value.confirm_password
                        ) {
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
            tax_number: [
                {
                    required: false,
                    message: "Please enter your tax number",
                    trigger: "blur",
                },
            ],
            address: [
                {
                    required:
                        frontAppSetting.value.warehouse.is_login_document === 1,
                    message: "Please enter your address",
                    trigger: "blur",
                },
            ],
            pincode: [
                {
                    required:
                        frontAppSetting.value.warehouse.is_login_document === 1,
                    message: "Please enter your pincode",
                    trigger: "blur",
                },
                {
                    pattern: /^[0-9]+$/,
                    message: "Please enter only numbers",
                    trigger: "blur",
                },
            ],
            addional_documents: [
                {
                    required:
                        frontAppSetting.value.warehouse.is_login_document === 1,
                    message: "Please upload additional documents",
                    trigger: "change",
                },
            ],
        };

        const onSignup = async () => {
            errorMessage.value = "";

            // Check if all required fields are filled
            for (const field of Object.keys(rules)) {
                const fieldRules = rules[field];
                // Check each validation rule for the field
                for (const rule of fieldRules) {
                    if (rule.required && !signupCredentials.value[field]) {
                        errorMessage.value = `Fill all the required Fields.`;
                        setTimeout(() => {
                            errorMessage.value = "";
                        }, 2000);
                        return;
                    }
                }
            }

            let data = signupCredentials.value;
            if (frontAppSetting.value.warehouse.is_login_document === 1) {
                await convertImageToBase64(
                    signupCredentials.value.addional_documents
                )
                    .then((res) => (data.addional_documents = res))
                    .catch((err) => console.log(err));
            }

            addEditRequestFront({
                url: "front/signup",
                data: data,
                successMessage: t("front.register_successfully"),
                success: (res) => {
                    signupSuccess.value = true;
                    if (frontAppSetting.value.warehouse.ecom_visibility != 1) {
                        addEditRequestFront({
                            url: "front/login",
                            data: signupCredentials.value,
                            successMessage: t("front.logged_in_successfully"),
                            success: (res) => {
                                store.commit("front/updateUser", res.user);
                                store.commit("front/updateToken", res.token);
                                store.commit(
                                    "front/updateExpires",
                                    res.expires_in
                                );

                                router.push({
                                    name: "front.homepage",
                                    params: {
                                        warehouse: frontWarehouse.value.slug,
                                    },
                                });
                            },
                            error: (errorRules) => {
                                if (errorRules.error_message) {
                                    errorMessage.value =
                                        errorRules.error_message;
                                }
                            },
                        });
                    }
                },
                error: (errorRules) => {
                    if (errorRules.error_message) {
                        errorMessage.value = errorRules.error_message;
                    }
                },
            });
        };
        return {
            frontWarehouse,
            frontAppSetting,
            convertImageToBase64,
            errorMessage,
            router,
            signupSuccess,
            signupCredentials,
            handleFile,
            onSignup,
            rules,
            handleForm,
        };
    },
});
</script>
