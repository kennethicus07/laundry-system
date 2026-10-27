export type Role = {
    id: number;
    name: string;
    label: string;

    status: number;
    status_label: string;

    access_group_id: number | null;

    role_group?: {
        id: number;
        name: string;
    };

    created_at: string;
    updated_at: string;
};
