const fields = () => {
    const columns = [
        {
            title: "Name",
            dataIndex: "name",
        },
        {
            title: "Email",
            dataIndex: "email",
        },
        {
            title: "OS",
            dataIndex: "operating_system",
            sorter: (a, b) => {
                const osA = a.operating_system.toLowerCase();
                const osB = b.operating_system.toLowerCase();
                return osA.localeCompare(osB);
            },
        },
        {
            title: "browser",
            dataIndex: "browser",
        },
        {
            title: "Date",
            dataIndex: "date",
        },
        {
            title: "Action",
            dataIndex: "action",
        },
    ];
    return {
        columns,
    };
};

export default fields;
