<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Add_manage_all_locations_to_company_relationship extends CI_Migration
{
    public function up()
    {
        $fields = array(
            'manage_all_locations' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'status_type_id'
            ),
        );
        $this->dbforge->add_column('tbl_user_company_relationship', $fields);
    }

    public function down()
    {
        $this->dbforge->drop_column('tbl_user_company_relationship', 'manage_all_locations');
    }
}
