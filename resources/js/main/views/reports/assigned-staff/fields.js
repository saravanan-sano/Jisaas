const fields = () => {
    const AssignedColumn = [
        {
            title: "S.No",
            dataIndex: "index",
        },
        {
            title: "Name",
            dataIndex: "name",
        },
        {
            title: "Customer Count",
            dataIndex: "customer_count",
        },
    ];
    const ExtendedCustomerColumn = [
        {
            title: "Name",
            dataIndex: "name",
        },
        {
            title: "Email",
            dataIndex: "email",
        },
        {
            title: "Phone",
            dataIndex: "phone",
        },
        {
            title: "Status",
            dataIndex: "status",
        },
    ];

    const exportColumn = {
        staff_member: "Staff Name",
        customer_count: "Customer Count",
    };
    return {
        AssignedColumn,
        ExtendedCustomerColumn,
        exportColumn,
    };
};

export default fields;
