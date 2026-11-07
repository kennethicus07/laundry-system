/**
 * Money arrives from the backend as 2-decimal strings ("1234.50").
 * The UI only displays them; all arithmetic happens in the Money class.
 */
export type Decimal = string;

export type InvoiceStatus = 'unpaid' | 'partially_paid' | 'paid' | 'void';
export type PaymentMethod = 'cash' | 'qr';
export type PaymentStatus =
    | 'pending_verification'
    | 'completed'
    | 'failed'
    | 'cancelled';
export type RefundStatus =
    | 'requested'
    | 'approved'
    | 'processing'
    | 'completed'
    | 'rejected'
    | 'failed';

export interface UserRef {
    id: number;
    name: string;
}

export interface InvoiceRef {
    id: number;
    invoice_number: string;
}

export interface LineItem {
    id: number;
    description: string;
    quantity: Decimal;
    unit_price: Decimal;
    line_total: Decimal;
}

export interface Refund {
    id: number;
    payment_id: number;
    amount: Decimal;
    status: RefundStatus;
    reason: string;
    provider_reference: string | null;
    requested_at: string | null;
    processed_at: string | null;
    requested_by?: UserRef | null;
    approved_by?: UserRef | null;
    payment?: {
        id: number;
        method: PaymentMethod;
        amount: Decimal;
        invoice: InvoiceRef | null;
    };
}

export interface Payment {
    id: number;
    invoice_id: number;
    amount: Decimal;
    method: PaymentMethod;
    status: PaymentStatus;
    reference_number: string | null;
    has_proof: boolean;
    notes: string | null;
    received_at: string | null;
    paid_at: string | null;
    failed_at: string | null;
    recorded_by?: UserRef | null;
    verified_by?: UserRef | null;
    invoice?: InvoiceRef;
    refunds?: Refund[];
}

export interface InvoiceRevision {
    id: number;
    revision_number: number;
    subtotal: Decimal;
    discount_amount: Decimal;
    tax_amount: Decimal;
    total_amount: Decimal;
    reason: string;
    revised_at: string | null;
    revised_by?: UserRef | null;
    items?: LineItem[];
}

export interface InvoiceSummary {
    id: number;
    invoice_number: string;
    order_id: number;
    total_amount: Decimal;
    status: InvoiceStatus;
    revision_number: number;
    issued_at: string | null;
}

export interface Invoice extends InvoiceSummary {
    subtotal: Decimal;
    discount_amount: Decimal;
    tax_amount: Decimal;
    voided_at: string | null;
    items: LineItem[];
    payments: Payment[];
    revisions: InvoiceRevision[];
}

export interface InvoiceBalance {
    total: Decimal;
    paid: Decimal;
    refunded: Decimal;
    net_paid: Decimal;
    pending: Decimal;
    balance: Decimal;
    payable: Decimal;
}

export interface PaginationLinks {
    first: string | null;
    last: string | null;
    prev: string | null;
    next: string | null;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
    per_page: number;
}

/** Shape of a paginated Laravel API Resource collection. */
export interface Paginated<T> {
    data: T[];
    links: PaginationLinks;
    meta: PaginationMeta;
}

/** An order the Create invoice picker can bill (from the Orders contract). */
export interface BillableOrderOption {
    id: number;
    order_number: string;
    customer_name: string | null;
    status: string;
    received_at: string | null;
    item_count: number;
    items_total: Decimal;
}
