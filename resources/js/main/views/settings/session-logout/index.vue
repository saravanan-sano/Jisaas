<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="'Session logout'" class="p-0">
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
                    {{ "Session logout" }}
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
                <a-row :gutter="[15, 15]" class="mb-20">
                    <a-col :xs="8" :sm="8" :md="8" :lg="8" :xl="8">
                        <a-input-search
                            style="width: 75%"
                            v-model:value="searchString"
                            placeholder="Search by Email"
                            show-search
                            @change="onTableSearch"
                            @search="onTableSearch"
                            :loading="filterLoading"
                        />
                    </a-col>
                    <!-- Clear All the Session -->
                    <!-- <a-col :xs="16" :sm="16" :md="16" :lg="16" :xl="16">
                        <div style="display: flex; justify-content: flex-end">
                            <a-popconfirm
                                title="Are you sure to Logout all the sessions?"
                                ok-text="Yes"
                                cancel-text="No"
                                @confirm="shortBookDelete(record)"
                            >
                                <a-button type="primary" danger>
                                    <template #icon
                                        ><LogoutOutlined />
                                    </template>
                                    Logout All Sessions
                                </a-button>
                            </a-popconfirm>
                        </div>
                    </a-col> -->
                </a-row>
                <a-row class="mt-20">
                    <a-col :span="24">
                        <div class="table-responsive">
                            <a-table
                                :columns="columns"
                                :dataSource="SessionUser"
                                :pagination="{ defaultPageSize: 10 }"
                            >
                                <template #bodyCell="{ column, record }">
                                    <template
                                        v-if="column.dataIndex === 'name'"
                                    >
                                        {{ record.name }}
                                    </template>
                                    <template
                                        v-if="column.dataIndex === 'email'"
                                    >
                                        {{ record.email }}
                                    </template>
                                    <template
                                        v-if="
                                            column.dataIndex ===
                                            'operation_system'
                                        "
                                    >
                                        {{ record.operation_system }}
                                    </template>
                                    <template
                                        v-if="column.dataIndex === 'browser'"
                                    >
                                        {{ record.browser }}
                                    </template>
                                    <template
                                        v-if="column.dataIndex === 'date'"
                                    >
                                        {{ formatDateTime(record.created_at) }}
                                    </template>
                                    <template
                                        v-if="column.dataIndex === 'action'"
                                    >
                                        <a-popconfirm
                                            title="Are you sure to Delete?"
                                            ok-text="Yes"
                                            cancel-text="No"
                                            @confirm="shortBookDelete(record)"
                                        >
                                            <a-button
                                                type="danger"
                                                style="margin-right: 4px"
                                            >
                                                <template #icon
                                                    ><DeleteOutlined
                                                /></template>
                                            </a-button>
                                        </a-popconfirm>
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
import HelpVideo from "../../../../common/core/ui/help.vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import { useI18n } from "vue-i18n";
import SettingSidebar from "../SettingSidebar.vue";
import { DeleteOutlined } from "@ant-design/icons-vue";
import { onMounted, ref } from "vue";
import { message } from "ant-design-vue";
import fields from "./fields";
import { LogoutOutlined } from "@ant-design/icons-vue";
export default {
    components: {
        AdminPageHeader,
        HelpVideo,
        DeleteOutlined,
        SettingSidebar,
        AdminPageHeader,
        LogoutOutlined,
    },
    setup() {
        const { t } = useI18n();
        const { formatDateTime, permsArray } = common();
        const SessionUser = ref([]);
        const { columns } = fields();
        const searchString = ref("");
        const filterLoading = ref(true);
        const onTableSearch = (event) => {
            const searchTerm = event.target.value;

            if (searchTerm != "") {
                const filteredData = SessionUser.value.filter((item) => {
                    // Adjust the condition based on your actual data structure
                    return item.email === "" || item.email.includes(searchTerm);
                });

                SessionUser.value = filteredData;
            } else {
                fetch();
            }
        };

        onMounted(() => {
            fetch();
        });

        const shortBookDelete = (record) => {
            const data = {
                token: record.token,
                refresh_token: record.refresh_token,
            };
            axiosAdmin.post(`sessionclear`, data).then((res) => {
                message.success(`Logout Device`);
                filterLoading.value = true;
                fetch();
            });
        };

        const fetch = () => {
            let url = `sessionuser`;
            axiosAdmin.get(url).then((res) => {
                SessionUser.value = res.data;
                filterLoading.value = false;
            });
        };

        return {
            shortBookDelete,
            fetch,
            SessionUser,
            permsArray,
            formatDateTime,
            columns,
            searchString,
            filterLoading,
            onTableSearch,
        };
    },
};
</script>
