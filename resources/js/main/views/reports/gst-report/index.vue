<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header title="GST Report" class="p-0" />
        </template>
        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item> GST Report </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>
    <a-card class="page-content-container">
        <a-row :gutter="15" class="mb-20">
            <a-col :xs="24" :sm="24" :md="10" :lg="6" :xl="6">
                <a-range-picker
                    size="large"
                    v-model:value="selectedRange"
                    @change="selectRange"
                />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="6"
                v-if="selectedRange != null"
            >
                <a-select
                    ref="select"
                    v-model:value="selectedReportType"
                    style="width: 350px"
                    size="large"
                >
                    <a-select-option value="none"
                        >Select a Report</a-select-option
                    >
                    <a-select-option value="GSTR1">GSTR 1</a-select-option>
                    <a-select-option value="GSTR2">GSTR 2</a-select-option>
                    <a-select-option value="B2B">B2B</a-select-option>
                    <a-select-option value="B2C">B2C</a-select-option>
                    <a-select-option value="HSN/SAC">HSN/SAC</a-select-option>
                </a-select>
            </a-col>
        </a-row>
        <a-row :gutter="15" class="mb-20">
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="3"
                v-if="selectedReportType === 'GSTR1' && selectedRange != null"
            >
                <GSTR1 :selectedRange="selectedRange" />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="3"
                v-if="selectedReportType === 'GSTR2' && selectedRange != null"
            >
                <GSTR2 :selectedRange="selectedRange" />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="3"
                v-if="selectedReportType === 'B2B' && selectedRange != null"
            >
                <B2B :selectedRange="selectedRange"/>
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="3"
                v-if="selectedReportType === 'B2C' && selectedRange != null"
            >
                <B2C :selectedRange="selectedRange" />
            </a-col>
            <a-col
                :xs="24"
                :sm="24"
                :md="10"
                :lg="6"
                :xl="3"
                v-if="selectedReportType === 'HSN/SAC' && selectedRange != null"
            >
                <HSN :selectedRange="selectedRange" />
            </a-col>
        </a-row>
    </a-card>
</template>
<script>
import { ref } from "vue";
import GSTR1 from "./GSTR1.vue";
import GSTR2 from "./GSTR2.vue";
import B2C from "./B2C.vue";
import B2B from "./B2B.vue";
import HSN from "./HSN.vue";

export default {
    components: {
        GSTR1,
        GSTR2,
        B2C,
        B2B,
        HSN
    },
    setup() {
        const selectedRange = ref(null);

        const selectRange = (dates) => {
            if (dates && dates.length === 2) {
                selectedRange.value = dates;
            }
        };
        return {
            GSTR1,
            GSTR2,
            B2C,
            B2B,
            HSN,
            selectRange,
            selectedRange,
            selectedReportType: ref("none"),
        };
    },
};
</script>
<style></style>
