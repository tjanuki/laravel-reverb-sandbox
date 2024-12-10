<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import type { User } from '@/types'
import {route} from "ziggy-js";

const props = defineProps<{
    availableUsers: User[]
}>()

const form = ref({
    name: '',
    user_ids: [] as number[]
})


const selectedUsers = ref<Set<number>>(new Set())

const toggleUser = (userId: number) => {
    if (selectedUsers.value.has(userId)) {
        selectedUsers.value.delete(userId)
    } else {
        selectedUsers.value.add(userId)
    }
    form.value.user_ids = Array.from(selectedUsers.value)
}

const createRoom = () => {
    router.post(route('rooms.store'), form.value)
}
</script>

<template>
    <AppLayout title="Create Room">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800">Create New Room</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Room Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="mt-1 w-full rounded-md border-gray-300"
                                placeholder="Enter room name"
                            />
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Members
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div
                                    v-for="user in availableUsers"
                                    :key="user.id"
                                    @click="toggleUser(user.id)"
                                    class="p-3 border rounded-lg cursor-pointer"
                                    :class="{
                                        'bg-blue-50 border-blue-500': selectedUsers.has(user.id),
                                        'hover:bg-gray-50': !selectedUsers.has(user.id)
                                    }"
                                >
                                    <div class="flex items-center space-x-3">
                                        <img
                                            :src="user.profile_photo_url"
                                            :alt="user.name"
                                            class="h-8 w-8 rounded-full"
                                        />
                                        <div>
                                            <div class="font-medium">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <button
                                @click="createRoom"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                                :disabled="!form.name || form.user_ids.length === 0"
                            >
                                Create Room
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
