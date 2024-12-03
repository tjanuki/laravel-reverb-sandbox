<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const messages = ref([])
const newMessage = ref('')
const users = ref([])

const sendMessage = () => {
    router.post('/messages', {
        message: newMessage.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newMessage.value = ''
        }
    })
}

onMounted(() => {
    // Listen for messages
    window.Echo.private('chat')
        .listen('MessageSent', (e) => {
            messages.value.push({
                message: e.message,
                user: e.user
            })
        })

    // Get initial messages
    router.get('/messages', {}, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            messages.value = page.props.messages
            users.value = page.props.users
        }
    })
})
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
                        <!-- Messages -->
                        <div class="mb-4 h-96 overflow-y-auto">
                            <div v-for="(message, index) in messages"
                                 :key="index"
                                 class="mb-2">
                                <div class="font-bold">{{ message.user.name }}</div>
                                <div>{{ message.message }}</div>
                            </div>
                        </div>

                        <!-- Message Input -->
                        <div class="mt-4">
                            <textarea
                                v-model="newMessage"
                                class="w-full rounded-md border-gray-300"
                                rows="2"
                                placeholder="Type your message..."
                            ></textarea>
                            <button
                                @click="sendMessage"
                                class="mt-2 px-4 py-2 bg-blue-500 text-white rounded-md"
                            >
                                Send Message
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
