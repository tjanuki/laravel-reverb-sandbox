<script setup lang="ts">
interface Props {
    users: string[];
}

const props = defineProps<Props>();

const formatTypingText = (users: string[]): string => {
    if (users.length === 0) return '';
    if (users.length === 1) return `${users[0]} is typing...`;
    if (users.length === 2) return `${users[0]} and ${users[1]} are typing...`;
    return `${users[0]} and ${users.length - 1} others are typing...`;
}
</script>

<template>
    <transition name="fade">
        <div v-if="users.length > 0" class="typing-indicator">
            <div class="dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <span class="text-sm text-gray-500">{{ formatTypingText(users) }}</span>
        </div>
    </transition>
</template>

<style scoped lang="scss">
.typing-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    height: 24px;
}

.dots {
    display: flex;
    gap: 2px;

    span {
        width: 4px;
        height: 4px;
        background: #9ca3af;
        border-radius: 50%;
        animation: bounce 1.4s infinite ease-in-out;

        &:nth-child(1) { animation-delay: -0.32s; }
        &:nth-child(2) { animation-delay: -0.16s; }
    }
}

@keyframes bounce {
    0%, 80%, 100% { transform: scale(0); }
    40% { transform: scale(1); }
}

.fade-enter-active, .fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
