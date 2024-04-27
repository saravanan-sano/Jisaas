<template>
    <main class="main">
        <div class="card-con">
            <div class="card" :style="style" ref="card"></div>
        </div>
        <aside class="side">
            <section class="item">
                <span class="name">corner</span>
                <input
                    type="range"
                    v-model="CardBState.corner"
                    :data-tips="CardBState.corner + 'px'"
                    :style="{ '--percent': CardBState.corner / CardBState.max }"
                    :max="CardBState.max"
                />
            </section>
        </aside>
    </main>
</template>
<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import fields from "./fields";

const { CardBState } = fields();
const emit = defineEmits(["changeStyle"]);
const style = computed(() => {
    let newStyle = {
        "-webkit-mask-image": `radial-gradient(circle at ${CardBState.value.corner}px ${CardBState.value.corner}px, transparent ${CardBState.value.corner}px, red ${CardBState.value.corner}.5px)`,
        "-webkit-mask-position": `-${CardBState.value.corner}px -${CardBState.value.corner}px`,
    };
    emit("changeStyle", CardBState.value);
    return newStyle;
});

const card = ref(null);

onMounted(() => {
    const { width, height } = card.value.getBoundingClientRect();
    CardBState.value.max = Math.min(width, height) / 2;
});
</script>
<style scoped></style>
