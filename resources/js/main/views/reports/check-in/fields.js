import { ref } from "vue";

const fields = () => {


    const exportColumn = {
        staff_member: "Staff Name",
        customer_count: "Customer Count",
    };
    return {

        exportColumn,
    };
};

export default fields;
