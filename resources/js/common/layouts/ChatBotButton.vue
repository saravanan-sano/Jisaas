<template>
    <div v-if="chatOpen">
        <div class="chat_bot_wrapper">
            <div class="chat_bot">
                <div class="chat_bot_top">
                    <div class="chat_bot_top_avatar">
                        <a-avatar size="large" class="avatar_icon">
                            <template #icon
                                ><img src="../../../../public/JI.png"
                            /></template>
                        </a-avatar>
                        <h4>JI BOT</h4>
                    </div>
                    <div class="chat_bot_top_button">
                        <a-button
                            type="link"
                            @click="
                                () => {
                                    $router.push({
                                        name: 'admin.chatbot.index',
                                    });
                                }
                            "
                            ><arrows-alt-outlined
                        /></a-button>
                        <a-button type="link" @click="toggleOpen"
                            ><close-outlined
                        /></a-button>
                    </div>
                </div>
                <div class="chat_bot_loader" v-if="welcomeLoading">
                    <div>
                        <img src="../../../../public/JI.png" alt="" />
                    </div>
                </div>
                <div
                    class="chat_bot_content"
                    v-if="!welcomeLoading"
                    ref="chatContent"
                >
                    <div v-for="(data, index) in chatHistory" :key="index">
                        <div class="chat_bot_content_bot">
                            <a-avatar size="default" class="avatar_icon">
                                <template #icon
                                    ><img src="../../../../public/JI.png"
                                /></template>
                            </a-avatar>
                            <div class="chat_bot_question">
                                <a-button
                                    v-for="(question, index) in data.question"
                                    :key="index"
                                    type="primary"
                                    @click="SelectedQuestion(question)"
                                    >{{ question }}</a-button
                                >
                            </div>
                        </div>
                        <div
                            class="chat_bot_content_user"
                            v-for="(selected, index) in data.selectedquestion"
                            :key="index"
                        >
                            <p>
                                {{ selected }}
                            </p>
                        </div>
                        <div
                            class="chat_bot_content_bot"
                            v-for="(answer, index) in data.answer"
                            :key="index"
                            :id="`chat_${index}`"
                        >
                            <a-avatar size="default" class="avatar_icon">
                                <template #icon
                                    ><img src="../../../../public/JI.png"
                                /></template>
                            </a-avatar>
                            <div class="chat_bot_replay" v-html="answer"></div>
                        </div>
                    </div>
                </div>
                <div v-if="menuOpen" class="chat_bot_bottom_menu">
                    <div class="chat_bot_menu_questions">
                        <a-button type="primary">Option 1</a-button>
                        <a-button type="primary">Option 2</a-button>
                        <a-button type="primary">Option 3</a-button>
                    </div>
                </div>
                <div class="chat_bot_bottom">
                    <a-button v-if="!menuOpen" type="text" @click="displayMenu"
                        ><menu-outlined class="rotate-animation"
                    /></a-button>
                    <a-button v-else type="text" @click="displayMenu"
                        ><close-outlined class="rotate-animation"
                    /></a-button>
                    <a-form
                        class="chat_bot_input"
                        @submit.prevent="SelectedQuestion(questionInput)"
                    >
                        <a-input
                            v-model:value="questionInput"
                            placeholder="input search text" />
                        <a-button
                            type="primary"
                            @click="SelectedQuestion(questionInput)"
                            ><send-outlined /></a-button
                    ></a-form>
                </div>
            </div>
        </div>
    </div>
    <div :style="affixStyle">
        <a-button
            v-if="position == 'bottom'"
            type="primary"
            shape="circle"
            :style="{ width: '50px', height: '50px' }"
            @click="toggleOpen"
        >
            <template #icon><robot-outlined /></template>
        </a-button>
    </div>
</template>

<script>
import { computed, ref, onUpdated, onMounted } from "vue";
import {
    RobotOutlined,
    CloseOutlined,
    SendOutlined,
    UserOutlined,
    ArrowsAltOutlined,
    MenuOutlined,
} from "@ant-design/icons-vue";
import common from "../composable/common";

