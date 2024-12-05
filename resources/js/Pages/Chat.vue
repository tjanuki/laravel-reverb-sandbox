<script setup>
import {onMounted, ref} from 'vue'
import {router, usePage} from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const messages = ref([])
const newMessage = ref('')
const users = ref([])

const sendMessage = () => {
    if (!newMessage.value.trim()) return

    router.post('/messages', {
        message: newMessage.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Add the message locally immediately
            messages.value.unshift({
                message: newMessage.value,
                user: usePage().props.auth.user,
                created_at: new Date().toISOString()
            })
            newMessage.value = ''
        }
    })
}

onMounted(() => {
    // Get initial messages
    router.get('/messages', {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            messages.value = page.props.messages
            users.value = page.props.users
        }
    })

    // Listen for new messages
    window.Echo.private('chat')
        .listen('MessageSent', (e) => {
            // Add new message to the beginning of the array
            console.log('Echo event received:', {
                eventName: 'MessageSent',
                data: e
            });
            messages.value.unshift({
                message: e.message,
                user: e.user,
                created_at: new Date().toISOString()
            })
        }).subscribed(() => {
        console.log('Successfully subscribed to chat channel');
    })
        .error((error) => {
            console.error('Echo error:', error);
        });

    window.Echo.connector.pusher.connection.bind('connected', () => {
        console.log('Connected to Reverb server');
    });

    window.Echo.connector.pusher.connection.bind('disconnected', () => {
        console.error('Disconnected from Reverb server');
    });
})

const formatDate = (date) => {
    return new Date(date).toLocaleString()
}
</script>

<template>
    <AppLayout title="Chat">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Chat Room
            </h2>
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
                            ></textarea>
                            <button
                                @click="sendMessage"
                                class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors"
                                :disabled="!newMessage.trim()"
                            >
                                Send Message
                            </button>
                        </div>

                        <!-- Messages -->
                        <div class="h-96 overflow-y-auto border rounded-lg p-4">
                            <div v-for="message in messages"
                                 :key="message.id"
                                 class="mb-4 p-3 rounded-lg"
                                 :class="{'bg-gray-50': message.user.id === $page.props.auth.user.id}">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-blue-600">{{ message.user.name }}</span>
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
