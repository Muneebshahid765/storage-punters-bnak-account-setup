<?php
defined('BASEPATH') or exit('No direct script access allowed');

class InvoiceModel extends CI_Model
{
    protected $users_table = 'tbl_users';

    protected $invoices_table = 'tbl_invoices';

    protected $auction_storage_facility_relationship_table = 'tbl_auction_storage_facility_relationship';

    protected $storage_facilities_table = 'tbl_storage_facilities';

    protected $invoice_auction_relationship_table = 'tbl_invoice_auction_relationship';

    protected $invoice_payment_method_type_table = 'tbl_invoice_payment_method_types';

    protected $invoice_type_table = 'tbl_invoice_types';

    protected $payment_type_table = 'tbl_payment_types';

    protected $invoice_stripe_relationship_table = 'tbl_invoice_stripe_relationship';

    protected $invoice_stripe_logs_relationship_table = 'tbl_invoice_stripe_logs_relationship';
    public function countAllAuctionInvoicesByBidderUser($user_id, $user_role_id, $auction_ids)
    {
        // ✅ Get count of DISTINCT auctions where the user has placed a bid
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT iar.id) as count');
        $this->db->from($this->invoices_table . ' i');
        $this->db->from($this->invoice_auction_relationship_table . ' iar');

        if ($user_role_id == 2) {
            $this->db->where_in('iar.user_id', $user_id);
        } else {
            $this->db->where_in('iar.auction_id', $auction_ids);
        }