export default {
    props: {
        position: {
            default: "bottom",
        },
    },
    components: {
        RobotOutlined,
        CloseOutlined,
        SendOutlined,
        UserOutlined,
        ArrowsAltOutlined,
        MenuOutlined,
    },
    setup(props, { emit }) {
        const { permsArray, appSetting, addMenus } = common();
        const menuSelected = () => {};
        const chatOpen = ref(false);
        const menuOpen = ref(false);
        const welcomeLoading = ref(true);
        const chatContent = ref(null);
        const questionInput = ref("");
        // Chat bot variables
        let chatHistory = ref([]);
        let localchatHistory = JSON.parse(
            sessionStorage.getItem("chat_history")
        );
        const questions = [
            "Top Selling 10 products?",
            "Show me a list of products that are in short supply.",
            "How many sales done today?",
            "Sales comparison yesterday & today?",
            "How is my online sales today?",
        ];
        // Position for the chatbot button
        const affixStyle = computed(() => {
            if (props.position == "bottom") {
                if (appSetting.value.rtl) {
                    return { position: "fixed", left: "150px", bottom: "30px" };
                } else {
                    return {
                        position: "fixed",
                        right: "150px",
                        bottom: "30px",
                    };
                }
            } else {
                return {};
            }
        });
        // Toggleopens the chat
        const toggleOpen = () => {
            if (localchatHistory == null) {
                fetchData();
            } else {
                welcomeLoading.value = false;
                chatHistory.value = [];
                localchatHistory.forEach((data) => {
                    chatHistory.value.push(data);
                });
            }
            chatOpen.value = !chatOpen.value;
        };
        // When opening the chatbot the initial question will be loaded
        const fetchData = () => {
            const newChat = {
                question: [],
                selectedquestion: [],
                answer: [],
            };
            chatHistory.value = [];
            questions.forEach((question) => {
                newChat.question.push(question);
            });
            chatHistory.value.push(newChat);
            setTimeout(() => {
                welcomeLoading.value = false;
            }, 3000);
        };
        // Toggle opens the menu in vhat
        const displayMenu = () => {
            menuOpen.value = !menuOpen.value;
        };
        // Post th equestion and gets the response and pushed to ChatHistory
        const SelectedQuestion = (question) => {
            questionInput.value = "";
            let chat_length = chatHistory.value.length - 1;
            chatHistory.value[chat_length].selectedquestion.push(question);

            axiosAdmin
                .post("chatbot", { question: question })
                .then((response) => {
                    const answer = response.message.answer;
                    typeWriterEffect(answer, () => {
                        const newChat = {
                            question: [],
                            selectedquestion: [],
                            answer: [],
                        };
                        if (
                            response.message.recommendedQuestions === undefined
                        ) {
                            questions.forEach((question) => {
                                newChat.question.push(question);
                            });
                        } else {
                            response.message.recommendedQuestions.forEach(
                                (question) => {
                                    newChat.question.push(question);
                                }
                            );
                        }
                        chatHistory.value.push(newChat);
                        sessionStorage.setItem(
                            "chat_history",
                            JSON.stringify(chatHistory.value)
                        );
                    });
                })
                .catch((error) => {
                    console.log(error);
                });
        };

        // Typewriter effect function
        const typeWriterEffect = (text, callback) => {
            let i = 0;
            const speed = 20;
            const typingInterval = setInterval(() => {
                if (i < text.length) {
                    const currentCharacter = text[i];
                    if (
                        currentCharacter === "<" ||
                        currentCharacter === ">" ||
                        currentCharacter === "/"
                    ) {
                        i++;
                    } else {
                        const currentAnswer = text.substring(0, i + 1);
                        chatHistory.value[chatHistory.value.length - 1].answer =
                            [currentAnswer];
                        i++;
                    }
                } else {
                    clearInterval(typingInterval);
                    callback();
                    let answer =
                        document.getElementsByClassName("chat_bot_replay");
                    let length =
                        document.getElementsByClassName(
                            "chat_bot_replay"
                        ).length;
                    setTimeout(() => {
                        var el =
                            document.getElementsByClassName(
                                "chat_bot_content"
                            )[0]; // Or whatever method to get the element
                        el.scrollTop = answer[length - 1].offsetTop - 100;
                    }, 1000);
                }
            }, speed);
        };

        // For making the content to stay in bottom
        const scrollToBottom = () => {
            if (chatContent.value) {
                chatContent.value.scrollTop =
                    chatContent.value.scrollHeight -
                    chatContent.value.clientHeight;
            }
        };

        onUpdated(() => {
            scrollToBottom();
        });
        // Assign chatContent ref to the corresponding element in the DOM
        onMounted(() => {
            chatContent.value = document.getElementById("chat-content");
        });

        return {
            permsArray,
            appSetting,
            addMenus,
            menuSelected,
            affixStyle,
            chatOpen,
            toggleOpen,
            displayMenu,
            SelectedQuestion,
            menuOpen,
            chatHistory,
            localchatHistory,
            welcomeLoading,
            questions,
            questionInput,
            innerWidth: window.innerWidth,
            typeWriterEffect,
            chatContent,
            scrollToBottom,
        };
    },
};
</script>

