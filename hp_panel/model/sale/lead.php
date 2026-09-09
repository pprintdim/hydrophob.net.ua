<?php
class ModelSaleLead extends Model {
	public function getLeads($data = array()) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "lead`";

		if (!empty($data['filter_form'])) {
			$sql .= " WHERE `form` = '" . $this->db->escape($data['filter_form']) . "'";
		}

		$sql .= " ORDER BY `date_added` DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalLeads($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "lead`";

		if (!empty($data['filter_form'])) {
			$sql .= " WHERE `form` = '" . $this->db->escape($data['filter_form']) . "'";
		}

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getTotalNew() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "lead` WHERE `status` = '0'");

		return (int)$query->row['total'];
	}

	public function getLead($lead_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "lead` WHERE lead_id = '" . (int)$lead_id . "'");

		return $query->row;
	}

	public function setStatus($lead_id, $status) {
		$this->db->query("UPDATE `" . DB_PREFIX . "lead` SET `status` = '" . (int)$status . "' WHERE lead_id = '" . (int)$lead_id . "'");
	}

	public function deleteLead($lead_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "lead` WHERE lead_id = '" . (int)$lead_id . "'");
	}
}
