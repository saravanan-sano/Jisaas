import { ref } from "vue";
import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();
    const columns = [
        {
            title: t("category.category"),
            dataIndex: "name",
        },
        {
            title: t("common.total_products"),
            dataIndex: "total_products",
        },
        {
            title: t("common.total_quantity"),
            dataIndex: "quantity",
        },
        {
            title: t("common.total_subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("common.total_discount"),
            dataIndex: "discount",
        },
        {
            title: t("common.total_tax_amount"),
            dataIndex: "tax_amount",
        },
        {
            title: t("common.grand_total"),
            dataIndex: "total",
        },
    ];
    const ExtendedColumns = [
        {
            title: t("common.name"),
            dataIndex: "name",
        },
        {
            title: "HSN/SAC Code",
            dataIndex: "hsn_sac_code",
        },
        {
            title: t("product.quantity"),
            dataIndex: "quantity",
        },
        {
            title: t("product.subtotal"),
            dataIndex: "subtotal",
        },
        {
            title: t("product.discount"),
            dataIndex: "discount",
        },
        {
            title: t("product.tax"),
            dataIndex: "tax_amount",
        },
        {
            title: t("common.total"),
            dataIndex: "total",
        },
    ];
    return {
        columns,
        ExtendedColumns,
    };
};

export default fields;
