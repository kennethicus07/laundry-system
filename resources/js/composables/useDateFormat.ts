export function useDateFormat() {
    const formatDateTime = (date: string) =>
        new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }).format(new Date(date));

    return {
        formatDateTime,
    };
}
