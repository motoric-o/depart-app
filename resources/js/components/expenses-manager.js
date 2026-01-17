import datatable from './datatable';
import Swal from 'sweetalert2';

export default function expensesManager(config) {
    return {
        ...datatable(config),
        current_user_id: config.current_user_id,

        issueModalOpen: false,
        activeIssue: null,
        activeExpense: null,

        openIssueModal(expense) {
            if (!expense.transaction || !expense.transaction.payment_issue_proofs || expense.transaction.payment_issue_proofs.length === 0) {
                // Fallback if casing is different or empty (should be handled by button visibility, but safety first)
                const proofs = expense.transaction?.paymentIssueProofs || [];
                if (proofs.length === 0) return;
                this.activeIssue = proofs[proofs.length - 1];
            } else {
                this.activeIssue = expense.transaction.payment_issue_proofs[expense.transaction.payment_issue_proofs.length - 1];
            }
            this.activeExpense = expense;
            this.issueModalOpen = true;
        },

        closeIssueModal() {
            this.issueModalOpen = false;
            this.activeIssue = null;
            this.activeExpense = null;
        },

        receiptModalOpen: false,

        openReceiptModal(expense) {
            this.activeExpense = expense;
            this.receiptModalOpen = true;
        },

        closeReceiptModal() {
            this.receiptModalOpen = false;
            this.activeExpense = null;
        },

        formatMoney(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        },

        formatDate(dateString) {
            const options = { day: '2-digit', month: 'short', year: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-GB', options);
        },

        isPdf(filePath) {
            return filePath && filePath.toLowerCase().endsWith('.pdf');
        },

        typeClass(type) {
            switch (type) {
                case 'reimbursement': return 'bg-yellow-100 text-yellow-800';
                case 'operational': return 'bg-blue-100 text-blue-800';
                case 'maintenance': return 'bg-red-100 text-red-800';
                case 'salary': return 'bg-green-100 text-green-800';
                default: return 'bg-gray-100 text-gray-800';
            }
        },

        statusClass(status) {
            // 'Paid', 'In Process', 'Pending Confirmation', 'Rejected', 'Canceled', 'Failed', 'Payment Issue', 'Pending'
            switch (status) {
                case 'Paid': return 'bg-green-100 text-green-800';
                case 'In Process': return 'bg-blue-100 text-blue-800';
                case 'Pending Confirmation': return 'bg-orange-100 text-orange-800';
                case 'Rejected':
                case 'Canceled':
                case 'Failed':
                case 'Payment Issue':
                    return 'bg-red-100 text-red-800';
                default: return 'bg-yellow-100 text-yellow-800';
            }
        },

        statusLabel(status) {
            const labels = {
                'Pending': 'Menunggu',
                'In Process': 'Diproses',
                'Pending Confirmation': 'Menunggu Konfirmasi',
                'Paid': 'Dibayar',
                'Payment Issue': 'Masalah Pembayaran',
                'Rejected': 'Ditolak',
                'Canceled': 'Dibatalkan',
                'Failed': 'Gagal'
            };
            return labels[status] || status;
        },

        get selectionCommonStatus() {
            if (this.selectedItems.length === 0) return null;
            const selectedExpenses = this.items.filter(item => this.selectedItems.includes(item.id));
            if (selectedExpenses.length === 0) return null;

            const firstStatus = selectedExpenses[0].status;
            const allSame = selectedExpenses.every(item => item.status === firstStatus);
            return allSame ? firstStatus : 'mixed';
        },


        bulkAction(action, status) {
            if (this.selectedItems.length === 0) return;

            // Workflow Validation
            const selectedExpenses = this.items.filter(item => this.selectedItems.includes(item.id));
            let invalidCount = 0;

            if (status === 'In Process') {
                // Can only approve (In Process) if currently Pending or Failed? Or maybe just not already Paid/In Process?
                // Let's being strict: Only 'Pending' items can be Approved.
                invalidCount = selectedExpenses.filter(e => e.status !== 'Pending').length;
                if (invalidCount > 0) {
                    Swal.fire({
                        title: 'Pilihan Tidak Valid',
                        text: `Anda hanya dapat menyetujui pengeluaran yang statusnya 'Menunggu'. ${invalidCount} item terpilih tidak valid.`,
                        icon: 'warning',
                        confirmButtonColor: '#2563EB'
                    });
                    return;
                }
            } else if (status === 'Pending Confirmation') {
                // Pay (Pending Confirmation) only if 'In Process' (Approved) OR resolving 'Payment Issue'
                invalidCount = selectedExpenses.filter(e => e.status !== 'In Process' && e.status !== 'Payment Issue').length;
                if (invalidCount > 0) {
                    Swal.fire({
                        title: 'Pilihan Tidak Valid',
                        text: `Anda hanya dapat membayar pengeluaran yang 'Diproses' (Disetujui) atau menyelesaikan 'Masalah Pembayaran'. ${invalidCount} item terpilih tidak valid.`,
                        icon: 'warning',
                        confirmButtonColor: '#2563EB'
                    });
                    return;
                }
            }
            // Add other status checks if needed (e.g. Reject allowed from Pending/In Process)

            let actionVerb = status === 'In Process' ? 'Setujui' : (status === 'Pending Confirmation' ? 'Bayar' : (status === 'Rejected' ? 'Tolak' : 'Perbarui'));

            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Anda akan me-${actionVerb.toLowerCase()} ${this.selectedItems.length} pengeluaran.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#4B5563',
                confirmButtonText: `Ya, ${actionVerb}!`,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    const promises = this.selectedItems.map(id => {
                        return fetch(`/api/admin/expenses/${id}/verify`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                            },
                            body: JSON.stringify({ status: status })
                        }).then(res => res.json());
                    });

                    Promise.all(promises).then(results => {
                        // Check failures? API returns filtered keys on success?
                        // Let's assume if promise resolves, it's basically handled.
                        // We could count failures if we want strict checking.
                        this.fetchData(this.pagination.current_page);
                        this.selectedItems = [];
                        Swal.fire({
                            title: 'Berhasil!',
                            text: 'Pengeluaran terpilih telah diperbarui.',
                            icon: 'success',
                            confirmButtonColor: '#2563EB'
                        });
                    }).catch(err => {
                        console.error(err);
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat pembaruan massal.',
                            icon: 'error',
                            confirmButtonColor: '#2563EB'
                        });
                    });
                }
            })
        },

        verifyExpense(id, status) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: `Perbarui status ke ${this.statusLabel(status)}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#4B5563',
                confirmButtonText: 'Ya, perbarui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/api/admin/expenses/${id}/verify`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    })
                        .then(res => {
                            if (!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(data => {
                            if (data.id) {
                                this.fetchData(this.pagination.current_page);
                                Swal.fire({
                                    title: 'Diperbarui!',
                                    text: 'Status pengeluaran berhasil diperbarui.',
                                    icon: 'success',
                                    confirmButtonColor: '#2563EB'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: 'Gagal memperbarui status.',
                                    icon: 'error',
                                    confirmButtonColor: '#2563EB'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonColor: '#2563EB'
                            });
                        });
                }
            });
        },

        confirmExpense(id) {
            Swal.fire({
                title: 'Konfirmasi Penerimaan?',
                text: "Apakah Anda sudah menerima dana untuk permintaan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981', // Green
                cancelButtonColor: '#4B5563',
                confirmButtonText: 'Ya, saya menerimanya!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/api/admin/expenses/${id}/confirm`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                        }
                    })
                        .then(res => {
                            if (!res.ok) return res.json().then(err => { throw new Error(err.message || 'Error confirming'); });
                            return res.json();
                        })
                        .then(data => {
                            this.fetchData(this.pagination.current_page);
                            Swal.fire({
                                title: 'Dikonfirmasi!',
                                text: 'Penerimaan berhasil dikonfirmasi.',
                                icon: 'success',
                                confirmButtonColor: '#10B981'
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Gagal!',
                                text: error.message || 'Terjadi kesalahan.',
                                icon: 'error',
                                confirmButtonColor: '#2563EB'
                            });
                        });
                }
            });
        }
    };
}
