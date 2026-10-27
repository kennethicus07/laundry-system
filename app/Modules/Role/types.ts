export type Role = {
    id: number;
    name: string;

    status: 0 | 1;
    status_label: string;

    access_group_id: number | null;

    role_group?: {
        id: number;
        name: string;
    };

    created_at: string;
    updated_at: string;
};
