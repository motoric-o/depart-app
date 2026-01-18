import datatable from './datatable';

export default function transactionsManager(config) {
    return {
        ...datatable(config),
        showProofModal: false,
        selectedProofs: [],
        selectedTransactionId: null,

        formatDate(dateString) {
            const options = { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: false };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        },

        formatPrice(price) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(price);
        },

        viewProofs(transaction) {
            this.selectedProofs = transaction.payment_issue_proofs || [];
            this.selectedTransactionId = transaction.id;
            this.showProofModal = true;
        }
    };
}
