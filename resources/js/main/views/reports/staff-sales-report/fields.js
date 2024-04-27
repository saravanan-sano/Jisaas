import { useI18n } from "vue-i18n";

const fields = () => {
    const { t } = useI18n();

    const Columns = [
        {
            title: "Name",
            dataIndex: "name",
            dbKey: "name",
        },
        {
            title: "Phone",
            dataIndex: "phone",
            dbKey: "phone",
        },
        {
            title: "Tax No",
            dataIndex: "tax_no",
        },
        {
            title: "Total Orders",
            dataIndex: "total_orders",
        },
    ];

    return { Columns };
};
export default fields;
