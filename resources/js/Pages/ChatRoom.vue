<script setup lang="ts">
import { onMounted, ref, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import TypingIndicator from './TypingIndicator.vue'
import type { Room, Message, User } from '@/types'
import { route } from "ziggy-js"

interface PaginatedMessages {
    data: Message[];
    current_page: number;
    total: number;
}

const messages = ref<Message[]>([])
const newMessage = ref('')
const users = ref<User[]>([])
const page = usePage()
const auth = page.props.auth as { user: User }
const room = ref<Room>(page.props.room as Room)
const typingUsers = ref<string[]>([])
const isTyping = ref(false)
let typingTimeout: NodeJS.Timeout | null = null

const formatDate = (date: string): string => {
    try {
        return new Date(date).toLocaleString()
    } catch (e) {
        return 'Invalid Date'
    }
}

const sendMessage = () => {
    if (!newMessage.value.trim()) return

    router.post(route('rooms.messages.store', { room: room.value.id }), {
        message: newMessage.value,
        room_id: room.value.id
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = ''
            isTyping.value = false
            if (typingTimeout) clearTimeout(typingTimeout)
        }
    })
}

const leaveRoom = () => {
    router.delete(route('rooms.destroy', { room: room.value.id }), {
        onSuccess: () => router.visit(route('rooms.index'))
    })
}

const startTyping = () => {
    if (!isTyping.value) {
        isTyping.value = true
        window.Echo.private(`room.${room.value.id}`)
            .whisper('typing', {
                user: auth.user.name
            })
    }

    // Reset the timeout
    if (typingTimeout) clearTimeout(typingTimeout)
    typingTimeout = setTimeout(() => {
        isTyping.value = false
    }, 2000)
}

// Cleanup typing timeout on component unmount
onUnmounted(() => {
    if (typingTimeout) clearTimeout(typingTimeout)
})

onMounted(() => {
    // Initialize messages from paginated data
    const paginatedMessages = page.props.messages as PaginatedMessages
    messages.value = paginatedMessages.data
    users.value = page.props.availableUsers as User[]

    // Join presence channel for the room
    window.Echo.join(`room.${room.value.id}`)
        .here((users: User[]) => {
            console.log('Present users:', users)
        })
        .joining((user: User) => {
            console.log('User joined:', user)
        })
        .leaving((user: User) => {
            console.log('User left:', user)
        })
        .listen('MessageSent', (event: { message: Message }) => {
            messages.value.unshift(event.message)
        })

    // Listen for typing events
    window.Echo.private(`room.${room.value.id}`)
        .listenForWhisper('typing', (e: { user: string }) => {
            if (e.user !== auth.user.name && !typingUsers.value.includes(e.user)) {
                typingUsers.value.push(e.user)
                setTimeout(() => {
                    typingUsers.value = typingUsers.value.filter(user => user !== e.user)
                }, 2000)
            }
        })
})
</script>

<template>
    <AppLayout :title="room.name">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ room.name }}
                </h2>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-500">
                        Members: {{ room.users.map(u => u.name).join(', ') }}
                    </div>
                    <button
                        @click="leaveRoom"
                        class="px-3 py-1 text-sm text-red-600 hover:text-red-800 transition-colors"
                    >
                        Leave Room
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <!-- Message Input -->
                        <div class="mb-4">
                            <textarea
                                v-model="newMessage"
                                class="w-full rounded-md border-gray-300"
                                rows="2"
                                placeholder="Type your message..."
                                @keyup.enter.prevent="sendMessage"
                                @input="startTyping"
                            ></textarea>
                            <div class="flex justify-between items-center mt-2">
                                <div>
                                    <TypingIndicator :users="typingUsers" />
                                </div>
                                <button
                                    @click="sendMessage"
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
                                    :disabled="!newMessage.trim()"
                                >
                                    Send Message
                                </button>
                            </div>
                        </div>

                        <!-- Messages -->
                        <div class="h-96 overflow-y-auto border rounded-lg p-4">
                            <div v-if="messages.length === 0" class="text-center text-gray-500 py-4">
                                No messages yet. Start the conversation!
                            </div>
                            <div v-else v-for="message in messages"
                                 :key="message.id"
                                 class="mb-4 p-3 rounded-lg"
                                 :class="{'bg-gray-50': message.user_id === auth.user.id}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-blue-600">{{ message.user?.name }}</span>
                                    <span class="text-sm text-gray-500">{{ formatDate(message.created_at) }}</span>
                                </div>
                                <div class="text-gray-700">{{ message.message }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
