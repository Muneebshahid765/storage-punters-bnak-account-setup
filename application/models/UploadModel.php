<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UploadModel extends CI_Model
{
    protected $uploads_table = 'tbl_uploads';

    public function insertUploadData($uploadData)
    {
        $this->db->insert($this->uploads_table, $uploadData);
        return [
            'upload_id' => $this->db->insert_id()
        ];
    }
}
