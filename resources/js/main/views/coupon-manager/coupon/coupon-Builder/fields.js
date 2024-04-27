import { ref } from "vue";

const fields = () => {
    const Type = ref({
        type: "A",
    });

    const CardAState = ref({
        radius: 10,
        direction: "vertical",
        position: "start",
        offset: 50,
        width: 100,
        height: 100,
    });
    const CardBState = ref({
        corner: 20,
    });
    return {
        Type,
        CardAState,
        CardBState,
    };
};
export default fields;
