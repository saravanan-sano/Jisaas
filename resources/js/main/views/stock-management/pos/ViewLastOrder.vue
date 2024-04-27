<template>
    <a-drawer
        :title="t('menu.sales')"
        :width="drawerWidth"
        :visible="visible"
        :body-style="{ paddingBottom: '80px' }"
        :footer-style="{ textAlign: 'right' }"
        :maskClosable="false"
        @close="onClose"
    >
        <OrderTable orderType="sales" :filters="filters" routeBack="pos"/>
        <template #footer>
            <a-button @click="onClose">
                {{ $t("common.cancel") }}
            </a-button>
        </template>
    </a-drawer>
</template>

<script>
import { defineComponent, ref } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
    BarcodeOutlined,
    DeleteOutlined,
} from "@ant-design/icons-vue";
import { useI18n } from "vue-i18n";
import OrderTable from "../../../components/order/OrderTable.vue";
export default defineComponent({
    props: ["visible"],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
        BarcodeOutlined,
        DeleteOutlined,
        OrderTable,
    },
    emits: ["closed"],
    setup(props, { emit }) {
        const onClose = () => {
            emit("closed");
        };
         const { t } = useI18n();


const filters = ref({
			payment_status: "all",
			user_id: undefined,
			dates: [],
			searchColumn: "invoice_number",
			searchString: "",
		});

        return {
            t,
            onClose,
            filters,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "60%",
        };
    },
});
</script>
