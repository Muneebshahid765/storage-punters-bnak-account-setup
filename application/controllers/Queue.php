<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Queue extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    // Called every 1 minute to add auction reminders to queue
    public function sendAuctionToQueue()
    {
        $this->load->model('AuctionModel');
        $this->load->model('EmailQueueModel');

        $nowTime = date('Y-m-d H:i:s');
        $auctionTimeFormat = date('Y-m-d h:i A');

        $totalQueued = 0;

        try {
            foreach ([1, 3] as $hours) {
                $auctions = $this->AuctionModel->getAuctionsEndingNear($hours, $auctionTimeFormat);

                if (empty($auctions)) {
                    log_message('info', "[AuctionQueue] No auctions ending in {$hours} hour(s) at {$nowTime}");
                    continue;
                }

                foreach ($auctions as $auction) {
                    $users = $this->AuctionModel->get_interested_users($auction->auction_id);

                    if (empty($users)) {
                        log_message('info', "[AuctionQueue] No interested users for auction ID {$auction->id}");
                        continue;
                    }

                    $scheduleTime = (new DateTime($nowTime))->modify("+{$hours} hour")->format('Y-m-d H:i:s');

                    $subject = "Reminder: Auction ends in {$hours} " . ($hours == 1 ? 'hour' : 'hours');

                    foreach ($users as $user) {
                        $this->EmailQueueModel->add([
                            'to_email'     => $user->email,
                            'subject'      => $subject,
                            'template'     => "auction_reminder_{$hours}hr_email",
                            'user_id'  =>  $user->user_id,
                            'name'         => trim($user->first_name . ' ' . $user->last_name),
                            'auction_id'  =>  $auction->auction_id,
                            'auction_item' => trim($auction->auction_storage_facility_name . ' #' . $auction->auction_id . ' - ' . $auction->auction_storage_facility_city . ' , ' . $auction->auction_storage_facility_state),
                            'auction_url'  => base_url('auctions/' . $auction->auction_id),
                            'scheduled_at' => $scheduleTime,
                            'status'       => 'pending'
                        ]);
                        $totalQueued++;
                    }
                }
            }

            $message = $totalQueued > 0
                ? "Queued {$totalQueued} auction reminder(s)."
                : "No auction reminders queued.";

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success'  => true,
                    'message'  => $message,
                    'datetime' => $nowTime
                ]))
                ->set_status_header(200);
        } catch (Exception $e) {
            log_message('error', '[AuctionQueue] Error: ' . $e->getMessage());

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'An error occurred while queuing auction reminders.',
                    'error'   => $e->getMessage(),
                    'datetime' => $nowTime
                ]))
                ->set_status_header(500);
        }
    }
}
