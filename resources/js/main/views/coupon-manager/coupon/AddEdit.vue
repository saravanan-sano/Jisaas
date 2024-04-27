<template>
    <a-drawer
        :title="pageTitle"
        :width="drawerWidth"
        :visible="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Coupon Code"
                        name="coupon_code"
                        :help="
                            rules.coupon_code ? rules.coupon_code.message : null
                        "
                        :validateStatus="rules.coupon_code ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.coupon_code"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Coupon Code'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Discount Type"
                        name="discount_type"
                        :help="
                            rules.discount_type
                                ? rules.discount_type.message
                                : null
                        "
                        :validateStatus="rules.discount_type ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.discount_type"
                            :placeholder="
                                $t('common.placeholder_default_select') +
                                'Discount Type'
                            "
                        >
                            <a-select-option value="percentage"
                                >Percentage</a-select-option
                            >
                            <a-select-option value="fixed"
                                >Fixed</a-select-option
                            >
                        </a-select>
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Discount Amount"
                        name="discount_amount"
                        :help="
                            rules.discount_amount
                                ? rules.discount_amount.message
                                : null
                        "
                        :validateStatus="rules.discount_amount ? 'error' : null"
                        class="required"
                    >
                        <a-input
                            v-model:value="formData.discount_amount"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Discount Amount'
                            "
                            ><template #addonBefore>
                                {{
                                    formData.discount_type == "fixed"
                                        ? appSetting.currency.symbol
                                        : "%"
                                }}
                            </template></a-input
                        >
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Start Date"
                        name="start_date"
                        :help="
                            rules.start_date ? rules.start_date.message : null
                        "
                        :validateStatus="rules.start_date ? 'error' : null"
                        class="required"
                    >
                        <a-date-picker
                            v-model:value="formData.start_date"
                            style="width: 100%"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Start Date'
                            "
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="End Date"
                        name="end_date"
                        :help="rules.end_date ? rules.end_date.message : null"
                        :validateStatus="rules.end_date ? 'error' : null"
                        class="required"
                    >
                        <a-date-picker
                            v-model:value="formData.end_date"
                            style="width: 100%"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'End Date'
                            "
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Minimum Spend"
                        name="minimum_spend"
                        :help="
                            rules.minimum_spend
                                ? rules.minimum_spend.message
                                : null
                        "
                        :validateStatus="rules.minimum_spend ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.minimum_spend"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Minimum Spend'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Maximum Spend"
                        name="maximum_spend"
                        :help="
                            rules.maximum_spend
                                ? rules.maximum_spend.message
                                : null
                        "
                        :validateStatus="rules.maximum_spend ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.maximum_spend"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Maximum Spend'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Use Limit"
                        name="use_limit"
                        :help="rules.use_limit ? rules.use_limit.message : null"
                        :validateStatus="rules.use_limit ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.use_limit"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Use Limit'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <!-- <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Check The Coupon Status"
                        name="status"
                        :help="rules.status ? rules.status.message : null"
                        :validateStatus="rules.status ? 'error' : null"
                        class="required"
                    >
                        <a-switch
                            v-model:checked="formData.status"
                            :checkedValue="'1'"
                            :unCheckedValue="'0'"
                        />
                    </a-form-item>
                </a-col> -->
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Use Same IP Limit"
                        name="use_same_ip_limit"
                        :help="
                            rules.use_same_ip_limit
                                ? rules.use_same_ip_limit.message
                                : null
                        "
                        :validateStatus="
                            rules.use_same_ip_limit ? 'error' : null
                        "
                    >
                        <a-input
                            v-model:value="formData.use_same_ip_limit"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Use Same IP Limit'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="User Limit"
                        name="user_limit"
                        :help="
                            rules.user_limit ? rules.user_limit.message : null
                        "
                        :validateStatus="rules.user_limit ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.user_limit"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'User Limit'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Use Device"
                        name="use_device"
                        :help="
                            rules.use_device ? rules.use_device.message : null
                        "
                        :validateStatus="rules.use_device ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.use_device"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Use Device'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Multiple Use"
                        name="multiple_use"
                        :help="
                            rules.multiple_use
                                ? rules.multiple_use.message
                                : null
                        "
                        :validateStatus="rules.multiple_use ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.multiple_use"
                            :placeholder="
                                $t('common.placeholder_default_text') +
                                'Multiple Use'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="6" :lg="6">
                    <a-form-item
                        label="Vendor"
                        name="vendor_id"
                        :help="rules.vendor_id ? rules.vendor_id.message : null"
                        :validateStatus="rules.vendor_id ? 'error' : null"
                    >
                        <a-input
                            v-model:value="formData.vendor_id"
                            :placeholder="
                                $t('common.placeholder_default_text') + 'Vendor'
                            "
                            maxLength="49"
                        />
                    </a-form-item>
                </a-col>
            </a-row>
            <CouponBuilder
                @changeStyles="styleChange"
                :value="formData.coupon_style"
            />
        </a-form>
        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
            <a-button
                type="primary"
                :loading="loading"
                @click="onSubmit"
                style="margin-left: 8px"
                :disabled="displayMessageForHSN || isWholeSaleValidated"
            >
                <template #icon>
                    <SaveOutlined />
                </template>
                {{
                    addEditType == "add"
                        ? $t("common.create")
                        : $t("common.update")
                }}
            </a-button>
        </template>
    </a-drawer>
</template>
<script>
import { defineComponent, ref, onMounted, watch, computed } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    BarcodeOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import common from "../../../../common/composable/common";
import apiAdmin from "../../../../common/composable/apiAdmin";
import CouponBuilder from "./coupon-Builder/CouponBuilder.vue";

export default defineComponent({
    props: [
        "formData",
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        DeleteOutlined,
        BarcodeOutlined,
        CouponBuilder,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const { appSetting } = common();
        const { t } = useI18n();

        onMounted(() => {});
        const styleChange = (data) => {
            props.formData.coupon_style = data;
        };

        const onSubmit = () => {
            emit("addEditSuccess");
            addEditRequestAdmin({
                url: props.url,
                data: props.formData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        return {
            t,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
            loading,
            rules,
            onClose,
            onSubmit,
            styleChange,
            appSetting,
        };
    },
});
</script>

<style>
.ant-calendar-picker {
    width: 100%;
}

.ant-input-number.error .ant-input-number-input {
    border: 1px solid red !important;
}
</style>
