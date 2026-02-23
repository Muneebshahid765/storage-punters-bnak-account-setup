<?php
class EmailQueueModel extends CI_Model
{
    protected $email_queue_table = 'tbl_email_queue';

    public function add($data)
    {
        return $this->db->insert($this->email_queue_table, $data);
    }

    public function get_reminder_3hr_pending_due($now, $limit = 100)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_reminder_3hr_email')
            // ->where('scheduled_at <=', $now)
            ->limit($limit)
            ->get($this->email_queue_table)
            ->result();
    }


    public function get_reminder_1hr_pending_due($now, $limit = 100)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_reminder_1hr_email')
            // ->where('scheduled_at <=', $now)
            ->limit($limit)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_outbid_pending_due($limit = 100)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_outbid_email')
            // ->where('scheduled_at <=', $now)
            ->limit($limit)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_outbid_pending_due_by_auction($auctionId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_outbid_email')
            ->where('auction_id', $auctionId)
            ->get($this->email_queue_table)
            ->result();
    }


    public function get_winner_pending_due($limit = 100)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_winner_email')
            // ->where('scheduled_at <=', $now)
            ->limit($limit)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_winner_pending_due_by_auction($auctionId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_winner_email')
            ->where('auction_id', $auctionId)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_winner_pending_payment_failed_due_by_auction($auctionId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_winner_email_payment_failed')
            ->where('auction_id', $auctionId)
            ->get($this->email_queue_table)
            ->result();
    }


    public function get_invoice_pending_due($limit = 100)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_invoice_email')
            // ->where('scheduled_at <=', $now)
            ->limit($limit)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_invoice_pending_due_by_auction($auctionId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_invoice_email')
            ->where('auction_id', $auctionId)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_after_sold_pending_due_by_auction($auctionId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'auction_after_sold_email')
            ->where('auction_id', $auctionId)
            ->get($this->email_queue_table)
            ->result();
    }

    public function get_account_connected_active_pending_by_account_id($bankId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'connected_account_active_verification_email')
            ->where('bank_id', $bankId)
            ->get($this->email_queue_table)
            ->result();
    }


    public function get_account_connected_inactive_pending_by_account_id($bankId)
    {
        return $this->db
            ->where('status', 'pending')
            ->where('template', 'connected_account_inactive_verification_email')
            ->where('bank_id', $bankId)
            ->get($this->email_queue_table)
            ->result();
    }
    public function mark_sent($id, $date)
    {
        return $this->db->update($this->email_queue_table, ['status' => 'sent', 'updated_at' => $date], ['id' => $id]);
    }

    public function mark_failed($id, $date, $error = '')
    {
        return $this->db->update($this->email_queue_table, ['status' => 'failed', 'updated_at' => $date, 'error_message' => $error], ['id' => $id]);
    }

    public function mark_processing($id, $date)
    {
        return $this->db->update($this->email_queue_table, ['status' => 'processing', 'updated_at' => $date], ['id' => $id]);
    }

    public function delete_all_bidder_outbid_queue($auctionId, $template)
    {
        return $this->db->delete($this->email_queue_table, ['auction_id' => $auctionId, 'template' => $template]);
    }
}
