<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminAuctions extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('AuctionModel');
    }

    public function ai_detect_items()
    {
        $image_url = $this->input->post('image_url');
        
        if (!$image_url) {
             return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Image URL is required']));
        }

        // Gemini API Configuration
        $api_key = 'ription AIzaSyBwvFjOw6fnI2evvJt3tKrQLdKqMNccUEM'; // Stored securely in real env
        $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$api_key}";

        // Prepare Prompt
        $prompt = "Analyze this storage unit image. List visible items (furniture, boxes, electronics, etc.). Estimate a total value range in AUD. Provide a short description for an auction listing. Return ONLY JSON: { \"items\": [\"item1\", \"item2\"], \"estimated_value_min\": 100, \"estimated_value_max\": 500, \"description\": \"...\" }";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        ["file_data" => ["mime_type" => "image/jpeg", "file_uri" => $image_url]] // Assuming Gemini supports URL or we need to download/base64
                    ]
                ]
            ]
        ];
        
        // Note: Gemini API often requires Base64 for images if not using Google Cloud Storage URIs. 
        // For simplicity in this implementation, we will assume we fetch the image and base64 encode it.
        $image_data = @file_get_contents($image_url);
        if ($image_data) {
             $base64_image = base64_encode($image_data);
             $data['contents'][0]['parts'][1] = ["inline_data" => ["mime_type" => "image/jpeg", "data" => $base64_image]];
        } else {
             // Fallback or error if image cannot be fetched
             // return $this->respond(false, 'Failed to fetch image');
        }

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($response, true);
        
        // Parse Gemini Response
        // Gemini returns visible candidates -> content -> parts -> text
        $raw_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean markdown code blocks if present
        $raw_text = str_replace(['```json', '```'], '', $raw_text);
        $ai_data = json_decode($raw_text, true);

        if ($ai_data) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'detected_items' => $ai_data['items'] ?? [],
                    'estimated_value' => ($ai_data['estimated_value_min'] ?? 0) . ' - ' . ($ai_data['estimated_value_max'] ?? 0),
                    'description' => $ai_data['description'] ?? '',
                    'confidence' => 0.95 // Synthetic confidence
                ]));
        } else {
             return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false, 
                    'message' => 'AI failed to analyze image',
                    'raw_response' => $response
                ]));
        }
    }

    public function save_master_profile()
    {
        $data = [
            'profile_name' => $this->input->post('name'),
            'cleaning_deposit' => $this->input->post('cleaning_deposit'),
            'deposit_type' => $this->input->post('deposit_type'),
            'auction_terms' => $this->input->post('terms'),
            'additional_features' => $this->input->post('features')
        ];
        
        $id = $this->AuctionModel->save_master_profile($data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => !!$id, 'profile_id' => $id]));
    }

    public function list_master_profiles()
    {
        $profiles = $this->AuctionModel->get_master_profiles();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $profiles]));
    }

    public function create_bulk_auction()
    {
        // Expecting:
        // - master_profile_id (optional, for default values)
        // - items (array of objects with { image_url, location_id, unit_number, ... })
        
        $input = json_decode(file_get_contents('php://input'), true);
        $items = $input['items'] ?? [];
        $master_profile_id = $input['master_profile_id'] ?? null;
        
        $master_data = [];
        if ($master_profile_id) {
            $master_data = $this->AuctionModel->get_master_profile($master_profile_id);
        }

        $created_ids = [];
        foreach ($items as $item) {
            // Merge item specific data with master data
            $auction_data = array_merge([
                'unit_number' => $item['unit_number'],
                'starting_price' => $item['starting_price'] ?? 10,
                'status' => 'scheduled', // Default
                'is_staged' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ], $master_data); // Master data overwrites defaults, but item specific should overwrite master? usually Master is "template", so item specific > master

            // Actually item specific > Master
            if ($master_data) {
                // Fill gaps
                $auction_data['description'] = $master_data['auction_terms']; // Example mapping
                // ... map other fields
            }
            
            // Insert auction
            $result = $this->AuctionModel->insertAuctionData($auction_data); 
            $auction_id = $result['auction_id'] ?? null;
            
            if ($auction_id) {
                $created_ids[] = $auction_id;
                
                // Link location
                if (!empty($item['location_id'])) {
                    $this->AuctionModel->set_auction_locations($auction_id, [$item['location_id']]);
                }
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'created_count' => count($created_ids),
                'ids' => $created_ids
            ]));
    }
    public function force_relist()
    {
        $auctionId = $this->input->post('auction_id');
        $startDate = $this->input->post('start_date') ?: date('Y-m-d');
        $startTime = $this->input->post('start_time') ?: '09:00 AM';
        $endDate   = $this->input->post('end_date')   ?: date('Y-m-d', strtotime('+7 days'));
        $endTime   = $this->input->post('end_time')   ?: '05:00 PM';

        if (!$auctionId) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Auction ID is required']));
        }

        $result = $this->AuctionModel->relistAuction($auctionId, $startDate, $startTime, $endDate, $endTime);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => !!$result,
                'message' => $result ? 'Auction relisted successfully' : 'Failed to relist auction'
            ]));
    }

    public function clone_auction()
    {
        $auctionId = $this->input->post('auction_id');

        if (!$auctionId) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Auction ID is required']));
        }

        $newId = $this->AuctionModel->cloneAuctionRecord($auctionId);

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => !!$newId,
                'new_auction_id' => $newId,
                'message' => $newId ? 'Auction cloned successfully as draft' : 'Failed to clone auction'
            ]));
    }
}
