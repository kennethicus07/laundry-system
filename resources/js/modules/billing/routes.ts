/**
 * Single place for Billing admin URLs (mirrors the Billing block in routes/web.php).
 * If you use Ziggy or Wayfinder, swap these for route('admin.invoices.…') calls.
 */
const base = '/admin';

export const billingRoutes = {
    invoices: {
        index: () => `${base}/invoices`,
        store: () => `${base}/invoices`,
        billableOrders: (search = '') =>
            `${base}/invoices/billable-orders` +
            (search ? `?search=${encodeURIComponent(search)}` : ''),
        show: (invoiceId: number) => `${base}/invoices/${invoiceId}`,
        cashPayment: (invoiceId: number) =>
            `${base}/invoices/${invoiceId}/payments/cash`,
    },
    payments: {
        pendingQr: () => `${base}/payments/pending-qr`,
    },
    refunds: {
        index: () => `${base}/refunds`,
    },
};
