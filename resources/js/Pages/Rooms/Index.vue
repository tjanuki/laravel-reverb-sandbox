<script setup lang="ts">
import {computed, ref} from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import {Room} from "@/types";
import {route} from "ziggy-js";

const props = defineProps<{
    rooms: Room[]
}>()

const searchQuery = ref('')

const filteredRooms = computed(() => {
    return props.rooms.filter(room =>
        room.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
</script>

<template>
    <AppLayout title="Chat Rooms">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800">Chat Rooms</h2>
                <Link
                    :href="route('rooms.create')"
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600"
                >
                    Create Room
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search rooms..."
                        class="w-full px-4 py-2 rounded-md border-gray-300"
                    />
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6 grid gap-4">
                        <div
                            v-for="room in filteredRooms"
                            :key="room.id"
                            class="border rounded-lg p-4 hover:bg-gray-50 transition-colors"
                        >
                            <Link :href="route('rooms.show', room.id)">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            {{ room.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            {{ room.users.length }} members
                                        </p>
                                    </div>
                                    <span class="text-sm text-gray-400">
                                        Created {{ new Date(room.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                                <div class="mt-2 text-sm text-gray-600">
                                    Members: {{ room.users.map(u => u.name).join(', ') }}
                                </div>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
