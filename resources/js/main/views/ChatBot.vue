<template>
    <div class="chat_page_top">
        <div class="chat_page_top_avatar">
            <h4>JIERP BOT</h4>
        </div>
    </div>
    <div class="chatbot-page-content-container">
        <div class="chat_page_wrapper">
            <div class="chat_bot_loader" v-if="welcomeLoading">
                <div>
                    <img src="../../../../public/JI.png" alt="" />
                </div>
            </div>
            <div
                class="chat_page_content"
                v-if="!welcomeLoading"
                ref="chatContent"
            >
                <div v-for="(data, index) in chatHistory" :key="index">
                    <div class="chat_page_content_bot">
                        <a-avatar size="default" class="avatar_icon">
                            <template #icon><robot-outlined /></template>
                        </a-avatar>
                        <div class="chat_page_replay">
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
                        v-for="(answer, index) in data.answer"
                        class="chat_page_content_bot"
                        :key="index"

                    >
                        <a-avatar size="default" class="avatar_icon">
                            <template #icon><robot-outlined /></template>
                        </a-avatar>
                        <div class="chat_page_replay">
                            <div

                                class="chat_bot_replay"
                                style="background-color: #f9f9f9"
                                v-html="answer"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chat_page_footer">
                <div v-if="menuOpen" class="chat_page_bottom_menu">
                <div class="chat_page_menu_questions">
                    <a-button>Example Questions ?</a-button>
                    <a-button>Example Questions 2 ?</a-button>
                    <a-button>Example Questions 2 ?</a-button>
                    <a-button>Example Questions 2 ?</a-button>
                    <a-button>Example Questions 2 ?</a-button>
                    <a-button>Example Questions 2 ?</a-button>
                </div>
            </div>
                <a-form
                    class="chat_page_input_box"
                    @submit.prevent="SelectedQuestion(questionInput)"
                >
                    <a-input
                        v-model:value="questionInput"
                        placeholder="input search text"
                        size="large"
                    >
                    </a-input>
                    <a-button
                        type="primary"
                        @click="SelectedQuestion(questionInput)"
                        ><send-outlined
                    /></a-button>
                    <div class="chat_page_menu_button">
                        <a-button
                            v-if="!menuOpen"
                            type="text"
                            @click="displayMenu"
                            ><menu-outlined class="rotate-animation"
                        /></a-button>
                        <a-button
                            v-else
                            type="text"
                            @click="displayMenu"
                            size="large"
                            ><close-outlined class="rotate-animation"
                        /></a-button>
                    </div>
                </a-form>
            </div>
        </div>
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
import common from "../../common/composable/common";

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
                    return { position: "fixed", left: "100px", bottom: "30px" };
                } else {
                    return {
                        position: "fixed",
                        right: "100px",
                        bottom: "30px",
                    };
                }
            } else {
                return {};
            }
        });
        if (localchatHistory) {
            welcomeLoading.value = false;
            chatHistory.value = [];
            localchatHistory.forEach((data) => {
                chatHistory.value.push(data);
            });
        } else {
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
            }, 4000);
        }
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
                }
            }, speed);
        };

        // For making the content to stay in bottom
        const scrollToBottom = () => {
            if (chatContent.value) {
                chatContent.value.scrollTop =
                    chatContent.value.scrollHeight +
                    200 -
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
            // toggleOpen,
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
.chat_page_top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 20px;
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--primary-color);
}
.chat_page_top_avatar > h4 {
    font-weight: bold;
    color: #fff;
    margin: 0;
    padding:0px 50px;
}

.chat_page_content {
    padding: 20px 0;
    height: 75vh;
    overflow-y: scroll;
}
.chat_page_content_bot {
    margin-right: 20%;
    display: grid;
    grid-template-columns: 5% auto;
    align-items: end;
    margin-bottom: 15px;
    gap: 15px;
}
.chat_page_footer {
    display: flex;
    position: fixed;
    bottom: 20px;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    left: 0;
}

.chat_page_input_box {
    width: 80%;
    display: flex;
    align-items: center;
    position: relative;
}

.chat_page_input_box > input {
    padding: 15px;
    border-radius: 68px;
    border: none;
    padding-left: 65px;
    padding-right: 65px;
}

.chat_page_input_box > button {
    position: absolute;
    right: 7px;
    background: transparent;
    border: none;
    color: gray;
    box-shadow: none;
    font-size: 18px;
}

.chat_page_input_box > button:hover {
    background: transparent;
    color: #444;
}

.chat_page_footer > button {
    font-size: 18px;
    background: #fff;
}
.chat_page_menu_button > button {
    position: absolute;
    top: 10px;
    left: 5px;
    font-size: 18px;
}
.chat_page_menu_button > button:hover {
    border: none;
    background: transparent;
    color: #383838;
}
.chat_page_replay > button {
    border-radius: 50px;
}
.chat_page_replay {
    padding: 10px;
    display: flex;
    flex-direction: column;
    border-radius: 6px;
    gap: 13px;
    justify-content: flex-start;
    align-items: baseline;
}
.chat_page_menu_questions {
    position: absolute;
    bottom: 0px;
    margin: 10px;
    background: #f2f2f2;
    border-radius: 15px;
    box-shadow: 2px 2px 8px 0px #7a7a7a;
    padding: 20px;
    height: 200px;
    display: flex;
    width: 80%;
    overflow-y: scroll;
    scroll-behavior: smooth;
    left: 25px;
    flex-wrap: wrap;
}

.chat_page_menu_questions > button {
    border-radius: 30px;
    background: var(--primary-color);
    color: #fff;
    border: none;
    margin-bottom: 5px;
    margin-left: 5px;
    margin-right: 5px;
}

.chat_page_bottom_menu {
    transition-timing-function: ease-in-out;
    animation-timing-function: ease-in-out;
    animation: chat_page_menu_slide-top 0.5s
        cubic-bezier(0.25, 0.46, 0.45, 0.94) both;
}

@-webkit-keyframes chat_page_menu_slide-top {
    0% {
        -webkit-transform: translateY(220px);
        transform: translateY(220px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(210px);
        transform: translateY(210px);
        opacity: 1;
    }
}
@keyframes chat_page_menu_slide-top {
    0% {
        -webkit-transform: translateY(220px);
        transform: translateY(220px);
        opacity: 0.2;
    }
    100% {
        -webkit-transform: translateY(210px);
        transform: translateY(210px);
        opacity: 1;
    }
}
.chat_page_content,
.chat_page_menu_questions {
    scrollbar-width: none;
    scroll-behavior: smooth;
}
.chat_page_content::-webkit-scrollbar,
.chat_page_menu_questions::-webkit-scrollbar {
    display: none;
}
.chat_page_content::-webkit-scrollbar-track,
.chat_page_menu_questions::-webkit-scrollbar-track {
    display: none;
}
.chat_page_content::-webkit-scrollbar-thumb,
.chat_page_menu_questions::-webkit-scrollbar-thumb {
    display: none;
}

.chat_page_bottom_menu {
    position: absolute;
    width: 100%;
    bottom: 220px;
left: 101px;
}

.chatbot-page-content-container {
    position: relative;
    padding: 0 120px;
}
</style>
