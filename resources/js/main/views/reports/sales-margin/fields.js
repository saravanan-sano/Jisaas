
import { useI18n } from "vue-i18n";
import common from "../../../../common/composable/common";

const fields = () => {
    const { t } = useI18n();
    const { formatDateTime, formatAmountCurrency } = common();

    const salesMarginColumn = [
        {
            title: t("sales.date"),
            dataIndex: "order_date",
        },
        {
            title: t("product.purchase_price"),
            dataIndex: "purchase_price",
        },
        {
            title: t("product.margin"),
            dataIndex: "margin",
        },
        {
            title: t("product.sales_price"),
            dataIndex: "sales_price",
        },
    ];

    return {
        salesMarginColumn,
    }
}

export default fields;
