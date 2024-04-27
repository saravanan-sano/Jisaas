<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.payment_gateway`)" class="p-0">
                <template
                    v-if="
                        permsArray.includes('payment_gateway_create') ||
                        permsArray.includes('admin')
                    "
                    #extra
                >
                    <!-- <a-button type="primary" @click="addItem">
                        <PlusOutlined />
                        {{ $t("payment_gateway.add") }}
                    </a-button> -->
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
                    {{ $t(`menu.settings`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.payment_gateway`) }}
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
                <!-- :url="addEditUrl" -->
                <AddEdit
                    addEditType="edit"
                    :visible="addEditVisible"
                    @addEditSuccess="addEditSuccess"
                    @closed="onCloseAddEdit"
                    :formData="formData"
                />
                <a-row class="mt-20">
                    <a-col :span="24">
                        <div class="table-responsive">
                            <a-table
                                :columns="columns"
                                :data-source="gateWayData"
                                :pagination="false"
                                :loading="false"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template
                                        v-if="column.dataIndex === 'action'"
                                    >
                                        <a-button
                                            v-if="
                                                permsArray.includes(
                                                    'payment_gateway_edit'
                                                ) ||
                                                permsArray.includes('admin')
                                            "
                                            type="primary"
                                            @click="editItem(record)"
                                            style="margin-left: 4px"
                                        >
                                            <template #icon
                                                ><EditOutlined
                                            /></template>
                                        </a-button>
                                    </template>
                                </template>
                            </a-table>
                        </div>
                    </a-col>
                </a-row>
            </a-card>
        </a-col>
    </a-row>
</template>
<script>
import { onMounted, ref } from "vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import SettingSidebar from "../SettingSidebar.vue";
import fields from "./fields";
import { EditOutlined } from "@ant-design/icons-vue";
import AddEdit from "./AddEdit.vue";

export default {
    components: { SettingSidebar, AdminPageHeader, EditOutlined, AddEdit },
    setup() {
        const { permsArray } = common();
        const { dummyData, columns } = fields();
        const gateWayData = ref([]);
        const addEditVisible = ref(false);

        const formData = ref({});

        onMounted(async () => {
            await fetchData("upi_gateway");
            await fetchData("paytm");
        });

        const addEditSuccess = async () => {
            gateWayData.value = [];

            await fetchData("upi_gateway");
            await fetchData("paytm");

            addEditVisible.value = false;
        };

        const editItem = (record) => {
            formData.value = record;
            addEditVisible.value = true;
        };
        const onCloseAddEdit = () => {
            addEditVisible.value = false;
        };
        const fetchData = async (mode) => {
            await axiosAdmin
                .post("get-payment-key", { paymentKey: mode })
                .then((result) => {
                    let data = result.data.data;
                    if (data) {
                        gateWayData.value.push({
                            ...data,
                            paymentKey: data.name_key,
                        });
                    } else {
                        gateWayData.value.push(dummyData[mode]);
                    }
                })
                .catch((err) => {
                    console.log(err);
                });
        };
        return {
            permsArray,
            gateWayData,
            columns,
            addEditVisible,
            addEditSuccess,
            onCloseAddEdit,
            formData,
            editItem,
        };
    },
};
</script>
<style></style>
