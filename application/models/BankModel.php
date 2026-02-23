<?php
defined('BASEPATH') or exit('No direct script access allowed');

class BankModel extends CI_Model
{

    protected $bank_table = 'tbl_bank';
    protected $bank_user_relationship_table = 'tbl_user_bank_informations_relationship';
    protected $storage_facility_bank_relationship_table = "tbl_storage_facility_bank_relationship";

    private function ensureBankTableHasPayoutColumns()
    {
        $columns = [
            'account_name' => "ALTER TABLE {$this->bank_table} ADD COLUMN account_name VARCHAR(191) NULL AFTER stripe_bank_account_id",
            'bsb' => "ALTER TABLE {$this->bank_table} ADD COLUMN bsb VARCHAR(16) NULL AFTER account_name",
            'account_number' => "ALTER TABLE {$this->bank_table} ADD COLUMN account_number VARCHAR(32) NULL AFTER bsb",
            'bank_institution' => "ALTER TABLE {$this->bank_table} ADD COLUMN bank_institution VARCHAR(191) NULL AFTER account_number",
        ];

        foreach ($columns as $column => $ddl) {
            if (!$this->db->field_exists($column, $this->bank_table)) {
                $this->db->query($ddl);
            }
        }
    }

    public function insertStorageFacilityBankData($bankAccountInfo)
    {
        $this->ensureBankTableHasPayoutColumns();
        $this->db->insert($this->bank_table, $bankAccountInfo);
        return [
            'bank_account_id' => $this->db->insert_id()
        ];
    }

    public function insertStorageFacilityBankUserRelationshipData($bankAccountUserRelationshipData)
    {
        return $this->db->insert($this->bank_user_relationship_table, $bankAccountUserRelationshipData);
    }

    public function getBankAccountByBankId($bankId)
    {
        $this->db->where('id', $bankId);
        $query = $this->db->get($this->bank_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function getBankAccountByAccountConenctedId($accountId)
    {
        $this->db->where('stripe_bank_account_id', $accountId);
        $query = $this->db->get($this->bank_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return auction details as an array
        } else {
            return null; // Auction not found
        }
    }

    public function linkBankToFacility($bankId, $facilityId)
    {
        $this->db->insert($this->storage_facility_bank_relationship_table, [
            'bank_id' => $bankId,
            'storage_facility_id' => $facilityId
        ]);
    }

    public function unlinkBankFromFacility($bankId, $facilityId)
    {
        $this->db->delete($this->storage_facility_bank_relationship_table, [
            'bank_id' => $bankId,
            'storage_facility_id' => $facilityId
        ]);
    }



    public function getBankByUserId($user_id)
    {

        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->bank_user_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return bank details as an array
        } else {
            return null; // bank not found
        }
    }

    public function getBankByIdWithUserId($bankId, $user_id)
    {
        $this->db->where('bank_id', $bankId);
        $this->db->where('user_id', $user_id);
        $query = $this->db->get($this->bank_user_relationship_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return bank details as an array
        } else {
            return null; // bank not found
        }
    }

    public function updateAccountConenctedStatusTypeData($bankId, $accountConnectedStatusTypeData)
    {
        $this->db->where('id', $bankId); // Match the user ID
        $this->db->update($this->bank_table, $accountConnectedStatusTypeData);

        return $this->db->affected_rows() > 0; // Return true if update was successful
    }


    public function getBankById($bankId)
    {
        $this->db->where('id', $bankId);
        $query = $this->db->get($this->bank_table);

        if ($query->num_rows() > 0) {
            return $query->row_array(); // Return company details as an array
        } else {
            return null; // company not found
        }
    }

    public function getLinkedLocationNames($bankId)
    {
        $this->db->select('sf.name as storage_facility_name');
        $this->db->from($this->storage_facility_bank_relationship_table . ' r');
        $this->db->join('tbl_storage_facilities sf', 'sf.id = r.storage_facility_id');
        $this->db->where('r.bank_id', $bankId);
        $query = $this->db->get();

        $names = array_column($query->result_array(), 'storage_facility_name');
        return !empty($names) ? implode(', ', $names) : '';
    }

    public function getBankAccountsByUserId($userId, $includeInactive = false)
    {
        $this->ensureBankTableHasPayoutColumns();
        $this->db->select('tbl_bank.*, tbl_user_bank_informations_relationship.status_type_id as relationship_status_id');
        $this->db->from('tbl_bank');
        $this->db->join('tbl_user_bank_informations_relationship', 'tbl_bank.id = tbl_user_bank_informations_relationship.bank_id', 'inner');
        $this->db->where('tbl_user_bank_informations_relationship.user_id', $userId);
        
        // Only show active accounts (status_type_id = 1) unless explicitly requested
        if (!$includeInactive) {
            $this->db->where('tbl_bank.status_type_id', 1);
        }
        
        $this->db->order_by('tbl_bank.created_at', 'DESC');
        $query = $this->db->get();
        $results = $query->result_array();

        if (empty($results)) {
            return [];
        }

        foreach ($results as &$bankAccount) {
            $bankAccount['linked_locations'] = $this->getLinkedLocationNames($bankAccount['id']);
        }

        return $results;
    }

    public function setDefaultBankForUser($userId, $bankId)
    {
        $this->ensureBankTableHasPayoutColumns();

        // Reset existing defaults
        $this->db->where('user_id', $userId);
        $this->db->update($this->bank_table, ['is_default' => 'false']);

        // Set selected bank as default
        $this->db->where('user_id', $userId);
        $this->db->where('id', $bankId);
        return $this->db->update($this->bank_table, ['is_default' => 'true']);
    }

    public function deactivateBankAccount($bankId)
    {
        $timestamp = date('Y-m-d H:i:s');

        $this->db->where('id', $bankId);
        $this->db->update($this->bank_table, [
            'status_type_id' => 2,
            'is_default' => 'false',
            'updated_at' => $timestamp
        ]);

        $this->db->where('bank_id', $bankId);
        $this->db->update($this->bank_user_relationship_table, [
            'status_type_id' => 2,
            'updated_at' => $timestamp
        ]);

        $this->db->delete($this->storage_facility_bank_relationship_table, ['bank_id' => $bankId]);

        return true;
    }

    public function updateBankAccount($bankId, $data)
    {
        if (empty($bankId) || empty($data)) {
            return false;
        }
        
        $this->db->where('id', $bankId);
        return $this->db->update($this->bank_table, $data);
    }

    /**
     * Check if a bank account with the same BSB and account number already exists for this user
     * Only checks active accounts (status_type_id = 1)
     */
    public function isDuplicateBankAccount($userId, $bsb, $accountNumber)
    {
        $this->db->select('id');
        $this->db->from($this->bank_table);
        $this->db->where('user_id', $userId);
        $this->db->where('bsb', $bsb);
        $this->db->where('account_number', $accountNumber);
        $this->db->where('status_type_id', 1); // Only check active accounts
        $query = $this->db->get();
        
        return $query->num_rows() > 0;
    }
}
