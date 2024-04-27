<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header title="Coupons" class="p-0">
                <template
                    v-if="
                        permsArray.includes(`coupon_create`) ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <a-space>
                        <a-button type="primary" @click="addItem">
                            <PlusOutlined />
                            Add
                        </a-button>
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
                <a-breadcrumb-item> Coupons-manager </a-breadcrumb-item>
                <a-breadcrumb-item> Coupons </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <a-card class="page-content-container">
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            url="addcoupon"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />
        <div class="coupon-container">
            <div class="card-con">
                <div class="card" ref="card"></div>
            </div>
        </div>
    </a-card>
</template>
<script>
import { computed, onMounted, ref, watch } from "vue";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import common from "../../../../common/composable/common";
import { PlusOutlined } from "@ant-design/icons-vue";
import fields from "./fields";
// import CouponCard from "./CouponCard.vue";
import AddEdit from "./AddEdit.vue";
import { useI18n } from "vue-i18n";
import CouponBuilder from "./coupon-Builder/CouponBuilder.vue";
export default {
    components: { AdminPageHeader, PlusOutlined, AddEdit, CouponBuilder },
    setup() {
        const { t } = useI18n();
        const { permsArray } = common();
        const { DummyJson, initData } = fields();
        const addEditType = ref("add");
        const addEditVisible = ref(false);
        const addEditUrl = ref("coupon");

        const formData = ref({});
        const viewData = ref("");
        const pageTitle = ref("Add Coupon");
        const successMessage = ref("Added Successfully");

        const getData = () => {
            axiosAdmin
                .post("/listcoupon")
                .then((result) => {
                    console.log(result);
                })
                .catch((err) => {
                    console.log(err);
                });
        };
        onMounted(() => {
            getData()
            formData.value = { ...initData };
        });
        const addItem = () => {
            formData.value = { ...initData };
            addEditVisible.value = true;
        };
        const addEditSuccess = (data) => {
            addEditVisible.value = false;
            formData.value = { ...initData };
        };
        const onCloseAddEdit = () => {
            addEditVisible.value = false;
            console.log("formData.value", formData.value);
            formData.value = { ...initData };
            console.log("Closed");
        };
        return {
            permsArray,
            DummyJson,
            addEditType,
            addEditVisible,
            addEditUrl,
            addEditSuccess,
            onCloseAddEdit,
            formData,
            viewData,
            pageTitle,
            successMessage,
            addItem,
        };
    },
};
</script>
