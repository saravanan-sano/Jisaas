<template>
    <main class="main">
        <div class="card-con">
            <div class="card" :style="style" ref="card"></div>
        </div>
        <aside class="side">
            <section class="item">
                <span class="name">radius</span>
                <input
                    type="range"
                    v-model="CardAState.radius"
                    :data-tips="CardAState.radius + 'px'"
                    :style="{ '--percent': CardAState.radius / max.radius }"
                    :max="max.radius"
                />
            </section>
            <section class="item">
                <span class="name">direction</span>
                <label class="radio" data-tips="horizontal"
                    ><input
                        type="radio"
                        name="dir"
                        value="horizontal"
                        v-model="CardAState.direction"
                /></label>
                <label class="radio" data-tips="vertical"
                    ><input
                        type="radio"
                        name="dir"
                        value="vertical"
                        v-model="CardAState.direction"
                /></label>
            </section>
            <section class="item" :direction="CardAState.direction">
                <span class="name">position</span>
                <label
                    class="radio"
                    :data-tips="
                        CardAState.direction == 'vertical' ? 'left' : 'top'
                    "
                    ><input
                        type="radio"
                        name="pos"
                        value="start"
                        v-model="CardAState.position"
                /></label>
                <label class="radio" data-tips="center"
                    ><input
                        type="radio"
                        name="pos"
                        value="center"
                        v-model="CardAState.position"
                /></label>
                <label
                    class="radio"
                    :data-tips="
                        CardAState.direction == 'vertical' ? 'right' : 'bottom'
                    "
                    ><input
                        type="radio"
                        name="pos"
                        value="end"
                        v-model="CardAState.position"
                /></label>
            </section>
            <section class="item" v-show="CardAState.position !== 'center'">
                <span class="name">offset</span>
                <input
                    type="range"
                    v-model="CardAState.offset"
                    :data-tips="CardAState.offset + 'px'"
                    :style="{ '--percent': CardAState.offset / max.offset }"
                    :max="max.offset"
                />
            </section>
            <!-- <Pre :style="style"/> -->
        </aside>
    </main>
</template>
<script setup>
import fields from "./fields";
import { ref, computed, onMounted } from "vue";

const { CardAState } = fields();

const emit = defineEmits(["changeStyle"]);

const style = computed(() => {
    const offset =
        CardAState.value.position === "center" ? "50%" : CardAState.value.offset + "px";
    const position = `${CardAState.value.direction === "horizontal" ? "" : "0 "}${
        CardAState.value.position === "end" ? "" : "-"
    }${CardAState.value.radius}px`;
    let newStyle = {
        "-webkit-mask-image": `radial-gradient(circle at ${
            CardAState.value.position === "end" ? "right " : ""
        }${
            CardAState.value.direction === "horizontal"
                ? CardAState.value.radius + "px"
                : offset
        } ${CardAState.value.position === "end" ? "bottom " : ""}${
            CardAState.value.direction === "horizontal"
                ? offset
                : CardAState.value.radius + "px"
        }, transparent ${CardAState.value.radius}px, red ${CardAState.value.radius}.5px)`,
        "-webkit-mask-position": position,
    };
    emit("changeStyle", CardAState.value);
    return newStyle;
});

const card = ref(null);

onMounted(() => {
    const { width, height } = card.value.getBoundingClientRect();
    CardAState.value.width = width;
    CardAState.value.height = height;
});

const max = computed(() => {
    return {
        radius: Math.min(CardAState.value.width, CardAState.value.height) / 2,
        offset:
            CardAState.value.direction === "horizontal"
                ? CardAState.value.height / 2
                : CardAState.value.width / 2,
    };
});
</script>
