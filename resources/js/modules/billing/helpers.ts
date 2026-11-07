import type {
    Decimal,
    InvoiceStatus,
    PaymentMethod,
    PaymentStatus,
    RefundStatus,
} from './types';

const peso = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
});
const dateTime = new Intl.DateTimeFormat('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

/** Display only. Never compute totals or balances in the UI. */
export function formatMoney(value: Decimal | null | undefined): string {
    if (value === null || value === undefined || value === '') return '—';
    return peso.format(Number(value));
}

export function formatQuantity(value: Decimal): string {
    return Number(value).toLocaleString('en-PH', { maximumFractionDigits: 2 });
}

export function formatDateTime(value: string | null | undefined): string {
    return value ? dateTime.format(new Date(value)) : '—';
}

/** String checks on backend decimals, so no float comparison is needed. */
export function isZeroAmount(value: Decimal): boolean {
    return /^-?0+(\.0+)?$/.test(value);
}

export function isNegativeAmount(value: Decimal): boolean {
    return value.startsWith('-') && !isZeroAmount(value);
}

export const invoiceStatusLabels: Record<InvoiceStatus, string> = {
    unpaid: 'Unpaid',
    partially_paid: 'Partially paid',
    paid: 'Paid',
    void: 'Void',
};

export const paymentStatusLabels: Record<PaymentStatus, string> = {
    pending_verification: 'Pending verification',
    completed: 'Completed',
    failed: 'Failed',
    cancelled: 'Cancelled',
};

export const paymentMethodLabels: Record<PaymentMethod, string> = {
    cash: 'Cash',
    qr: 'QR',
};

/** Order statuses that can appear in the invoice picker (owned by Orders). */
export const orderStatusLabels: Record<string, string> = {
    received: 'Received',
    processing: 'Processing',
    ready_for_delivery: 'Ready for delivery',
    completed: 'Completed',
};

export const refundStatusLabels: Record<RefundStatus, string> = {
    requested: 'Requested',
    approved: 'Approved',
    processing: 'Processing',
    completed: 'Completed',
    rejected: 'Rejected',
    failed: 'Failed',
};

/**
 * UUID v4 for idempotency keys. crypto.randomUUID() only exists on
 * HTTPS/localhost, so fall back to getRandomValues() (e.g. a LAN IP over http).
 */
export function newIdempotencyKey(): string {
    if (typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    const bytes = crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 0x0f) | 0x40;
    bytes[8] = (bytes[8] & 0x3f) | 0x80;
    const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0')).join(
        '',
    );

    return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
}
