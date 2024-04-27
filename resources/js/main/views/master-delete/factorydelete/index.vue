<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.master`)" class="p-0">
                <template
                    v-if="
                        permsArray.includes('categories_create') ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <a-space>
                        <HelpVideo
                            :pageTitle="$t('common.help_video')"
                            :FileUrl="FileUrl"
                            importUrl="brands/import"
                            @onUploadSuccess="setUrlData"
                            class="mob"
                        />
                    </a-space>
                </template>
            </a-page-header>
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.master`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <a-row>
        <a-col
            :xs="24"
            :sm="24"
            :md="24"
            :lg="4"
            :xl="4"
            class="bg-setting-sidebar"
        >
            <SettingSidebar />
        </a-col>
        <a-col :xs="24" :sm="24" :md="24" :lg="20" :xl="20">
            <a-card class="page-content-container">
                <template #title>
                    <div :style="{ marginTop: '8px', paddingBottom: '14px' }">
                        Master Delete
                    </div>
                </template>
                <a-form layout="vertical">
                    <a-row :gutter="16">
                        <a-col :xs="24" :sm="24" :md="24" :lg="24">
                            <a-row style="gap: 1rem;">
                                <a-typography-title :level="5">
                                    Choose Delete Table:
                                </a-typography-title>

                                <a-checkbox-group
                                    v-model:value="state.checkedList"
                                    style="width: 100%"
                                    @change="onCheckboxChange"
                                >
                                    <a-row>
                                        <a-col :span="6">
                                            <a-checkbox value="Product">
                                                Product
                                            </a-checkbox>
                                        </a-col>
                                        <a-col :span="6">
                                            <a-checkbox value="Order">
                                                Order
                                            </a-checkbox>
                                        </a-col>
                                        <a-col :span="6">
                                            <a-checkbox value="Brand">
                                                Brand
                                            </a-checkbox>
                                        </a-col>
                                        <a-col :span="6">
                                            <a-checkbox value="Customer">
                                                Customer
                                            </a-checkbox>
                                        </a-col>
                                    </a-row>
                                </a-checkbox-group>

                                <a-space>
                                    <a-button
                                        type="primary"
                                        @click="submitData"
                                    >
                                        Delete
                                    </a-button>
                                </a-space>
                            </a-row>
                        </a-col>
                    </a-row>
                </a-form>
            </a-card>
        </a-col>
    </a-row>

    <a-modal
        v-model="isVisible"
        title="Confirmation SMS"
        :visible="isVisible"
        @cancel="closeModal"
        @ok="sendConfirmationSMS"
    >
        <a-input v-model:value="smsCode" placeholder="Enter SMS code" />
    </a-modal>
</template>

<script>
import { ref, createVNode } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import HelpVideo from "../../../../common/core/ui/help.vue";
import common from "../../../../common/composable/common";
import { useI18n } from "vue-i18n";
import { ExclamationCircleOutlined } from "@ant-design/icons-vue";
import { Modal, notification, message } from "ant-design-vue";
import SettingSidebar from "../../settings/SettingSidebar.vue";
export default {
    components: {
        ExclamationCircleOutlined,
        AdminPageHeader,
        HelpVideo,
        SettingSidebar,
    },

    setup() {
        const isVisible = ref(false);
        const closeModal = () => {
            isVisible.value = false;
        };
        const { t } = useI18n();
        const { permsArray } = common();
        const plainOptions = ["Product", "Order", "Brand", "Customer"];
        const checkedList = ref([]);
        const smsCode = ref("");

        const onCheckboxChange = (checkedValues) => {
            // Update the checkedList with the checked values
            checkedList.value = checkedValues;
        };

        const submitData = () => {
            if (checkedList.value.length === 0) {
                // If no checkboxes are checked, show a notification or take appropriate action
                notification.error({
                    message: t("common.error"),
                    description: t("common.select_items_to_delete"),
                });
                return;
            }
            Modal.confirm({
                title: t("common.delete") + "?",
                icon: createVNode(ExclamationCircleOutlined),
                content: t("category.delete_message"),
                centered: true,
                okText: t("common.yes"),
                okType: "danger",
                cancelText: t("common.no"),
                onOk() {
                    let url = `confirmation-sms`;
                    isVisible.value = true;
                    axiosAdmin.post(url).then((res) => {
                        message.success(res.message);
                    });
                },
                onCancel() {},
            });
        };
        const sendConfirmationSMS = () => {
            if (smsCode.value != "") {
                let url = `master-delete`;
                const data = {
                    name: checkedList.value,
                    otp: smsCode.value,
                };
                axiosAdmin.post(url, data).then((res) => {
                    if (res.success == 1) {
                        isVisible.value = false;
                        message.success(res.message);
                    } else {
                        message.error(res.message);
                    }
                });
            }
        };

        return {
            isVisible,
            closeModal,
            smsCode,
            plainOptions,
            state: {
                checkedList,
            },
            permsArray,
            onCheckboxChange,
            submitData,
            sendConfirmationSMS,
        };
    },
};
</script>
<style scoped>
.checkbox-container {
    max-width: 400px;
    margin: 0 auto;
    padding: 20px;
}
</style>
