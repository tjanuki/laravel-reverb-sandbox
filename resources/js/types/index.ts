export interface User {
    id: number;
    name: string;
    email: string;
    profile_photo_url?: string;
}

export interface Message {
    id: number;
    message: string;
    user: User;
    room_id: number;
    created_at: string;
}

export interface Room {
    id: number;
    name: string;
    users: User[];
    created_at: string;
    updated_at: string;
}
