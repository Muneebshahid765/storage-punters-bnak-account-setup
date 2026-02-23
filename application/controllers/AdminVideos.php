<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminVideos extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('VideosModel');
    }

    public function list_seasons()
    {
        $seasons = $this->db->order_by('order_index', 'ASC')->get('tbl_video_seasons')->result_array();
        foreach ($seasons as &$season) {
            $season['episodes'] = $this->db->where('season_id', $season['id'])
                ->order_by('episode_number', 'ASC')
                ->get('tbl_video_episodes')
                ->result_array();
        }
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $seasons]));
    }

    public function create_season()
    {
        $name = $this->input->post('name');
        if (!$name) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Season name is required']));
        }

        $data = [
            'season_name' => $name,
            'description' => $this->input->post('description') ?? '',
            'order_index' => $this->input->post('order') ?? 0
        ];
        $this->db->insert('tbl_video_seasons', $data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    public function add_episode()
    {
        $data = [
            'season_id' => $this->input->post('season_id'),
            'episode_name' => $this->input->post('title'),
            'youtube_url' => $this->input->post('video_url'),
            'description' => $this->input->post('description') ?? '',
            'thumbnail_url' => $this->input->post('thumbnail_url') ?? '',
            'episode_number' => $this->input->post('number') ?? 1,
            'duration_seconds' => $this->input->post('duration') ?? 0
        ];
        $this->db->insert('tbl_video_episodes', $data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true]));
    }

    public function update_season()
    {
        $id = $this->input->post('id');
        $data = [
            'season_name' => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'order_index' => $this->input->post('order')
        ];
        // Filter empty values if necessary, or let them be updated to empty
        $result = $this->VideosModel->update_season($id, $data);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => $result]));
    }

    public function delete_season()
    {
        $id = $this->input->post('id');
        $result = $this->VideosModel->delete_season($id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => $result]));
    }

    public function update_episode()
    {
        $id = $this->input->post('id');
        $data = [
            'episode_name' => $this->input->post('title'),
            'youtube_url' => $this->input->post('video_url'),
            'description' => $this->input->post('description'),
            'thumbnail_url' => $this->input->post('thumbnail_url'),
            'episode_number' => $this->input->post('number'),
            'duration_seconds' => $this->input->post('duration')
        ];
        $result = $this->VideosModel->update_episode($id, $data);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => $result]));
    }

    public function delete_episode()
    {
        $id = $this->input->post('id');
        $result = $this->VideosModel->delete_episode($id);
        return $this->output->set_content_type('application/json')->set_output(json_encode(['success' => $result]));
    }
}
