<?php
defined('BASEPATH') or exit('No direct script access allowed');

class LogsModel extends CI_Model
{
    protected $users_table = 'tbl_users';

    protected $auction_confirmation_type_table = 'tbl_auction_confirmation_types';
    protected $invoice_stripe_logs_relationship_table = 'tbl_invoice_stripe_logs_relationship';

    protected $auctions_release_payment_schedule_table = 'tbl_auctions_release_payment_schedule';
    protected $auction_confirmations_table = 'tbl_auction_confirmations';

    public function countAllInvoiceHistoryByBidderUser($invoice_id)
    {
        // ✅ Get count of DISTINCT auctions where the user has placed a bid
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT islrh.id) as count');
        $this->db->from($this->invoice_stripe_logs_relationship_table . ' islrh');
        $this->db->where('islrh.invoice_id', $invoice_id);

        return $this->db->get()->row()->count;
    }


    public function countAllAuctionWinnerConfirmLogsById($winner_id)
    {
        // ✅ Get count of DISTINCT auctions where the user has placed a bid
        $this->db->distinct();
        $this->db->select('COUNT(DISTINCT ac.id) as count');
        $this->db->from($this->auction_confirmations_table . ' ac');
        $this->db->where('ac.winner_id', $winner_id);

        return $this->db->get()->row()->count;
    }

    public function countFilteredInvoiceHistoryByBidderUser($search, $invoice_id)
    {
        $this->db->select('COUNT(DISTINCT islrh.invoice_id) as count'); // ✅ Ensure unique auction count
        $this->db->from($this->invoice_stripe_logs_relationship_table . ' islrh');
        $this->db->where('islrh.invoice_id', $invoice_id);

        if (!empty($search)) {
            $this->db->like('islrh.id', $search);
        }

        return $this->db->get()->row()->count;
    }

    public function countFilteredAuctionWinnerConfirmLogsById($search, $winner_id)
    {
        $this->db->select('COUNT(DISTINCT ac.id) as count');
        $this->db->from($this->auction_confirmations_table . ' ac');
        $this->db->where('ac.winner_id', $winner_id);

        if (!empty($search)) {
            $this->db->like('ac.id', $search);
        }

        return $this->db->get()->row()->count;
    }


    public function getFilteredInvoiceHistoryByBidderUser($start, $length, $search, $orderColumn, $orderDir, $invoice_id)
    {
        $this->db->distinct();
        $this->db->select('
            islrh.id as invoice_log_id,
            (islrh.amount_received / 100) as invoice_amount_received,
            islrh.currency as invoice_currency,
            islrh.payment_status as invoice_payment_status,
            islrh.event_type as invoice_event_type,
            islrh.payment_at as invoice_payment_at
        ');
        $this->db->from($this->invoice_stripe_logs_relationship_table . ' islrh');
        $this->db->where('islrh.invoice_id', $invoice_id);

        // Apply search filters
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('islrh.id', $search);
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


    public function getFilteredAuctionWinnerConfirmLogsById($start, $length, $search, $orderColumn, $orderDir, $winner_id)
    {
        $this->db->distinct();
        $this->db->select("
    ac.id AS winner_id,
    CONCAT(u.first_name, ' ', u.last_name) AS confirm_person_name,
    act.name AS confirm_status,
    ac.confirmed_at AS confirmed_at
");
        $this->db->from($this->auction_confirmations_table . ' ac');
        $this->db->join($this->users_table . ' u', 'u.id = ac.user_id', 'left'); // ✅ Fixed join condition
        $this->db->join($this->auction_confirmation_type_table . ' act', 'act.id = ac.confirmation_type_id', 'left'); // ✅ Fixed join condition
        $this->db->where('ac.winner_id', $winner_id);

        // Apply search filters
        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('ac.id', $search);
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


    public function insert_stripe_invoice_payment_logs_data($logsData)
    {
        return $this->db->insert($this->invoice_stripe_logs_relationship_table, $logsData);
    }

    public function insert_stripe_payment_release_data($paymentReleaseData)
    {
        return $this->db->insert($this->auctions_release_payment_schedule_table, $paymentReleaseData);
    }


    public function updateStripePaymentReleaseStatus($invoiceId, $paymentReleaseData)
    {
        $this->db->where('invoice_id', $invoiceId); // Match the user ID
        $this->db->update($this->auctions_release_payment_schedule_table, $paymentReleaseData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }
}
