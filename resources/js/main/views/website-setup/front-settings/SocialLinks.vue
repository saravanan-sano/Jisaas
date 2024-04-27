<template>
    <div>
        <a-page-header style="padding-left: 0px; padding-bottom: 0px">
            <template #title>
                {{ $t("front_setting.social_links") }}
            </template>
        </a-page-header>
        <a-divider class="mt-5" />

        <a-form layout="vertical" class="mt-10">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.facebook')"
                        name="name"
                        :help="
                            rules.facebook_url
                                ? rules.facebook_url.message
                                : null
                        "
                        :validateStatus="rules.facebook_url ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.facebook_url"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.facebook'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.twitter')"
                        name="name"
                        :help="
                            rules.twitter_url ? rules.twitter_url.message : null
                        "
                        :validateStatus="rules.twitter_url ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.twitter_url"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.twitter'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.instagram')"
                        name="name"
                        :help="
                            rules.instagram_url
                                ? rules.instagram_url.message
                                : null
                        "
                        :validateStatus="rules.instagram_url ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.instagram_url"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.instagram'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.linkedin')"
                        name="name"
                        :help="
                            rules.linkedin_url
                                ? rules.linkedin_url.message
                                : null
                        "
                        :validateStatus="rules.linkedin_url ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.linkedin_url"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.linkedin'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>

            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.youtube')"
                        name="name"
                        :help="
                            rules.youtube_url ? rules.youtube_url.message : null
                        "
                        :validateStatus="rules.youtube_url ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.youtube_url"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.youtube'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.header1')"
                        name="name"
                        :help="
                            rules.header_line_1
                                ? rules.header_line_1.message
                                : null
                        "
                        :validateStatus="rules.header_line_1 ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.header_line_1"
                            maxlength="21"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.header_line_1'),
                                ])
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.header2')"
                        name="name"
                        :help="
                            rules.header_line_2
                                ? rules.header_line_2.message
                                : null
                        "
                        :validateStatus="rules.header_line_2 ? 'error' : null"
                    >
                        <a-input
                            v-model:value="addEditForm.formData.header_line_2"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.header2'),
                                ])
                            "
                            maxlength="21"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16" class="mb-10">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.order_thank_msg')"
                        name="name"
                        :help="
                            rules.order_thank_msg
                                ? rules.order_thank_msg.message
                                : null
                        "
                        :validateStatus="rules.order_thank_msg ? 'error' : null"
                        style="margin-bottom: 10px;"
                    >
                        <a-textarea
                            v-model:value="addEditForm.formData.order_thank_msg"
                            show-count
                            :maxlength="250"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.order_thank_msg'),
                                ])
                            "
                            style="width: 100%"
                            :auto-size="{ minRows: 2, maxRows: 5 }"
                        />
                    </a-form-item>
                    <div
                        style="
                            margin-right: 8px;
                            display: flex;
                            align-items: center;
                        "
                    >
                        <span :style="{ marginRight: '8px' }"
                            >{{
                                $t("front_setting.order_thank_msg_value")
                            }}:</span
                        >
                        <div
                            style="display: flex; gap: 5px; align-items: center"
                        >
                            <a-button
                                @click="
                                    () =>
                                        addDynamicValue(
                                            '{ORDER_NO}',
                                            'order_thank_msg'
                                        )
                                "
                                :disabled="
                                    addEditForm.formData.order_thank_msg
                                        .length >= 232
                                "
                            >
                                {ORDER_NO}
                            </a-button>
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16" class="mb-10">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.app_greeting_msg')"
                        name="name"
                        :help="
                            rules.app_greeting_msg
                                ? rules.app_greeting_msg.message
                                : null
                        "
                        :validateStatus="
                            rules.app_greeting_msg ? 'error' : null
                        "
                        style="margin-bottom: 10px;"
                    >
                        <a-textarea
                            v-model:value="
                                addEditForm.formData.app_greeting_msg
                            "
                            show-count
                            :maxlength="250"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.app_greeting_msg'),
                                ])
                            "
                            style="width: 100%"
                            :auto-size="{ minRows: 2, maxRows: 5 }"
                        />
                    </a-form-item>
                    <div style="display: flex; align-items: center">
                        <span :style="{ marginRight: '8px' }"
                            >{{
                                $t("front_setting.app_greeting_msg_value")
                            }}:</span
                        >
                        <div
                            style="display: flex; gap: 5px; align-items: center"
                        >
                            <a-button
                                @click="
                                    () =>
                                        addDynamicValue(
                                            '{WAREHOUSE_NAME}',
                                            'app_greeting_msg'
                                        )
                                "
                                :disabled="
                                    addEditForm.formData.app_greeting_msg
                                        .length >= 232
                                "
                            >
                                {WAREHOUSE_NAME}
                            </a-button>
                            <a-button
                                @click="
                                    () =>
                                        addDynamicValue(
                                            '{WAREHOUSE_NUMBER}',
                                            'app_greeting_msg'
                                        )
                                "
                                :disabled="
                                    addEditForm.formData.app_greeting_msg
                                        .length >= 232
                                "
                            >
                                {WAREHOUSE_NUMBER}
                            </a-button>
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16" class="mb-10">
                <a-col :xs="24" :sm="24" :md="12" :lg="12">
                    <a-form-item
                        :label="$t('front_setting.app_login_msg')"
                        name="name"
                        :help="
                            rules.app_login_msg
                                ? rules.app_login_msg.message
                                : null
                        "
                        :validateStatus="rules.app_login_msg ? 'error' : null"
                        style="margin-bottom: 10px;"
                    >
                        <a-textarea
                            v-model:value="addEditForm.formData.app_login_msg"
                            show-count
                            :maxlength="250"
                            :placeholder="
                                $t('front_setting.placeholder_social_text', [
                                    $t('front_setting.app_login_msg'),
                                ])
                            "
                            style="width: 100%"
                            :auto-size="{ minRows: 2, maxRows: 5 }"
                        />
                    </a-form-item>
                    <div style="display: flex; align-items: center">
                        <span :style="{ marginRight: '8px' }"
                            >{{
                                $t("front_setting.app_login_msg_value")
                            }}:</span
                        >
                        <div
                            style="display: flex; gap: 5px; align-items: center"
                        >
                            <a-button
                                @click="
                                    () =>
                                        addDynamicValue(
                                            '{WAREHOUSE_NUMBER}',
                                            'app_login_msg'
                                        )
                                "
                                :disabled="
                                    addEditForm.formData.app_login_msg.length >=
                                    232
                                "
                            >
                                {WAREHOUSE_NUMBER}
                            </a-button>
                        </div>
                    </div>
                </a-col>
            </a-row>
            <a-row :gutter="16" class="mt-30">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item>
                        <a-button
                            v-if="
                                permsArray.includes('front_settings_edit') ||
                                permsArray.includes('admin')
                            "
                            type="primary"
                            @click="onSubmit"
                        >
                            <template #icon> <SaveOutlined /> </template>
                            {{ $t("common.update") }}
                        </a-button>
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
    </div>
</template>

<script>
import { defineComponent, reactive, onMounted } from "vue";
import {
    SaveOutlined,
    SearchOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import common from "../../../../common/composable/common";

export default defineComponent({
    props: ["formData", "data", "rules"],
    emits: ["onSubmit"],
    components: {
        SaveOutlined,
        SearchOutlined,
        DeleteOutlined,
    },
    setup(props, { emit }) {
        const addEditForm = reactive({
            formSubmitting: false,
            formData: props.formData,
        });
        const { permsArray } = common();

        onMounted(() => {
            addEditForm.formData = props.formData;
        });

        const onSubmit = () => {
            emit("onSubmit", addEditForm.formData);
        };

        const addDynamicValue = (value, key) => {
            addEditForm.formData[key] =
                addEditForm.formData[key] + " " + value + " ";
        };

        return {
            addEditForm,
            onSubmit,
            permsArray,
            addDynamicValue,
        };
    },
});
</script>

<style></style>
