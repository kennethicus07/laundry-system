export type Permission = {
    id: number;
    name: string;

    status: 0 | 1;
    status_label: string;

    access_group_id: number | null;
    features_id: number | null;

    accessGroup?: {
        id: number;
        name: string;
    };

    features?: {
        id: number;
        name: string;
    };

    created_at: string;
    updated_at: string;
};
