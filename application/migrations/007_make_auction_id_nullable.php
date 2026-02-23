<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Make_auction_id_nullable extends CI_Migration
{
    public function up()
    {
        // Make auction_id nullable to support non-auction invoices (e.g. Vault, Subscriptions)
        $fields = array(
            'auction_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
        );
        $this->dbforge->modify_column('tbl_invoice_auction_relationship', $fields);
    }

    public function down()
    {
        // Revert back to NOT NULL (Be careful, this might fail if NULLs exist)
        $fields = array(
            'auction_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE,
            ),
        );
        $this->dbforge->modify_column('tbl_invoice_auction_relationship', $fields);
    }
}