        return $this->db->get()->row()->count;
    }

    // ✅ Count filtered invoices based on a search keyword
    public function countFilteredAuctionInvoicesByBidderUser($search, $user_id, $user_role_id, $auction_ids)
    {
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT i.id) as count');
        $this->db->from($this->invoices_table . ' i');
        $this->db->join($this->invoice_auction_relationship_table . ' iar', 'i.id = iar.invoice_id', 'inner');
        if ($user_role_id == 2) {
            $this->db->where_in('iar.user_id', $user_id);
        } else {
            $this->db->where_in('iar.auction_id', $auction_ids);
        }

        // Apply search filters
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('i.invoice_number', $search);
            $this->db->group_end();
        }

        $query = $this->db->get();
        return ($query->num_rows() > 0) ? $query->row()->count : 0;
    }

    // ✅ Get filtered invoices with pagination and sorting
    public function getFilteredAuctionInvoicesByBidderUser($start, $length, $search, $orderColumn, $orderDir, $user_id, $user_role_id, $auction_ids)
    {
        $this->db->distinct();
        $this->db->select('
            iar.auction_id as auction_id,
            i.id as invoice_id,
            i.invoice_number,
            i.amount as invoice_amount,
            i.tax_amount as invoice_tax_amount,
            i.total_amount as invoice_total_amount,
            i.currency as invoice_currency,
            i.invoice_date as invoice_date,
            i.due_date as invoice_due_date,
            i.paid_date as invoice_paid_date,
            it.name as invoice_status,
            ips.name as payment_status,
            ipmt.name as payment_method,
            sft.name as facility_name,
            u.id, u.first_name as firstname,
            u.last_name as lastname
        ');
        $this->db->from($this->invoices_table . ' i');
        $this->db->join($this->invoice_auction_relationship_table . ' iar', 'i.id = iar.invoice_id', 'inner');
        $this->db->join($this->invoice_type_table . ' it', 'iar.invoice_type_id = it.id', 'left');
        $this->db->join($this->payment_type_table . ' ips', 'iar.invoice_payment_status_type_id = ips.id', 'left');
        $this->db->join($this->invoice_payment_method_type_table . ' ipmt', 'iar.invoice_payment_method_type_id = ipmt.id', 'left');
        $this->db->join($this->auction_storage_facility_relationship_table . ' asfrt', 'iar.auction_id = asfrt.auction_id', 'left');
        $this->db->join($this->storage_facilities_table . ' sft', 'asfrt.storage_facility_id = sft.id', 'left');
        $this->db->join($this->users_table . ' u', 'u.id = iar.user_id', 'left');
        if ($user_role_id == 2) {
            $this->db->where_in('iar.user_id', $user_id);
        } else {
            $this->db->where_in('iar.auction_id', $auction_ids);
        }

        // Apply search filters
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('i.invoice_number', $search);
            $this->db->group_end();
        }

        // Apply sorting
        if (!empty($orderColumn) && !empty($orderDir)) {
            $this->db->order_by($orderColumn, $orderDir);
        }

        // Apply pagination
        if ($length != -1) {
            $this->db->limit($length, $start);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function hasAuctionPaymentPaid($auctionId)
    {
        $this->db->from($this->invoice_auction_relationship_table);
        $this->db->where('auction_id', $auctionId);

        // Assuming 1 is the ID for 'paid' status
        $this->db->where('invoice_payment_status_type_id', 2);

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row_array(); // ✅ return payment row
        } else {
            return null; // ✅ not paid
        }
    }

    public function getAuctionDetailsByInvoiceId($invoiceId)
    {
        $this->db->where('invoice_id', $invoiceId);
        $query = $this->db->get($this->invoice_auction_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // invoice not found
        }
    }


    public function getInvoiceDetailsByAuctionId($auctionId)
    {
        $this->db->where('auction_id', $auctionId);
        $query = $this->db->get($this->invoice_auction_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }


    public function getInvoiceById($invoiceId)
    {
        $this->db->where('id', $invoiceId);
        $query = $this->db->get($this->invoices_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getInvoiceStripePaymentLogsByInvoiceId($invoiceId)
    {
        $this->db->where('invoice_id', $invoiceId);
        $query = $this->db->get($this->invoice_stripe_logs_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getByPaymentIntentId($paymentIntentId)
    {
        $this->db->where('stripe_invoice_id', $paymentIntentId);
        $query = $this->db->get($this->invoice_stripe_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getStripeInvoicePaymentInformationByInvoiceId($invoiceId)
    {
        $this->db->where('invoice_id', $invoiceId);
        $query = $this->db->get($this->invoice_stripe_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function updateStatusByPaymentIntentId($invoiceId, $invoiceData)
    {
        $this->db->where('invoice_id', $invoiceId); // Match the user ID
        $this->db->update($this->invoice_auction_relationship_table, $invoiceData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }


    // **📌 Insert User Invoice Stripe**
    public function insert_invoice_with_stripe($invoicePaymentByStripeData)
    {
        return $this->db->insert($this->invoice_stripe_relationship_table, $invoicePaymentByStripeData);
    }

    public function insert_invoice($invoiceData)
    {
        $this->db->insert($this->invoices_table, $invoiceData);
        return [
            'invoice_id' => $this->db->insert_id()
        ];
    }

    public function insert_invoice_auction_relationship($invoiceAuctionRelationshipData)
    {
        return $this->db->insert($this->invoice_auction_relationship_table, $invoiceAuctionRelationshipData);
    }
    /**
     * Get invoices that are past their due date and haven't had a late fee applied
     */
    public function getOverdueInvoices()
    {
        $this->db->select('i.*, iar.invoice_payment_status_type_id');
        $this->db->from($this->invoices_table . ' i');
        $this->db->join($this->invoice_auction_relationship_table . ' iar', 'iar.invoice_id = i.id', 'inner');
        $this->db->where('i.due_date <', date('Y-m-d'));
        $this->db->where('i.late_fee_applied', 0);
        $this->db->where_not_in('iar.invoice_payment_status_type_id', [2, 5]); // Not Paid or Refunded
        return $this->db->get()->result_array();
    }

    /**
     * Apply a late fee to an invoice
     */
    public function applyLateFee($invoiceId, $feeAmount)
    {
        $this->db->where('id', $invoiceId);
        $invoice = $this->db->get($this->invoices_table)->row_array();

        if (!$invoice) return false;

        $newAmount = $invoice['amount'] + $feeAmount;
        $newTotalAmount = $invoice['total_amount'] + $feeAmount;

        $data = [
            'amount' => $newAmount,
            'total_amount' => $newTotalAmount,
            'late_fee_applied' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $invoiceId);
        return $this->db->update($this->invoices_table, $data);
    }
}
