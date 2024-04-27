const fields = () => {
    const HourlyColumn = [
        {
            title: "Hour",
            dataIndex: "hour",
            align:"left"
        },
        {
            title: "Avg",
            dataIndex: "order_count",
            align:"center"
        },
        {
            title: "Avg Value",
            dataIndex: "average_order",
            align:"right"
        },
        {
            title: "Total Sales",
            dataIndex: "total_sales",
            align:"right"
        },
    ];
    const CategoryColumn = [
        {
            title: "Category",
            dataIndex: "category_name",
            align:"left"
        },
        {
            title: "Qty",
            dataIndex: "total_quantity",
            align:"center"
        },
        {
            title: "Total",
            dataIndex: "total_value",
            align:"right"
        },
    ];
    const ProductColumn = [
        {
            title: "Name",
            dataIndex: "product_name",
            align:"left"
        },
        {
            title: "Qty",
            dataIndex: "total_quantity",
            align:"center"
        },
        {
            title: "Total",
            dataIndex: "total_value",
            align:"right"
        },
    ];

    const PaymentsColumn = [
        {
            title: "Payment",
            dataIndex: "payment",
            align:"left"
        },
        {
            title: "Orders",
            dataIndex: "orders",
            align:"center"
        },
        {
            title: "Amount",
            dataIndex: "amount",
            align:"right"
        },
    ]

    return {
        HourlyColumn,
        CategoryColumn,
        ProductColumn,
        PaymentsColumn
    }
}

export default fields;
