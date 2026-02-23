<?php
defined('BASEPATH') or exit('No direct script access allowed');

class VideosModel extends CI_Model
{
    protected $table_seasons = 'tbl_video_seasons';
    protected $table_episodes = 'tbl_video_episodes';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_seasons()
    {
        $this->db->order_by('created_at', 'DESC');
        $query = $this->db->get($this->table_seasons);
        return $query->result_array();
    }

    public function get_episodes_by_season($season_id)
    {
        $this->db->where('season_id', $season_id);
        $this->db->order_by('created_at', 'ASC');
        $query = $this->db->get($this->table_episodes);
        return $query->result_array();
    }

    public function create_season($name)
    {
        $this->db->insert($this->table_seasons, ['season_name' => $name]);
        return $this->db->insert_id();
    }

    public function add_episode($data)
    {
        return $this->db->insert($this->table_episodes, $data);
    }

    public function delete_episode($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete($this->table_episodes);
    }

    public function update_season($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_seasons, $data);
    }

    public function delete_season($id)
    {
        // Delete episodes first (cascade)
        $this->db->where('season_id', $id);
        $this->db->delete($this->table_episodes);

        $this->db->where('id', $id);
        return $this->db->delete($this->table_seasons);
    }

    public function update_episode($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update($this->table_episodes, $data);
    }

    public function get_all_grouped_by_season()
    {
        $seasons = $this->get_seasons();
        foreach ($seasons as &$season) {
            $season['episodes'] = $this->get_episodes_by_season($season['id']);
        }
        return $seasons;
    }
}
