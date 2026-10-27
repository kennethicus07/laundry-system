import { BadgeVariants } from '@/components/ui/badge';

type StatusPayload = {
    value: number;
    label: string;
    key: string;
};

type StatusValue = {
    value: number;
    label: string;
};

const map: Record<number, BadgeVariants['variant']> = {
    1: 'success',
    2: 'secondary',
    0: 'destructive',
};

export function useStatus() {
    function get(status: StatusPayload) {
        return {
            label: status.label,
            variant: map[status.value] ?? 'secondary',
        };
    }

    function fromStatus(status: StatusValue) {
        return {
            label: status.label,
            variant: map[status.value],
        };
    }

    return { get, fromStatus };
}