<style>
.chat_bot_wrapper {
    bottom: 10%;
    right: 17px;
    position: fixed;
    transform: translate(0%, 0%);
    background: #fff;
    border-radius: 4px;
    box-shadow: 1px 1px 4px 0px #888888;
    width: 450px;
    height: 540px;
    z-index: 10;
    border: none;
    transition-timing-function: ease-in-out;
    animation-timing-function: ease-in-out;
    -webkit-animation: slide-top 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
    animation: slide-top 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

@-webkit-keyframes slide-top {
    0% {
        -webkit-transform: translateY(200px);
        transform: translateY(200px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(0px);
        transform: translateY(0px);
        opacity: 1;
    }
}
@keyframes slide-top {
    0% {
        -webkit-transform: translateY(200px);
        transform: translateY(200px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(0px);
        transform: translateY(0px);
        opacity: 1;
    }
}
.chat_bot_wrapper p {
    margin: 0;
}
.chat_bot_wrapper h4 {
    margin: 0;
}
.chat_bot_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--primary-color);
    border: 1px solid var(--primary-color);
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
}

.chat_bot_top_avatar > h4 {
    font-weight: bold;
    color: #fff;
}

.chat_bot_top_button > button {
    color: #fff;
    opacity: 0.5;
    font-size: 16px;
    font-weight: 700;
}
.chat_bot_top_button > button:hover {
    color: #fff;
    opacity: 1;
}

.chat_bot_bottom {
    padding: 10px;
    background: #fff;
    position: fixed;
    display: flex;
    gap: 10px;
    align-items: center;
    position: fixed;
    bottom: 6px;
    width: 95%;
    border-radius: 10px;
    margin: 0 10px;
    box-shadow: 1px 1px 8px 2px #b7b7b7;
}

.chat_bot_bottom button {
    background: var(--primary-color);
    color: #fff;
}
.chat_bot_input {
    display: flex;
    width: 100%;
    align-items: center;
}
.chat_bot_bottom button {
    background: transparent;
    border: none;
    box-shadow: none;
    color: #606060;
}
.chat_bot_bottom button:hover,
.chat_bot_bottom button:focus {
    background: transparent;
    color: #000;
    border: none;
    box-shadow: none;
}

.chat_bot_bottom input {
    border: none;
}
.chat_bot_bottom .ant-input-search .ant-input:focus {
    border: 0 !important;
    box-shadow: none;
    border-color: transparent !important;
    outline: none !important;
}

.avatar_icon {
    background: var(--primary-color);
    vertical-align: "middle";
}

.chat_bot_content {
    padding: 15px 20px;
    height: 412px;
    overflow-y: scroll;
}

.chat_bot_content_bot {
    margin-right: 20%;
    display: grid;
    grid-template-columns: 15% auto;
    align-items: end;
    margin-bottom: 15px;
}

.chat_bot_replay {
    background: #eeeeee;
    padding: 10px;
    display: flex;
    flex-direction: column;
    border-radius: 6px;
}

.chat_bot_replay th,
td {
    font-size: 12px;
    /* text-align: center; */
}
.chat_bot_replay th {
    padding: 0 8px;
}
.chat_bot_replay table {
    margin: 10px 0;
}
.chat_bot_replay th {
    background: var(--primary-color);
    color: #fff;
}

.chat_bot_question {
    background: #eeeeee;
    padding: 15px;
    display: flex;
    border-radius: 6px;
    flex-wrap: wrap;
    gap: 10px;
}
.chat_bot_question > button {
    border-radius: 5px;
    background: var(--primary-color);
    white-space: normal;
    word-wrap: break-word;
    height: auto;
    text-align: left;
}
.chat_bot_question > button:hover {
    background: #fff;
    color: var(--primary-color);
}

.chat_bot_content_user {
    display: flex;
    text-align: left;
    justify-content: flex-end;
}

.chat_bot_bottom_menu {
    transition-timing-function: ease-in-out;
    animation-timing-function: ease-in-out;
    -webkit-animation: menu_slide-top 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)
        both;
    animation: menu_slide-top 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

@-webkit-keyframes menu_slide-top {
    0% {
        -webkit-transform: translateY(100px);
        transform: translateY(100px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(73px);
        transform: translateY(73px);
        opacity: 1;
    }
}
@keyframes menu_slide-top {
    0% {
        -webkit-transform: translateY(100px);
        transform: translateY(100px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(73px);
        transform: translateY(73px);
        opacity: 1;
    }
}

.chat_bot_menu_questions {
    position: absolute;
    bottom: 40px;
    margin: 10px;
    background: #fff;
    border-radius: 5px;
    box-shadow: 2px 2px 8px 0px #7a7a7a;
    padding: 10px;
    height: 200px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    width: 95%;
    overflow-y: scroll;
    scroll-behavior: smooth;
}
.chat_bot_menu_questions > button {
    border-radius: 30px;
    background: var(--primary-color);
}
.chat_bot_menu_questions > button:hover {
    background: #fff;
    color: var(--primary-color);
}

.chat_bot_content_user > p {
    color: #fff;
    max-width: max-content;
    background: var(--primary-color);
    margin-bottom: 15px;
    padding: 12px 14px;
    border-radius: 5px;
    width: 70%;
}

/* ===== Scrollbar CSS ===== */
.chat_bot_content,
.chat_bot_menu_questions {
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) #ffffff;
    scroll-behavior: smooth;
}
.chat_bot_content::-webkit-scrollbar,
.chat_bot_menu_questions::-webkit-scrollbar {
    width: 1px;
}

.chat_bot_content::-webkit-scrollbar-track,
.chat_bot_menu_questions::-webkit-scrollbar-track {
    background: #ffffff;
}

.chat_bot_content::-webkit-scrollbar-thumb,
.chat_bot_menu_questions::-webkit-scrollbar-thumb {
    background-color: var(--primary-color);
    border-radius: 30px;
    border: 14px solid #ffffff;
}

/* Rotating Animation */
.rotate-animation {
    animation-name: rotate;
    animation-duration: 0.25s;
    animation-timing-function: ease-in-out;
    animation-iteration-count: 0.25s;
}

@keyframes rotate {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(180deg);
    }
}

.chat_bot_loader > div {
    display: flex;
    display: flex;
    justify-content: center;
    background: var(--primary-color);
    border-radius: 99px;
}

.chat_bot_loader > div > img {
    width: 178px;
}

.chat_bot_loader {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 35px;
    width: 100%;
    height: 412px;
}

.chat_bot_input > input:focus {
    outline: none;
    box-shadow: none;
}

.chat_bot_top_avatar {
    display: flex;
    align-items: center;
    gap: 10px;
}
</style>
